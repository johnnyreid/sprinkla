<?php declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

use Volantus\Pigpio\Client;
use Volantus\Pigpio\Network\Socket;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\DefaultRequest;

function ToggleGpio(int $gpioNumber): int
{
    global $config;

    $client = new Client(new Socket($config["pigpio_host"], $config["pigpio_port"]));
    $status = $client->sendRaw(new DefaultRequest(Commands::READ, $gpioNumber, 0))->getResponse();

    $status = $status == 1 ? 0 : 1;

    return $client->sendRaw(new DefaultRequest(Commands::WRITE, $gpioNumber, $status))->getResponse();
}

if (PHP_SAPI === 'cli')
{
    echo "Welcome to gpioToggle!\n";
    echo "CLI detected...\n";

    $gpioNumber = isset($argv[1]) ? intval($argv[1]) : 0;

    if ($gpioNumber === 0) {
        echo "Invalid parameters were passed to the script, please try again.\n";
    } else {
        echo "We will attempt to toggle GPIO number: $gpioNumber\n";
        $result = ToggleGpio($gpioNumber);
        echo $result . "\n";
    }
}
else
{
    if (isset($_GET["switch"]) && is_numeric($_GET["switch"]))
    {
        $gpioNumber = intval($_GET["switch"]);
        $result = ToggleGpio($gpioNumber);
        echo $result;
    }
    else
    {
        echo "fail";
    }
}
