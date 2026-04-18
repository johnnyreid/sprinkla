<?php declare(strict_types=1);

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/log.php';

header('Content-Type: application/json');

$dataFile = $config['data_dir'] . '/timer_state.json';
$state = [];

if (file_exists($dataFile)) {
    $fp = fopen($dataFile, 'r');
    if ($fp) {
        flock($fp, LOCK_SH);
        $content = stream_get_contents($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            $state = $decoded;
        }
    }
}

echo json_encode($state);
