<!DOCTYPE HTML>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="css/sliders.css">
    <link rel="stylesheet" type="text/css" href="css/tables.css">

    <style>
        .error {color: #FF0000;}
    </style>
</head>
<body>


<?php

require_once 'vendor/autoload.php';

use PiPHP\GPIO\GPIO;
use PiPHP\GPIO\Pin\InputPinInterface;
use PiPHP\GPIO\Pin\OutputPinInterface;
$sprinkler1 = false;
$sprinkler2 = false;
$sprinkler3 = false;
$sprinkler4 = false;
$sprinkler5 = false;
$sprinkler6 = false;
$sprinkler7 = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    empty($_POST["checkbox1"]) ? $sprinkler1="false" : $sprinkler1="true";

}
function gather_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<h2>Turn the sprinklers on or off</h2>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">





<form action=".todelete/doSomething.php" method="POST" target="_self">

<table style="width:50%" >
    <tr>
        <td>
            <label class="switch"/>
                <input type="checkbox" name="checkbox1" value="true" <?php if (isset($sprinkler1) && $sprinkler1=="true")echo "checked";?> onChange="this.form.submit()">
                <span class="slider round"></span>
            </label>
        </td>
        <td class="fuckyou">
            <p >Area 1</p>
        </td>
    </tr>

</table>

</form>


    <?php
    echo "<h2>Your Input:</h2>";

    echo "<br>";
    echo $sprinkler1;
    echo "<br>";
    ?>


</body>
</html>
