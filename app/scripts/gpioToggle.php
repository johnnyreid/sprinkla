<?php


function ToggleGpio(int $gpioNumber)
{
    //set the gpio's mode to output
    system("sudo gpio mode " . $gpioNumber . " out");
    //reading pin's status
    exec("sudo gpio read " . $gpioNumber, $status, $return);

    //set the gpio to high/low
    if ($status[0] == "0")
    {
        $status[0] = "1";
    }
    elseif( $status[0] == "1" )
    {
        $status[0] = "0";
    }

    system("sudo gpio write " . $gpioNumber . " " . $status[0]);
    //reading pin's status
    exec("sudo gpio read " . $gpioNumber, $status, $return);
    //return the status of the GPIO
    return ($status[0]);
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
        if ((is_numeric($gpioNumber)) && ($gpioNumber <= 7) && ($gpioNumber >= 0))
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


