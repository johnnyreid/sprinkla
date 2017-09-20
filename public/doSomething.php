<?php

//sudo git clone https://github.com/johnnoatemybaby/www.git




/*use PiPHP\GPIO\GPIO;
use PiPHP\GPIO\Pin\InputPinInterface;
use PiPHP\GPIO\Pin\OutputPinInterface;

// This GPIO object can be used to retrieve pins and create interrupt watchers
$gpio = new GPIO();

// Configure pin 2 as an output pin and retrieve an object that we can use to change it
$ledPin = $gpio->getOutputPin(2);

// Configure pin 3 as an input pin and retrieve an object that we can use to observe it
$buttonPin = $gpio->getInputPin(3);

// Configure this pin to trigger interrupts when the voltage rises.
// ::EDGE_FALLING and ::EDGE_BOTH are also valid.
$buttonPin->setEdge(InputPinInterface::EDGE_RISING);

// Create an interrupt watcher (this is a type of event loop)
$interruptWatcher = $gpio->createWatcher();

// Register a callback for handling interrupts on the button pin
$interruptWatcher->register($buttonPin, function () use ($ledPin) {
    echo 'Blinking LED...' . PHP_EOL;

    // Toggle the value of the LED five times
    for ($i = 0; $i < 5; $i++) {
        $ledPin->setValue(OutputPinInterface::VALUE_HIGH);
        usleep(100000);
        $ledPin->setValue(OutputPinInterface::VALUE_LOW);
        usleep(100000);
    }

    // Returning false would cause the loop below to exit
    return true;
});

// Loop until an interrupt callback returns false, this code will iterate every 5 seconds
while ($interruptWatcher->watch(5000))
{
    echo "waiting for 5 seconds";
};

*/

?>