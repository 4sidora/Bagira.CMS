window.reEmail = /^([a-z0-9\.\-\_])+\@(([a-zA-Z0-9\-\_])+\.)+([a-zA-Z0-9]{2,4})+$/i;
var wrongL = false;


jQuery(document).ready(function() {

    testlogin();
    testpass();

    $("#login").keyup(function () { testlogin();});
    $("#passw").keyup(function () { testpass()  });

    $("input").keyup(function (event) {
        if ((event.keyCode == 0xA)||(event.keyCode == 0xD)){
            enter_auth();
            return false;
        }
    });

    $("#login").focus();

});

function testlogin(){
    if ( reEmail.test($("#login").val()) ) {
        $("#login").css({ color: "#000"} );
        $("#logintext").css({ color: "#000"} );
        if ($("#passw").val().length >= 1) wrongL = true;
    } else { hideLogin(); }
}

function testpass(){
    if ($("#passw").val().length >= 1){
        $("#passw").css({ color: "#000"} );
        $("#passwtext").css({ color: "#000"} );
        if (reEmail.test($("#login").val())) wrongL = true;
    } else {  hidePassw(); }
}

function hideLogin(){  $("#login").css({ color: "#CC6666"});  $("#logintext").css({ color: "#CC6666"} );   }
function hidePassw(){  $("#passw").css({ color: "#CC6666"});  $("#passwtext").css({ color: "#CC6666"} );  }
function enter_auth(){  if (wrongL) $('#auth_form').submit(); }


