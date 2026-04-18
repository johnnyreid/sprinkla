<?php declare(strict_types=1);

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/log.php';

header('Content-Type: application/json');

$dataDir = $config['data_dir'];
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$dataFile = $dataDir . '/timer_state.json';

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input) || !isset($input['switchNumber'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$switchNumber = (string) $input['switchNumber'];
if (!preg_match('/^\d+$/', $switchNumber)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid switch number']);
    exit;
}

$fp = fopen($dataFile, 'c+');
flock($fp, LOCK_EX);
$content = stream_get_contents($fp);
$state = $content ? json_decode($content, true) : [];
if (!is_array($state)) {
    $state = [];
}

if (isset($input['state']) && is_array($input['state'])) {
    $state[$switchNumber] = $input['state'];
} else {
    unset($state[$switchNumber]);
}

ftruncate($fp, 0);
rewind($fp);
fwrite($fp, json_encode($state, JSON_PRETTY_PRINT));
flock($fp, LOCK_UN);
fclose($fp);

if (isset($input['state']) && is_array($input['state'])) {
    $bcn = $input['state']['broadcomNumber'] ?? '?';
    $name = $input['state']['name'] ?? 'Unknown';
    if (!empty($input['state']['paused'])) {
        sprinkla_log_operation("Timer paused for $name (pin $bcn)");
    } else {
        $mins = $input['state']['durationMinutes'] ?? '?';
        sprinkla_log_operation("Timer set for $name (pin $bcn): {$mins}m");
    }
} else {
    sprinkla_log_operation("Timer cleared for switch $switchNumber");
}

echo json_encode(['success' => true]);
