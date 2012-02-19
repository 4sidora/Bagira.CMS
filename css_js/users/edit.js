
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
    if ($('#alert_msg').text() != '') {
        alert($('#alert_msg').text());
        $('#'+$('#alert_field').text()).focus();
    }

});

