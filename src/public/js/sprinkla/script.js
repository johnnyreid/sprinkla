let switch_0 = document.getElementById("switch_0");
let switch_1 = document.getElementById("switch_1");
let switch_2 = document.getElementById("switch_2");
let switch_3 = document.getElementById("switch_3");
let switch_4 = document.getElementById("switch_4");
let switch_5 = document.getElementById("switch_5");
let switch_6 = document.getElementById("switch_6");
let switch_7 = document.getElementById("switch_7");
let switch_8 = document.getElementById("switch_8");
let switch_9 = document.getElementById("switch_9");

//Create an array for easy access later
let switches = [ switch_0, switch_1, switch_2, switch_3, switch_4, switch_5, switch_6, switch_7, switch_8, switch_9];

window.onload=function(){
    const button = document.getElementById('button');
    if (button) {
        alert('Got the button element!');
        button.addEventListener('click', onButtonClick);
    }
}


function onButtonClick() {
    alert('Button clicked!');
}
function removeCheck ( switchNumber )
{
    const elem = document.getElementById("switch_" + switchNumber);
    elem.removeAttribute("checked" )
}

//This function is asking for gpio.php, receiving datas and updating the index.php pictures
function toggleSwitch( switchNumber, broadcomNumber )
{
    let data = "";

    //this is the http request
    let request = new XMLHttpRequest();
    request.open( "GET" , "/gpio/toggle/" + broadcomNumber, true);
    request.send(null);

    //receiving informations
    request.onreadystatechange = function () {
        if (request.readyState === 4 && request.status === 200) {
            data = request.responseText;

            //update the index pic
            if ( data === "1" ) //switch is off
            {
                //todo: remove the line below
                // alert("Switch " + switchNumber + " has been toggled and is off. Data: " + data);
                // switches[switchNumber].removeAttribute("checked");
            }
            else if ( data === "0" ) //switch is on
            {
                //todo: remove the line below
                // alert("Switch " + switchNumber + "  has been toggled and is on. Data: " + data);
                //
                // switches[switchNumber].setAttribute("checked", "checked");

                // //this is the http request
                // var request2 = new XMLHttpRequest();
                // request2.open( "GET" , "../app/scripts/pleaseTurnOffInAFewMinutes.php?switch=" + gpioNumber, true);
                // request2.send(null);
            }
            else if ((data.localeCompare("fail")))
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
        else if (request.status === 404)
        {
            alert("Request Response 404: doesn't exist.");
            return ("fail");
        }
        //test if fail
        else if (request.readyState === 4 && request.status === 500)
        {
            alert("Request Response 500: Internal server error");
            return ("fail");
        }
        //else
        else if (request.readyState === 4 && request.status !== 200 && request.status !== 500)
        {
            alert(request.status)
            alert("Something went wrong3!");

            return ("fail");
        }
    };

    return 0;
}

function setSwitch( switchNumber, broadcomNumber )
{
    let data = "empty";

    //this is the http request
    let request = new XMLHttpRequest();
    request.open( "GET" , "/gpio/read/" + broadcomNumber, true);
    request.send(null);

    //receiving informations
    request.onreadystatechange = function () {
        if (request.readyState === 4 && request.status === 200)
        {
            data = request.responseText;

            //update the index pic
            if ( data === "0" ) //switch is on
            {
                //todo: remove the line below
                const elem = document.getElementById("switch_" + switchNumber);
                elem.setAttribute("checked", "checked")

                // alert("Switch " + switchNumber + " is on. Data: " + data.toString());
                // switches[switchNumber].setAttribute("checked", "checked");
            }
            else if ( data === "1" ) //switch is off
            {
                //todo: remove the line below
                // alert("Switch " + switchNumber + " is off. Data: " + data.toString());
                const elem = document.getElementById("switch_" + switchNumber);
                elem.removeAttribute("checked");
                // switches[switchNumber].removeAttribute("checked");
            }
            else
            {
                alert("Something went wrong2!");
                return ("fail");
            }
        }
        else if (request.status === 404)
        {
            alert("Request Response 404: doesn't exist.");
            return ("fail");
        }
        //test if fail
        else if (request.readyState === 4 && request.status === 500)
        {
            alert("Request Response 500: Internal server error");
            return ("fail");
        }
        //else
        else if (request.readyState === 4 && request.status !== 200 && request.status !== 500)
        {
            alert(request.status)
            alert("Something went wrong3!");

            return ("fail");
        }
    };

    return 0;
}

// function blah()
// {
//     //this is the http request
//     var request = new XMLHttpRequest();
//     request.open( "GET" , "../../app/scripts/gpioToggle.php?switch=2", true);
//
//     //receiving informations
//     request.onreadystatechange = function (){
//         if ( request.readyState === 4 ){
//             document.getElementById("demo").innerHTML = request.responseText;
//
//         }else{
//             document.getElementById("demo").innerHTML = request.statusText;
//             alert(request.statusText);
//         }
//     };
//     request.send(null);
// }
