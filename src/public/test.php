<?php declare(strict_types=1);

// To call this page, in the browser type:
// http://localhost/gpio/toggle/1

//echo "USER IN VIEWS WITH ID: $broadcomNumber";


require_once '../../config/bootstrap.php';


use Volantus\Pigpio\Client;
use Volantus\Pigpio\Network\Socket;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\DefaultRequest;


$client = new Client(new Socket('192.168.20.9', 8888));

// TODO Add assertions etc. about this value, passed by route
/** @var string $broadcomNumber */

// Get the current value
$status = $client->sendRaw(new DefaultRequest(Commands::READ, intval(17), 0))->getResponse();

echo("Response received from the remote client: " . strval($status));