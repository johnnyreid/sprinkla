<?php declare(strict_types=1);

require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../../../config/config.php';

use Volantus\Pigpio\Client;
use Volantus\Pigpio\Network\Socket;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\DefaultRequest;

/** @var string $broadcomNumber */
$pin = intval($broadcomNumber);

$validPins = array_column($config["gpio"], "broadcom_number");
if (!in_array($pin, $validPins, true)) {
    http_response_code(400);
    echo "fail";
    exit();
}

$client = new Client(new Socket($config["pigpio_host"], $config["pigpio_port"]));
$client->sendRaw(new DefaultRequest(Commands::WRITE, $pin, 1));

$gpioNames = array_column($config["gpio"], "name", "broadcom_number");
$name = $gpioNames[$pin] ?? 'Unknown';
sprinkla_log_operation("Auto-off $name (pin $pin)");

// Clear sprinkler on-since record
$dataDir = $config['data_dir'];
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$onSinceFile = $dataDir . '/sprinkler_on_since.json';
$onSince = file_exists($onSinceFile) ? (json_decode(file_get_contents($onSinceFile), true) ?: []) : [];
unset($onSince[(string) $pin]);
file_put_contents($onSinceFile, json_encode($onSince, JSON_PRETTY_PRINT), LOCK_EX);

echo "1";
