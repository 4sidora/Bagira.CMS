
$(window).load(function(){

    // Валидация формы 
    $('#addUserForm').submit(function (){

        wrong = false;

        if ( $("#login").val() == "" ) {
            $('#login').focus();
            alert('Укажите свой E-mail, он так же будет использоваться для входа на сайт.');

        } else if ( !reEmail.test($("#login").val()) ) {
            $('#login').focus();
            alert('E-mail указан в неправильном формате.');

        } else if ( $("#password_reg").val() == "" ) {
            $('#password_reg').focus();
            alert('Укажите пароль!');

        } else if ( $("#password_reg").val().length < 6 ) {
            $('#password_reg').focus();
            alert('Пароль должен быть не меньше 6 символов!');

        }  else if ( $("#password_reg2").val() == "" ) {
            $('#password_reg2').focus();
            alert('Повторите пароль еще раз!');

        } else if ( $("#password_reg2").val() != $("#password_reg").val() ) {
            $('#password_reg2').focus();
            alert('Указанные пароли должны совпадать!');

        } else if ( $("#name").val() == '' ) {
             $('#name').focus();
             alert('Укажите свое имя!');

        } else if ( $("#surname").val() == '' ) {
             $('#surname').focus();
             alert('Укажите свою фамилию!');

        } else if ($("#confirm").length && !$("#confirm").prop('checked')) {
             $('#confirm').focus();
             alert('Необходимо согласие с уловиями регистрации!');

        } else if ($("#captcha").val() == '') {
            $('#captcha').focus();
            alert('Введите цифры указанные на картинке!');

        } else wrong =true;

        return wrong;
    });

    // Сообщение об ошибке
    if ($('#alert_msg').text() != '') {
        alert($('#alert_msg').text());
        $('#'+$('#alert_field').text()).focus();
    }

});
