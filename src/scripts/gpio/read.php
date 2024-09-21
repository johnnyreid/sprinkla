<?php declare(strict_types=1);

use Volantus\Pigpio\Client;
use Volantus\Pigpio\Network\Socket;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\DefaultRequest;


$client                 = new Client(new Socket('192.168.20.9', 8888));

// TODO Add assertions etc. about this value, passed by route
/** @var string $broadcomNumber */

// Get the current value
$status                 = $client->sendRaw(new DefaultRequest(Commands::READ, intval($broadcomNumber), 0))->getResponse();

echo (strval($status));