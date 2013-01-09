$(window).load(function(){

    // Кнопка "Проголосовать"
    $(".do_voting").click(function(e){

        if ($("input:checked").length == 0) {

            alert('Выберите хотя бы один вариант ответа!');

        } else {

            var vote_id = this.id;

            $.post('/voting/do', $("#vote_form_"+vote_id).serialize(), function(data) {

                if (data.error == 0) {

                    $('#vote_block_'+vote_id).replaceWith(data.html);

                } else if (data.error == 1) {

                    $('#vote_block_'+vote_id).replaceWith(data.html);
                    alert(data.msg);

                } else alert(data.msg);

            }, 'json');
        }
        return false;

    });

    // Кнопка "Показать результаты"
    $(".show_result").click(function(e){
        var vote_id = this.id;
        $.get('/voting/view/'+vote_id, function(data){
            $('#vote_block_'+vote_id).replaceWith(data);
        });
        return false;
    });

});

