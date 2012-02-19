
$(window).load(function(){

    $('#passwordForm').submit(function(){
	     wrong = false;

        if ( $("#current_password").val() == "" ) {
                $('#current_password').focus();
                alert('Укажите текущий пароль!');

        } else if ( $("#password").val() == "" ) {
                $('#password').focus();
                alert('Укажите новый пароль!');

        } else if ( $("#password").val().length < 6 ) {
                $('#password').focus();
                alert('Пароль не может быть меньше 6 символов!');

        } else if ( $("#password2").val() == "" ) {
                $('#password2').focus();
                alert('Повторите пароль!');

        } else if ( $("#password2").val() != $("#password").val() ) {
                $('#password').focus();
                alert('Пароли не равны!');

        } else return true;

        return false;
    });

    if ($('#alert_msg').text() != '') {
        alert($('#alert_msg').text());
        $('#'+$('#alert_field').text()).focus();
    }
});

