<?php

use Phalcon\Mvc\Controller;


use Phalcon\Di;



use PiPHP\GPIO\GPIO;
use PiPHP\GPIO\Pin\PinInterface;

class SprinklaController extends Controller
{
    public function indexAction()
    {


    }

    public function registerAction()
    {

        $status = [];
//set pins mode to output

            system ( "gpio mode ".$i." out" );

//turns on the LEDs

            system ( "gpio write ".$i." 1" );

//reads and prints the LEDs status
        for ($i = 0; $i <= 7; $i++ ) {
            exec ( "gpio read ".$i, $status );
            echo ( $status[0] );
        }
//waits 2 seconds
        sleep ( 2 );
//turns off the LEDs
        for ($i = 0; $i <= 7; $i++ ) {
            system ( "gpio write ".$i." 0" );
        }
        echo '<h1>fuckin sprinklers on biatch!</h1>';
      //  echo "<h1>$blah</h1>";


//// Toggle the value of the LED five times
//        for ($i = 0; $i < 5; $i++) {
//            $pin1->setValue(PinInterface::VALUE_HIGH);
//            usleep(100000);
//            $pin1->setValue(PinInterface::VALUE_LOW);
//            usleep(100000);
//        }
    }
}