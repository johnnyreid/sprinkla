<?php

    //Getting and using values
    if (isset ( $_GET["switch"] ))
    {
        $gpioNumber = strip_tags ($_GET["switch"]);

        //test if value is a number
        if ( (is_numeric($gpioNumber)) && ($gpioNumber <= 7) && ($gpioNumber >= 0) )
        {

            //set the gpio's mode to output
            system("sudo gpio mode ".$gpioNumber." out");
            //reading pin's status
            exec ("sudo gpio read ".$gpioNumber, $status, $return );


            //set the gpio to high/low
            if ($status[0] == "0" )
            {
                $status[0] = "1";
            }
            else if
            (
                $status[0] == "1" ) { $status[0] = "0";
            }

            system("sudo gpio write ".$gpioNumber." ".$status[0] );
            //reading pin's status
            exec ("sudo gpio read ".$gpioNumber, $status, $return );
            //print it to the client on the response
            echo($status[0]);

        }
        else
        {
            echo ("fail");
        }
    } //print fail if cannot use values
    else
    {
        echo ("fail");
    }
?>