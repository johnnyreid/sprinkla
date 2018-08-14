

var switch_0 = document.getElementById("switch_0");
var switch_1 = document.getElementById("switch_1");
var switch_2 = document.getElementById("switch_2");
var switch_3 = document.getElementById("switch_3");
var switch_4 = document.getElementById("switch_4");
var switch_5 = document.getElementById("switch_5");
var switch_6 = document.getElementById("switch_6");
var switch_7 = document.getElementById("switch_7");
var switch_21 = document.getElementById("switch_21");
var switch_22 = document.getElementById("switch_22");

//Create an array for easy access later
var switches = [ switch_0, switch_1, switch_2, switch_3, switch_4, switch_5, switch_6, switch_7, switch_21, switch_22];



//This function is asking for gpio.php, receiving datas and updating the index.php pictures
function change_pin ( gpioNumber)
{
    var data = 0;

    //send the switch number to gpio.php for changes

    //this is the http request
    var request = new XMLHttpRequest();
    request.open( "GET" , "../app/scripts/gpioToggle.php?switch=" + gpioNumber, true);
    request.send(null);

    //receiving informations
    request.onreadystatechange = function () {
        if (request.readyState === 4 && request.status === 200) {
            data = request.responseText;

            //update the index pic
            if ((data.localeCompare("0"))) //false
            {
                //todo: remove the line below
                //alert("Switched on. Data: " + data);
                switches[gpioNumber].removeAttribute("checked");
            }
            else if ((data.localeCompare("1"))) //true
            {
                //todo: remove the line below
                //alert("Switched off. Data: " + data);

                switches[gpioNumber].setAttribute("checked", "checked");

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
