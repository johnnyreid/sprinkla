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
//
//# ##### NOTE: ##########
//# 0 = relay on
//# 1 = relay off
//########################

require_once __DIR__ . '/../../config/config.php';
/** @var array $config */

$timerOptions = $config["timer_options"];
$timerDefault = $config["timer_default"];

/**
 * Render timer controls (toggle, radio buttons, countdown) for a sprinkler row.
 */
function renderTimerControls(int $index, int $broadcomNumber, array $timerOptions, int $timerDefault): string
{
    $html = '';

    // Timer enable toggle
    $html .= '<td style="vertical-align:middle; text-align:center; width:50px">';
    $html .= '<label class="timer-toggle-label" title="Enable auto-off timer">';
    $html .= '<input type="checkbox" id="timer_toggle_' . $index . '" ';
    $html .= 'onchange="onTimerToggleChange(' . $index . ',' . $broadcomNumber . ')">';
    $html .= ' ⏱</label></td>';

    // Duration radio buttons
    $html .= '<td style="vertical-align:middle; white-space:nowrap">';
    foreach ($timerOptions as $minutes) {
        $checked = ($minutes === $timerDefault) ? ' checked' : '';
        $html .= '<label class="timer-radio-label">';
        $html .= '<input type="radio" name="timer_duration_' . $index . '" value="' . $minutes . '"' . $checked;
        $html .= ' onchange="onDurationChange(' . $index . ',' . $broadcomNumber . ')">';
        $html .= ' ' . $minutes . 'm</label> ';
    }
    $html .= '</td>';

    // Countdown display
    $html .= '<td style="vertical-align:middle; width:60px; text-align:center">';
    $html .= '<span id="timer_display_' . $index . '" class="timer-countdown" style="display:none"></span>';
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
        .timer-radio-label { font-size: 0.85em; margin-right: 4px; cursor: pointer; }
        .timer-countdown {
            font-family: monospace;
            font-size: 1em;
            font-weight: bold;
            color: #e74c3c;
            background: #fef0ef;
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>
</head>
<body class="landing">

<div id="sprinkla-config" data-timer-default="<?php echo $timerDefault; ?>" style="display:none"></div>

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
                            renderTimerControls($i, $bcn, $timerOptions, $timerDefault),
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
                                renderTimerControls($i, $bcn, $timerOptions, $timerDefault),
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
