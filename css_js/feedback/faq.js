
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

            $.post($("#faqForm").attr('action'), $("#faqForm").serialize(), function(data) {

                if (data.field == 0) {

                    alert(data.msg);

                    $('.cross').click();
                    $('#captcha').attr('src', '/core/random_image/'+(Math.random()*10000).toFixed(0));
                    $('#random_image, #content').val('');

                } else {

                    alert(data.msg);
                    $('#captcha').attr('src', '/core/random_image/'+(Math.random()*10000).toFixed(0));
                    $('#random_image').val('');                             
                    $('#'+data.field).focus();
                }

            }, 'json');
        }

        return false;
    });

});
