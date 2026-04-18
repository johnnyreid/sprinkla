<?php declare(strict_types=1);

/**
 * Cron script to auto-turn-off sprinklers with expired timers.
 * Run every minute: * * * * * /usr/bin/php /path/to/sprinkla/src/scripts/timer/cron.php
 */

require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../../../config/config.php';

use Volantus\Pigpio\Client;
use Volantus\Pigpio\Network\Socket;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\DefaultRequest;

$dataDir = $config['data_dir'];
$dataFile = $dataDir . '/timer_state.json';

if (!file_exists($dataFile)) {
    exit(0);
}

$fp = fopen($dataFile, 'c+');
if (!$fp) {
    exit(1);
}

flock($fp, LOCK_EX);
$content = stream_get_contents($fp);
$state = $content ? json_decode($content, true) : [];
if (!is_array($state)) {
    $state = [];
}

$now = time();
$changed = false;
$validPins = array_column($config["gpio"], "broadcom_number");

foreach ($state as $sw => $timer) {
    if (!is_array($timer)) {
        unset($state[$sw]);
        $changed = true;
        continue;
    }

    if (!empty($timer['paused'])) {
        continue;
    }

    if (!isset($timer['endTime']) || $timer['endTime'] > $now) {
        continue;
    }

    // Timer expired — turn off the sprinkler
    $bcn = (int) $timer['broadcomNumber'];
    $name = $timer['name'] ?? 'Unknown';

    if (!in_array($bcn, $validPins, true)) {
        unset($state[$sw]);
        $changed = true;
        continue;
    }

    try {
        $client = new Client(new Socket($config['pigpio_host'], $config['pigpio_port']));
        $client->sendRaw(new DefaultRequest(Commands::WRITE, $bcn, 1));
        sprinkla_log_operation("CRON: Timer expired, turned off $name (pin $bcn)");
    } catch (\Exception $e) {
        sprinkla_log_operation("CRON: Failed to turn off $name (pin $bcn): " . $e->getMessage());
    }

    unset($state[$sw]);
    $changed = true;
}

if ($changed) {
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($state, JSON_PRETTY_PRINT));
}

flock($fp, LOCK_UN);
fclose($fp);

// Safety check: turn off any sprinkler that has been running too long
$maxRunSeconds = ($config['max_run_minutes'] ?? 60) * 60;
$onSinceFile = $dataDir . '/sprinkler_on_since.json';

if (file_exists($onSinceFile)) {
    $onSince = json_decode(file_get_contents($onSinceFile), true) ?: [];
    $onSinceChanged = false;

    foreach ($onSince as $pin => $entry) {
        $startTime = is_array($entry) ? ($entry['startTime'] ?? 0) : $entry;
        if ($now - $startTime < $maxRunSeconds) {
            continue;
        }

        $bcn = (int) (is_array($entry) ? ($entry['broadcomNumber'] ?? $pin) : $pin);
        $name = is_array($entry) ? ($entry['name'] ?? 'Unknown') : 'Unknown';

        if (!in_array($bcn, $validPins, true)) {
            unset($onSince[$pin]);
            $onSinceChanged = true;
            continue;
        }

        try {
            $client = new Client(new Socket($config['pigpio_host'], $config['pigpio_port']));
            $client->sendRaw(new DefaultRequest(Commands::WRITE, $bcn, 1));
            sprinkla_log_operation("CRON: Safety timeout, turned off $name (pin $bcn) after {$config['max_run_minutes']} minutes");

            // Send email alert if configured
            $alertEmail = $config['alert_email'] ?? '';
            if (!empty($alertEmail)) {
                $subject = "Sprinkla: Safety timeout - $name turned off";
                $body = "The sprinkler \"$name\" (pin $bcn) was automatically turned off after running for {$config['max_run_minutes']} minutes.\n\n"
                    . "This is a safety measure to prevent water waste.\n\n"
                    . "Time: " . date('Y-m-d H:i:s') . "\n";
                $headers = "From: sprinkla@" . gethostname() . "\r\n"
                    . "Content-Type: text/plain; charset=UTF-8\r\n";
                if (!mail($alertEmail, $subject, $body, $headers)) {
                    sprinkla_log_operation("CRON: Failed to send safety timeout email to $alertEmail");
                }
            }
        } catch (\Exception $e) {
            sprinkla_log_operation("CRON: Safety timeout failed for $name (pin $bcn): " . $e->getMessage());
        }

        unset($onSince[$pin]);
        $onSinceChanged = true;
    }

    if ($onSinceChanged) {
        file_put_contents($onSinceFile, json_encode($onSince, JSON_PRETTY_PRINT), LOCK_EX);
    }
}
