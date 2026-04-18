<?php declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/config.php';

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
$status = $client->sendRaw(new DefaultRequest(Commands::READ, $pin, 0))->getResponse();

echo (strval($status));