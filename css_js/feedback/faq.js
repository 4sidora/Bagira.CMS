
$(window).load(function(){

    // Валидация формы
    $('#faqForm').submit(function (){

        if ($("#name").val() == '') {
            $('#name').focus();
            alert("Укажите свое имя!");

        } else if ($("#email").val() == '') {
            $('#email').focus();
            alert("Введите E-mail, пожалуйста!");

        } else if (!reEmail.test($("#email").val())) {
            alert( 'E-mail указан в неправильном формате.' );
            $('#email').focus();

        } else if ($("#content").val() == '') {
            $('#content').focus();
            alert("Введите сообщение, пожалуйста!");

        } else if ($("#random_image").val() == '') {
            $('#random_image').focus();
            alert('Введите цифры указанные на картинке!');

        } else {

            $.post('/feedback/send_message', $("#faqForm").serialize(), function(data) {

                if (data.error == 0) {

                    alert(data.data);

                    $('.cross').click();                    
                    $('#random_image, #content').val('');
                    $('#captcha').attr('src', '/core/random_image/'+(Math.random()*10000).toFixed(0));

                } else if (data.error == 1) {

                    alert(data.data);
                    $('#captcha').attr('src', '/core/random_image/'+(Math.random()*10000).toFixed(0));
                    $('#random_image').val('');
                    $('#random_image').focus();

                } else {

                    alert(data.data);
                    $('#'+data.error).focus();
                }

            }, 'json');
        }

        return false;
    });

});
