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

require_once __DIR__ . '/../../config/config.php';
/** @var array $config */

$timerDefault = $config["timer_default"];
$timerStep = $config["timer_step"];
$timerMin = $config["timer_min"];

/**
 * Render timer controls (toggle, +/- duration, countdown) for a sprinkler row.
 */
function renderTimerControls(int $index, int $broadcomNumber, int $timerDefault): string
{
    $html = '';

    // Timer enable toggle
    $html .= '<td style="vertical-align:middle; text-align:center; width:50px">';
    $html .= '<label class="timer-toggle-label" title="Enable auto-off timer">';
    $html .= '<input type="checkbox" id="timer_toggle_' . $index . '" ';
    $html .= 'onchange="onTimerToggleChange(' . $index . ',' . $broadcomNumber . ')">';
    $html .= ' ⏱</label></td>';

    // Duration adjuster: -5 / display / +5
    $html .= '<td style="vertical-align:middle; white-space:nowrap; text-align:center">';
    $html .= '<button class="timer-adj-btn" onclick="adjustDuration(' . $index . ',' . $broadcomNumber . ',-1)">−5</button> ';
    $html .= '<span id="timer_duration_display_' . $index . '" class="timer-duration-display">' . $timerDefault . 'm</span> ';
    $html .= '<button class="timer-adj-btn" onclick="adjustDuration(' . $index . ',' . $broadcomNumber . ',1)">+5</button>';
    $html .= '</td>';

    // Countdown display and clear button
    $html .= '<td style="vertical-align:middle; width:100px; text-align:center; white-space:nowrap">';
    $html .= '<span id="timer_display_' . $index . '" class="timer-countdown" style="display:none"></span> ';
    $html .= '<button id="timer_clear_' . $index . '" class="timer-clear-btn" style="display:none" ';
    $html .= 'onclick="onTimerClear(' . $index . ')" title="Clear timer">✕</button>';
    $html .= '</td>';

    return $html;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sprinkla</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Sprinkla sprinkler control interface" />
    <!--[if lte IE 8]><script src="js/html5shiv.js"></script><![endif]-->
    <script src="js/jquery.min.js"></script>
    <script src="js/skel.min.js"></script>
    <script src="js/skel-layers.min.js"></script>
    <script src="js/init.js"></script>
    <script src="js/sprinkla/script.js"></script>
    <noscript>
        <link rel="stylesheet" href="css/skel.css" />
        <link rel="stylesheet" href="css/style.css" />
        <link rel="stylesheet" href="css/style-xlarge.css" />
    </noscript>
    <style>
        .timer-toggle-label { cursor: pointer; font-size: 1.1em; }
        .timer-adj-btn {
            background: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.85em;
            padding: 2px 8px;
            font-weight: bold;
        }
        .timer-adj-btn:hover { background: #e0e0e0; }
        .timer-duration-display {
            font-family: monospace;
            font-size: 0.95em;
            font-weight: bold;
            min-width: 30px;
            display: inline-block;
            text-align: center;
        }
        .timer-countdown {
            font-family: monospace;
            font-size: 1em;
            font-weight: bold;
            color: #e74c3c;
            background: #fef0ef;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .timer-paused {
            color: #e67e22;
            background: #fef5e7;
        }
        .timer-clear-btn {
            background: none;
            border: 1px solid #ccc;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.8em;
            padding: 1px 5px;
            color: #999;
            vertical-align: middle;
        }
        .timer-clear-btn:hover {
            color: #e74c3c;
            border-color: #e74c3c;
        }
    </style>
</head>
<body class="landing">

<div id="sprinkla-config" data-timer-default="<?php echo $timerDefault; ?>" data-timer-step="<?php echo $timerStep; ?>" data-timer-min="<?php echo $timerMin; ?>" style="display:none"></div>

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
                    <h3>Lawn Sprinklers</h3>
                    <table>
<?php
                    for ($i = 0; $i < 5; $i++)
                    {
                        $bcn = $config["gpio"][$i]["broadcom_number"];
                        echo '<tr>',
                            '<td style="vertical-align: middle; text-align: left">' . $config["gpio"][$i]["name"] . ':</td>',
                            '<td style="vertical-align:middle; width: 80px"><label class="switch"><input type="checkbox" id="switch_' . $i . '" onclick="toggleSwitch(' . $i . ',' . $bcn . ')">',
                            '<span class="slider round"></span>',
                            '</label></td>',
                            renderTimerControls($i, $bcn, $timerDefault),
                            '</tr>',
                            '<script type="text/javascript">',
                            'setSwitch(' . $i . ',' . $bcn . ');',
                            '</script>';
                    }
?>
                    </table>
                </section>
            </div>
            <div class="6u 16u$(medium) 12u$(xsmall)">
                <section class="box">
                    <h3>Garden Beds</h3>
                    <table>
<?php
                        for ($i = 5; $i < count($config["gpio"]); $i++)
                        {
                            $bcn = $config["gpio"][$i]["broadcom_number"];
                            echo '<tr>',
                                '<td style="vertical-align:middle; text-align: left;">' . $config["gpio"][$i]["name"] . ':</td>',
                                '<td style="vertical-align:middle; width: 80px"><label class="switch"><input type="checkbox" id="switch_' . $i . '" onclick="toggleSwitch(' . $i . ',' . $bcn . ')">',
                                '<span class="slider round"></span>',
                                '</label></td>',
                                renderTimerControls($i, $bcn, $timerDefault),
                                '</tr>',
                                '<script type="text/javascript">',
                                'setSwitch(' . $i . ',' . $bcn . ');',
                                '</script>';
                        }
?>
                    </table>
                </section>
            </div>
        </div>
    </div>
</section>

<?php include "footer.html" ?>

</body>
</html>
