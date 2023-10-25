<?php

use Volantus\Pigpio\Client;
use Volantus\Pigpio\Network\Socket;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\DefaultRequest;

function ToggleGpio(int $gpioNumber)
{
    $client = new Client(new Socket('dumber.home.arpa', 8888));

    //set the gpio's mode to output
//    system("sudo gpio mode " . $gpioNumber . " out");
    //reading pin's status
//    exec("sudo gpio read " . $gpioNumber, $status, $return);
    //read the current value of the pin and load into the array
    $status = $client->sendRaw(new DefaultRequest(Commands::READ, $gpioNumber, 0))->getResponse();

    //set the gpio to high/low
    if ($status == "0")
    {
        $status = "1";
    }
    elseif( $status == "1" )
    {
        $status = "0";
    }
    $status = $client->sendRaw(new DefaultRequest(Commands::WRITE, $gpioNumber, $status))->getResponse();

//    system("sudo gpio write " . $gpioNumber . " " . $status[0]);
    //reading pin's status
//    exec("sudo gpio read " . $gpioNumber, $status, $return);
    //return the status of the GPIO
    return ($status);
}

if (PHP_SAPI === 'cli')
{

    echo "Welcome to gpioToggle!\n";
    echo "CLI detected...\n";

    $gpioNumber = intval($argv[1]) ??0?: 200;

    $b = 'blah';
    $a = intval($b) ??0?: 200;
    echo $a;

    $b = 'blah';
    $a = intval($b) ??0;
    echo $a;


    if (  $gpioNumber === 200 ) {
        echo "Invalid parameters were passed to the script, please try again.\n";
    }
    else
    {
        echo "We will attempt to toggle GPIO number: $gpioNumber\n";
        $result = ToggleGpio($gpioNumber);
        echo $result . "\n";
    }
}
else
{
    //Getting and using values
    if (isset ($_GET["switch"]))
    {
        $gpioNumber = strip_tags($_GET["switch"]);

        //test if value is a number
        if (is_numeric($gpioNumber))
        {
            $result = ToggleGpio($gpioNumber);

            //Return the status of the gpio
            echo $result;

        }
        else
        {
            echo("fail");
        }
    } //print fail if cannot use values
    else
    {
        echo("fail");
    }
}


function pleaseTurnOffInAFewMinutes(int $gpioNumber)
{

    system("sudo gpio write " . $gpioNumber . " 1");

    //wait a sec
    sleep(30);

    system("sudo gpio write " . $gpioNumber . " 0");

    //reading pin's status
    exec("sudo gpio read " . $gpioNumber, $status, $return);

    //return the status of the GPIO
    return ($status[0]);
}
