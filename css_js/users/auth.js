
//функции всплывающего окна для авторизации через соц. сети
function OpenAuthWindow(ob){
    OpenAuthWindowUrl($(ob).attr('href'),$(ob).text());
}

function OpenAuthWindowUrl(url,title){
    var params = "menubar=no,location=no,resizable=yes,scrollbars=no,status=no,width=1000,height=500"
    var nwin= window.open(url, title, params);
    nwin.focus()
}

$(window).load(function(){


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

    
    //проверка формы "Авторизация" на отдельной странице
    $("#authFormError").submit(function (){

        if ( $("#login_er").val() == '') {
             alert('Укажите свой E-mail!');
             $('#login_er').focus();

        } else  if (!reEmail.test($("#login_er").val())) {
             alert('E-mail указан в неправильном формате!');
             $('#login_er').focus();

        } else if ( $("#password_er").val() == '' ) {
             $('#password_er').focus();
             alert('Укажите пароль!');

        } else return true;

        return false;
    });

    if ($("#login_er").val() == '')
        $('#login_er').focus();
    else
        $('#password_er').focus();
    
});
