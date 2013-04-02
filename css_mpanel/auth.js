reEmail = /^([a-z0-9\.\-\_])+\@(([a-zA-Z0-9\-\_])+\.)+([a-zA-Z0-9]{2,4})+$/i;
var wrongL = false;


jQuery(document).ready(function() {


    //проверка формы "Авторизация"
    $("#authForm").submit(function (){

        if ( $("#auth_login").val() == '') {
            alert('Укажите свой E-mail!');
            $('#auth_login').focus();

        } else  if (!reEmail.test($("#auth_login").val())) {
            alert('E-mail указан в неправильном формате!');
            $('#auth_login').focus();

        } else if ( $("#auth_password").val() == '' ) {
            $('#auth_password').focus();
            alert('Укажите пароль!');

        } else return true;

        return false;
    });

    testlogin();
    testpass();

    $("#auth_login").keyup(testlogin).change(testlogin);
    $("#auth_password").keyup(testpass).change(testpass);

    $("#login").focus();

    if ($('#error').val() > 0)
        alert('Вы указали не правильный логин или пароль!');

});

function testlogin(){
    if ( reEmail.test($("#auth_login").val()) ) {
        $("#auth_login").css({ color: "#000"} );
        $("#logintext").css({ color: "#000"} );
        if ($("#auth_password").val().length >= 1) wrongL = true;
    } else { hideLogin(); }
}

function testpass(){
    if ($("#auth_password").val().length >= 1){
        $("#auth_password").css({ color: "#000"} );
        $("#passwtext").css({ color: "#000"} );
        if (reEmail.test($("#auth_login").val())) wrongL = true;
    } else {  hidePassw(); }
}

function hideLogin(){  $("#auth_login").css({ color: "#CC6666"});  $("#logintext").css({ color: "#CC6666"} ); }
function hidePassw(){  $("#auth_password").css({ color: "#CC6666"});  $("#passwtext").css({ color: "#CC6666"} ); }



