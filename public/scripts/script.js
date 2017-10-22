

var switch_0 = document.getElementById("switch_0");
var switch_1 = document.getElementById("switch_1");
var switch_2 = document.getElementById("switch_2");
var switch_3 = document.getElementById("switch_3");
var switch_4 = document.getElementById("switch_4");
var switch_5 = document.getElementById("switch_5");
var switch_6 = document.getElementById("switch_6");
var switch_7 = document.getElementById("switch_7");

var switch_2gay = document.getElementById("switch_2gay");

//Create an array for easy access later
var switches = [ switch_0, switch_1, switch_2, switch_3, switch_4, switch_5, switch_6, switch_7];

function blah()
{
alert("u are so shit")
}


//This function is asking for gpio.php, receiving datas and updating the index.php pictures
function change_pin ( gpioNumber)
{



    //this is the http request
    var request = new XMLHttpRequest();
    var data = 0;

    document.getElementById("demo").innerHTML ="gpioNumber: " + gpioNumber;
    //send the switch number to gpio.php for changes
    try{

url = "/../../../app/scripts/gpio.php";
var result = '';
result.get(url)
    .done(function() {
        document.getElementById("demo").innerHTML ="script exists";
    }).fail(function() {
    document.getElementById("demo").innerHTML ="script does not exists";
});

    request.open( "GET" , "/../../../app/scripts/gpio.php?switch=" + gpioNumber, true);

    }
    catch(err) {
        document.getElementById("demo").innerHTML = err.message;
    }

    request.send(null);

    //receiving informations
    request.onreadystatechange = function ()
    {
        if (request.readyState === 4 && request.status === 200)
        {


            data = request.responseText;
            //update the index pic
            if (!(data.localeCompare("0"))) //false
            {
                switches[gpioNumber].removeAttribute("checked");
            }
            else if (!(data.localeCompare("1"))) //true
            {
                switches[gpioNumber].setAttribute("checked", "checked");
            }
            else if (!(data.localeCompare("fail")))
            {
                alert("Something went wrong1!");
                return ("fail");
            }
            else
            {
                alert("Something went wrong2!");
                return ("fail");
            }


        }
        else if(request.status==404)s+=" doesn't exist.";

        //test if fail
        else if (request.readyState === 4 && request.status === 500)
        {
            alert("server error");
            return ("fail");
        }
        //else
        else if (request.readyState === 4 && request.status !== 200 && request.status !== 500)
        {
            alert("Something went wrong3!");
            return ("fail");
        }
    };

    return 0;
}