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

<script>
    function myFunction() {
        var x = document.getElementById("mySelect").value;
        document.getElementById("demo").innerHTML = "You selected: " + x;
    }
</script>

<html>
<head>
    <link rel="stylesheet" type="text/css" href="css/sliders.css">
    <link rel="stylesheet" type="text/css" href="css/tables.css">
</head>

<body>



<table style="width:50%" >
    <tr>
        <td>
            <label class="switch" id="fart" onchange="check(this);" />
                <input type="checkbox">
                  <span class="slider round"></span>
            </label>
        </td>
        <td class="fuckyou">
            <p >Area 1</p>
        </td>



    </tr>
    <tr>
        <td>
            <p id="demo"></p>


        </td>
    </tr>
    <tr>
        <td>
            <label class="switch">
                <input type="checkbox">
                <span class="slider round"></span>
            </label>
        </td>
        <td class="fuckyou">
            <p>Area 2</p>
        </td>
    </tr>
    <tr>
        <td>
            <label class="switch">
                <input type="checkbox">
                <span class="slider round"></span>
            </label>
        </td>
        <td class="fuckyou">
            <p>Area 3</p>
        </td>
    </tr>
    <tr>
        <td> <label class="switch">
                <input type="checkbox">
                <span class="slider round"></span>
            </label>
        </td>
        <td class="fuckyou">
            <p>Area 4</p>
        </td>
    </tr>
    <tr>
        <td> <label class="switch">
                <input type="checkbox">
                <span class="slider round"></span>
            </label>
        </td>
        <td class="fuckyou">
            <p>Area 5</p>
        </td>
    </tr>
    <tr>
        <td> <label class="switch">
                <input type="checkbox">
                <span class="slider round"></span>
            </label>
        </td>
        <td class="fuckyou">
            <p>Area 6</p>
        </td>
    </tr>
    <tr>
        <td> <label class="switch">
                <input type="checkbox">
                <span class="slider round"></span>
            </label>
        </td>
        <td class="fuckyou">
            <p>Area 7</p>
        </td>
    </tr>


</table>




</body>


</html>
