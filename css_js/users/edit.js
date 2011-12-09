
$(window).load(function(){

    // редактирование личных данных
    $('#editUserForm').submit(function(){
        wrong = false;

        if ( $("#name").val() == '' ) {
                $('#name').focus();
                alert('Укажите свое имя!');

        } else if ( $("#surname").val() == '' ) {
                $('#surname').focus();
                alert('Укажите свою фамилию!');

        } else wrong =true;

        return wrong;
    });

    // Сообщение об ошибке
    if ($('#error_msg').text() != '') {
        alert($('#error_msg').text());
        $('#'+$('#error_field').text()).focus();
    }

});

