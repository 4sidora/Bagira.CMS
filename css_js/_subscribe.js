$(window).load(function(){

    $("#subscribeForm").submit(function (){

        if ($(".subscribe input:checked").length == 0) {

            alert('Выберите хотя бы одну категорию рассылки!');

        } else if ($('#emailSubscr').val() == '') {

            alert('Укажите свой E-mail!');
            $('#emailSubscr').focus();

        } else if (!reEmail.test($("#emailSubscr").val())) {

            alert('E-mail указан в неправильном формате!');
            $('#emailSubscr').focus();
            
        } else return true;

        return false;
    });

});

