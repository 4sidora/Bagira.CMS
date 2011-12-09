
$(window).load(function(){

    $('#captcha_img').click(function(){
        $('#captcha_img').attr('src', '/core/random_image/'+(Math.random()*10000).toFixed(0));
    });

    $('#captcha_change').click(function(){
        $('#captcha_img').click();
        return false;
    });

    $('#recoverForm').submit(function (){
        wrong = false;

        if ( $("#login_or_email").val() == "" ) {
            $('#login_or_email').focus();
            alert('Укажите свой E-mail!');

        } else if ( !reEmail.test($("#login_or_email").val()) ) {
            $('#login_or_email').focus();
            alert('E-mail указан в неправильном формате!');

        } else if ($("#captcha").val() == '') {
            $('#captcha').focus();
            alert('Введите цифры указанные на картинке!');

        } else wrong =true;

        return wrong;
    });

    if ($('#error_msg').text() != '') {
        alert($('#error_msg').text());
        $('#'+$('#error_field').text()).focus();
    }
    
});
