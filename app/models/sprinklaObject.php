<?php
/**
 * Created by PhpStorm.
 * User: uqreid
 * Date: 20/09/2017
 * Time: 5:25 PM
 */

namespace sprinkla\models;

use PiPHP\GPIO\GPIO;
use PiPHP\GPIO\Pin\OutputPin;

/**
 * Class sprinklaObject
 * @package sprinkla\models
 */
class sprinklaObject
{
    /**
     * @var GPIO
     */
    protected $myGpio;
    /**
     * @var OutputPin|\PiPHP\GPIO\Pin\OutputPinInterface
     */
    protected $pin1;
    /**
     * @var OutputPin|\PiPHP\GPIO\Pin\OutputPinInterface
     */
    protected $pin2;
    /**
     * @var OutputPin|\PiPHP\GPIO\Pin\OutputPinInterface
     */
    protected $pin3;
    /**
     * @var OutputPin|\PiPHP\GPIO\Pin\OutputPinInterface
     */
    protected $pin4;
    /**
     * @var OutputPin|\PiPHP\GPIO\Pin\OutputPinInterface
     */
    protected $pin5;
    /**
     * @var OutputPin|\PiPHP\GPIO\Pin\OutputPinInterface
     */
    protected $pin6;
    /**
     * @var OutputPin|\PiPHP\GPIO\Pin\OutputPinInterface
     */
    protected $pin7;
    /**
     * @var OutputPin|\PiPHP\GPIO\Pin\OutputPinInterface
     */
    protected $pin8;

    /**
     * sprinklaObject constructor.
     */
    function __construct()
    {
        $this->gpio = new GPIO();
        $this->pin1 = $this->myGpio->getOutputPin(14);
        $this->pin2 = $this->myGpio->getOutputPin(15);
        $this->pin3 = $this->myGpio->getOutputPin(18);
        $this->pin4 = $this->myGpio->getOutputPin(23);
        $this->pin5 = $this->myGpio->getOutputPin(24);
        $this->pin6 = $this->myGpio->getOutputPin(25);
        $this->pin7 = $this->myGpio->getOutputPin(8);
        $this->pin8 = $this->myGpio->getOutputPin(9);
    }

    public function getPin1(): OutputPin
    {
        return $this->pin1;
    }
}