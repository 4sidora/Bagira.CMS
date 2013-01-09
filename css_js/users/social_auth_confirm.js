
window.reEmail = /^([a-z0-9\.\-\_])+\@(([a-zA-Z0-9\-\_])+\.)+([a-zA-Z0-9]{2,6})+$/i;

$(window).load(function(){

    // Валидация формы 
    $('#socialAuthForm').submit(function (){

        wrong = false;

        if ($("#email").length && $("#email").val() == '') {
            $('#email').focus();
            alert('Для продолжения регистрации вам необходимо указать E-mail.');

        } else if ($("#email").length && !reEmail.test($("#email").val()) ) {
            $('#email').focus();
            alert('E-mail указан в неправильном формате.');

        } else if ($("#confirm").length && !$("#confirm").prop('checked')) {
             $('#confirm').focus();
             alert('Необходимо согласие с уловиями регистрации!');

        } else wrong = true;

        return wrong;
    });

    // Сообщение об ошибке
    if ($('#alert_msg').text() != '') {
        alert($('#alert_msg').text());
        $('#'+$('#alert_field').text()).focus();
    }

});
