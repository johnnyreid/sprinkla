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
        <script src="scripts/script.js"></script>
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
                                <?php
                                echo ("<h3>Lawns</h3>");
                                echo ("<table>");

                                $gpioPins   = array(0,1,2,3,4,5);
                                $values     = array(0,0,0,0,0,0);

                                for ( $i= 0; $i<count($gpioPins); $i++)
                                {
//                                    echo ("<h3>i = $i</h3>");
//                                    echo ("<h3>val = $gpioPins[$i]</h3>");
                                    //set the pin's mode to output
//                                    system("sudo gpio mode ".$gpioPins[$i]." out");

                                    //read the current value of the pin and load into the array
//                                    exec ("sudo gpio read ".$gpioPins[$i],$values[$i], $return );

//                                    echo ("<h3>Sprinkler $gpioPins[$i], current value: $values[$i][0]</h3>");
                                    //if offs
                                    if ( $values[$i][0] == 0 )
                                    {
                                        echo ("<tr>");
                                        echo ("<td style='vertical-align:middle'>Sprinkler ".$gpioPins[$i].": </td>");

                                        echo ("<td style='vertical-align:middle'><label class='switch'><input type='checkbox' id='switch_".$gpioPins[$i]."' onclick='change_pin(".$gpioPins[$i].");'>");
                                        echo ("<span class='slider round'></span>");
                                        echo ("</label></td>");
                                        echo ("</tr>");
                                    }
                                    //if on
                                    if ( $values[$i][0] == 1 )
                                    {
                                        echo("<tr>");
                                        echo("<td style='vertical-align:middle'>Sprinkler " . $gpioPins[$i]. ":</td>");
                                        echo("<td style='vertical-align:middle'><label class='switch'><input type='checkbox' id='switch_" . $gpioPins[$i] . "' checked onclick='change_pin(".$gpioPins[$i].");'>");
                                        echo("<span class='slider round'></span>");
                                        echo ("</label></td>");
                                        echo ("</tr>");
                                    }
                                }
                                echo ("</table>");
                                ?>
                            </section>
                        </div>
                        <div class="6u$ 16u$(medium) 12u$(xsmall)">
                            <section class="box">

                                <?php
                                echo ("<h3>Garden beds</h3>");
                                echo ("<table>");

                                $gpioPins   = array(6,7,21,22);
                                $values     = array(0,0,0,0);

                                for ( $i= 0; $i<count($gpioPins); $i++)
                                {
//                                    echo ("<h3>i = $i</h3>");
//                                    echo ("<h3>val = $gpioPins[$i]</h3>");
                                    //set the pin's mode to output
                                    system("sudo gpio mode ".$gpioPins[$i]." out");

                                    //read the current value of the pin and load into the array
                                    exec ("sudo gpio read ".$gpioPins[$i],$values[$i], $return );

//                                    echo ("<h3>Sprinkler $gpioPins[$i], current value: $values[$i][0]</h3>");
                                    //if offs
                                    if ( $values[$i][0] == 0 )
                                    {
                                        echo ("<tr>");
                                        echo ("<td style='vertical-align:middle'>Sprinkler ".$gpioPins[$i].": </td>");

                                        echo ("<td style='vertical-align:middle'><label class='switch'><input type='checkbox' id='switch_".$gpioPins[$i]."' onclick='change_pin(".$gpioPins[$i].");'>");
                                        echo ("<span class='slider round'></span>");
                                        echo ("</label></td>");
                                        echo ("</tr>");
                                    }
                                    //if on
                                    if ( $values[$i][0] == 1 )
                                    {
                                        echo("<tr>");
                                        echo("<td style='vertical-align:middle'>Sprinkler " . $gpioPins[$i]. ":</td>");
                                        echo("<td style='vertical-align:middle'><label class='switch'><input type='checkbox' id='switch_" . $gpioPins[$i] . "' checked onclick='change_pin(" . $gpioPins[$i] . ");'>");
                                        echo("<span class='slider round'></span>");
                                        echo ("</label></td>");
                                        echo ("</tr>");
                                    }
                                }
                                echo ("</table>");
                                ?>
                            </section>
                        </div>

					</div>
				</div>
		    </section>


<?php include  "footer.html"?>

	</body>
</html>