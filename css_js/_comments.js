$(window).load(function(){

    // Кнопка "Ответить" у каждого комментария
    $('body').on('click', 'a.answer', function() {
        $("#comment_parent_id").val($(this).attr('rel'));
        $("#comment_text").focus();
        return false;
    });

    // Изменение рейтинга комментария
    $('body').on('click', '.change-rate', function(){

        var id = $(this).attr('rel');
        var vector = $(this).attr('href');

        if (id != '')
            $.get('/comments/change-rate/' + id + '/' + vector, function(data) {
                var rate = $('#rate'+id).text() * ((vector == 'up') ? 1 : -1) + 1;
                $('#rate'+id).text(rate);
                $('#rate'+id).attr('id', 0);
            });

        return false;
    });

    // Обработчик для формы отправки нового комментария
    $('#commentForm').submit(function(){

        if ($('#comment_text').val().length == 0) {

            alert('Укажите текст комментария!');
            $('#comment_text').focus();

        } else if ($('#comment_email').val().length == 0) {

            alert('Укажите свой E-mail!');
            $('#comment_email').focus();
                   
        } else if (!reEmail.test($("#comment_email").val())) {

            alert('Указанный вами E-mail не правильный!');
            $('#comment_email').focus();

        } else if ($('#comment_username').val() == '') {

            alert('Представьтесь, пожалуйста!');
            $('#comment_username').focus();

        } else if ($('#random_image').length > 0 && $('#random_image').val() == '') {

            alert('Укажите числа указанные на картике!');
            $('#random_image').focus();

        } else {

            $.post('/comments/add', $('#commentForm').serialize(), function(data){

                if (data.error == 0) {

                    var parent_id = $('#comment_parent_id').val();

                    if (parent_id != 0)
                        var ul = $('#com'+parent_id+' > ul');
                    else
                        var ul = $('#comments > ul');

                    if (ul.length == 0) {
                        $('#com' + parent_id).append('<ul></ul>');
                        ul = $('#com' + parent_id + ' > ul');
                    }

                    $(ul).append(data.data);
                    $('#random_image, #commentForm textarea').val('');
                    $('#captcha').attr('src', '/core/random_image/'+(Math.random()*10000).toFixed(0));

                } else if (data.error == 1) {

                    alert(data.data);
                    $('#captcha').attr('src', '/core/random_image/'+(Math.random()*10000).toFixed(0));
                    $('#random_image').val('').focus();

                } else
                    alert(data.data);

            }, 'json');


            //return true;
        }

        return false;
    });

});

