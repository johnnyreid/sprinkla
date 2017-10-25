<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="../public/css/sliders.css">
    <link rel="stylesheet" type="text/css" href="../public/css/tables.css">
    <link rel="stylesheet" type="text/css" href="../public/css/general.css">
    <title>Raspberry Pi Gpio</title>
</head>

<body>
<!-- javascript -->
<script src="../public/scripts/script.js"></script>

<?php

    //system("whoami");

    //this php script generate the first page in function of the file
    $values = array(0,0,0,0,0,0,0,0);

    for ( $i= 0; $i<8; $i++)
    {
        //set the pin's mode to output
        system("sudo gpio mode ".$i." out");

        //read the current value of the pin and load into the array
        exec ("sudo gpio read ".$i, $values[$i], $return );
    }

    //for loop to read the value
    for ($i = 0; $i < 8; $i++)
    {
        //if off
        if ( $values[$i][0] == 0 )
        {
            echo ("<label class='switch'>");
            echo ("<h3>Sprinkler ".$i.": </h3>");
            echo ("<input type='checkbox' id='switch_".$i."' onclick='change_pin (".$i.");'>");
            echo ("<span class='slider round'></span>");
            echo ("</label>");
            echo ("<br><br>");
        //echo ("<img id='button_".$i."' src='data/img/red/red_".$i.".jpg' onclick='change_pin (".$i.");'/>");
        }
        //if on
        if ( $values[$i][0] == 1 )
        {
            echo ("<label class='switch'>");
            echo ("<h3>Sprinkler ".$i.": </h3>");
            echo ("<input type='checkbox' id='switch_".$i."' checked onclick='change_pin (".$i.");'>");
            echo ("<span class='slider round'></span>");
            echo ("</label>");
            echo ("<br><br>");
        //echo ("<img id='button_".$i."' src='data/img/green/green_".$i.".jpg' onclick='change_pin (".$i.");'/>");
        }
    }
?>




</body>
</html>



