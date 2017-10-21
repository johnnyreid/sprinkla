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

     //   $pin1 = $gpio->getOutputPin(14);
//        $pin2 = $gpio->getOutputPin(15);
//        $pin3 = $gpio->getOutputPin(18);
//        $pin4 = $gpio->getOutputPin(23);
//        $pin5 = $gpio->getOutputPin(24);
//        $pin6 = $gpio->getOutputPin(25);
//        $pin7 = $gpio->getOutputPin(8);
//        $pin8 = $gpio->getOutputPin(9);
   //     $blah = $pin1->getNumber();


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