<?php declare(strict_types=1);

//# Reference for pigpio library: https://abyz.me.uk/rpi/pigpio/
//
//# This is a Raspberry Pi 1 Model B+
//# Relay | GPIO# | PIN | Broadcom number (used by pigpio)
//# 1     | 0     | 11  | 17
//# 2     | 1     | 13  | 27
//# 3     | 2     | 15  | 22
//# 4     | 3     | 16  | 23
//# 5     | 4     | 12  | 18
//# 6     | 5     | 18  | 24
//# 7     | 6     | 22  | 25
//# 8     | 7     | 7   | 4
//# 9     | 21    | 29  | 5
//
//# Not in use:
//# 10    | 22    | 31  | 6
//# 11    | 23    | 33  | 13
//# 12    | 24    | 35  | 19
//# 13    | 25    | 37  | 26
//# 14    | 26    | 32  | 12
//# 15    | 27    | 36  | 16
//# 16    | 28    | 38  | 20
//
//# ##### NOTE: ##########
//# 0 = relay on
//# 1 = relay off
//########################

$gpioPins               = array(17,18,27,22,23,24,25,4,5,6);
$values                 = array(0,0,0,0,0,0,0,0,0,0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sprinkla</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <!--[if lte IE 8]><script src="js/html5shiv.js"></script><![endif]-->
    <script src="js/jquery.min.js"></script>
    <script src="js/skel.min.js"></script>
    <script src="js/skel-layers.min.js"></script>
    <script src="js/init.js"></script>
    <!-- javascript -->
    <script src="js/sprinkla/script.js"></script>
    <noscript>
        <link rel="stylesheet" href="css/skel.css" />
        <link rel="stylesheet" href="css/style.css" />
        <link rel="stylesheet" href="css/style-xlarge.css" />
    </noscript>
</head>
<body class="landing">
<?php include "header.html" ?>
<!-- Banner -->
<section id="banner">
    <header class="major">
        <h2>Hi. This is Sprinkla.</h2>
    </header>
</section>

<section id="one" class="wrapper style1 special">
    <div class="container">
        <header class="major">
            <p>Control sprinklers below:</p>
        </header>
        <div class="row 150%">
            <div class="6u 16u$(medium) 12u$(xsmall)">
                <section class="box">
                    <h3>Lawns</h3>
                    <table>
<?php
                    for ( $i= 0; $i<6; $i++)
                    {
                        echo    '<tr>',
                                '<td style="vertical-align:middle">Sprinkler ' . $i+1 . ':</td>',
                                '<td style="vertical-align:middle"><label class="switch"><input type="checkbox" id="switch_'.$i.'" onclick="toggleSwitch('.$i.','.$gpioPins[$i].')">',
                                '<span class="slider round"></span>',
                                '</label></td>',
                                '</tr>',
                                #run a script to set the initial checked status
                                '<script type="text/javascript">',
                                'setSwitch('.$i.','.$gpioPins[$i].');',
                                '</script>'
                                ;
                    }
?>
                    </table>
                </section>
            </div>
            <div class="6u$ 16u$(medium) 12u$(xsmall)">
                <section class="box">
                    <h3>Garden beds</h3>
                    <table>

<?php
                    for ( $i= 6; $i<count($gpioPins); $i++)
                    {
                        echo    '<tr>',
                                '<td style="vertical-align:middle">Sprinkler ' . $i+1 . ':</td>',
                                '<td style="vertical-align:middle"><label class="switch"><input type="checkbox" id="switch_' . $i . '" onclick="toggleSwitch('.$i.','.$gpioPins[$i].')">',
                                '<span class="slider round"></span>',
                                '</label></td>',
                                '</tr>',
                                #run a script to set the initial checked status
                                '<script type="text/javascript">',
                                '   setSwitch(' . $i . ',' . $gpioPins[$i] . ');',
                                '</script>'
                        ;
                    }
?>
                    </table>
                </section>
            </div>
        </div>
    </div>
</section>


<?php include  "footer.html"?>

</body>
</html>
