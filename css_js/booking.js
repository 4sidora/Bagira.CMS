
var limit_places_get = 0, coast_tikets=0;
var cl=0, cl2='', rat=0,  flag_bron=0;


function declOfNum(number) {
    titles = new Array('билет', 'билета', 'билетов');
    cases = [2, 0, 1, 1, 1, 2];
    return "<b>" + number + "</b> " + titles[ (number%100>4 && number%100<20)? 2 : cases[Math.min(number%10, 5)] ];
}

// возвращает тип числа с окончание
function declOfNum2(number) {
    titles = new Array('места', 'мест', 'мест');
    cases = [2, 0, 1, 1, 1, 2];
    return "<b>" + number + "</b> " + titles[ (number%100>4 && number%100<20)? 2 : cases[Math.min(number%10, 5)] ];
}

function enableSelectPlace() {

    // выбор мест на плане зала
    $('.pl-1, .pl-2, .pl-3, .pl-5').click(function() {

        var limit_places = parseInt( $('#limit_places').html() );

        var idp= $(this).attr("id");
        $('#result_buy').hide();

        if ($(this).attr("class") != 'pl-4' && flag_bron == 0) {

            // отмена выбранного места
            if ($(this).attr("class")=='pl-5') { /*отказаться от места*/


                if (limit_places_get >= limit_places-limit_places+1){



                    $("#limit_now").html('');
                    limit_places_get--;
                    $("#count_tiket").html(declOfNum(limit_places_get));

                    // отнимаем от общей стоимости
                    var price = parseInt($('#price_group_' + $(this).attr('rel')).attr('alt'));
                    coast_tikets -= price;
                    $("#coast_tikets strong").html((coast_tikets)+'р.');

                    $('#lt-'+$(this).attr('lang')).parent('li').remove();

                    $('#place_hid-'+$(this).attr('lang')).remove();





                    $(this).attr("class", $(this).attr("alt")); // возвращием тип места

                    if (limit_places_get==0){
                        $("#count_tiket").html('Выберите места <br /><u>Кликните по тем местам на схеме слева, которые вы хотите выбрать</u>');
                        $("#hide_vis").hide();
                    }
                }

            } else {

                /*Выбрать место*/
                if (limit_places_get <= limit_places-1){

                    $("#limit_now").html('');
                    limit_places_get++;
                    $("#count_tiket").html(declOfNum(limit_places_get));


                    // прибавляем к общей стоимости
                    var price = parseInt($('#price_group_' + $(this).attr('rel')).attr('alt'));


                    coast_tikets += price;
                    $("#coast_tikets strong").html((coast_tikets)+'р.');

                    $(this).attr("alt", $(this).attr("class"));
                    $(this).attr("class","pl-5");

                    var attr_class= $(this).attr("alt");


                    if (attr_class == 'pl-2') {

                        $("#hidden_enter_bron").append('<input type="hidden" name="place_hid[]" value="'+$(this).attr('lang')+'" id="place_hid-'+$(this).attr('lang')+'" />');
                        $("#plice_list").append('<li class="vip_price">' + '<div class="take_attr" id="lt-'+$(this).attr('lang')+'">'+' <div class="place">'+$(this).attr('title')+', </div> ' + ' <div class="price">'+ price +'р.</div></div>' + '<div class="cancel_bron"></div>' + '<div class="clear"></div>'  + '</li>');

                        $("#hide_vis").animate({   opacity: 'show'}, { duration: 'slow' });


                    } else {

                        $("#hidden_enter_bron").append('<input type="hidden" name="place_hid[]" value="'+$(this).attr('lang')+'" id="place_hid-'+$(this).attr('lang')+'" />');
                        $("#plice_list").append('<li>' + '<div class="take_attr" id="lt-'+$(this).attr('lang')+'">'+' <div class="place">'+$(this).attr('title')+', </div>' + ' <div class="price">'+ price +'р.</div></div>' + '<div class="cancel_bron"></div>' + '<div class="clear"></div>'  + '</li>');

                        $("#hide_vis").animate({   opacity: 'show'}, { duration: 'slow' });

                    };

                } else
                if (limit_places != 0)
                    $("#limit_now").html('Вы можете забронировать только '+declOfNum2(limit_places)+' на данный сеанс!<br /><br />');
            }

        }

    });




}

$(document).ready(function() {

    $(".HallHaveNoPlaces ins").click(function(){
        $('.HallHaveNoPlaces, #TB_sloi2').hide();
    });

    var session_id = parseInt($('#session_id').val());
    var limit_places = parseInt($('#limit_places').html());
    if (limit_places==0) $('#count_tiket, #buy_tikets, #hide_vis').hide();

    // Запрашиваем занятые места
    $.get('/booking/get-unavailable-place/'+session_id, function(data) {

        if (data.error == 0) {

            // Блокируем занятые места
            if(data.unavailable_seats != ''){
                for (var i = 0; i < data.unavailable_seats.length; i++){
                    $('#seat_'+data.unavailable_seats[i]).attr('class', 'pl pl-4');
                }
            }

            // Выводим количество мест
            if ($('#max_seats_booking').val() >= data.count_free_seats || $('#max_seats_booking').val() == 0)
                var for_booking = data.count_free_seats;
            else
                var for_booking = $('#max_seats_booking').val();

            $('#count_seats').html(data.count_seats);
            $('#count_free_seats').html(data.count_free_seats);


            // смотрим, если число забронированных мест больше, чем в настройках, неразрешаем бронировать
            if ( $('#max_seats_booking').val() != 0 )
            if  ( data.count_booking_seats >= $('#max_seats_booking').val() ){
                for_booking = 0;
                $('#limit_places').html(0) ;
            } else {
                for_booking =  ($('#max_seats_booking').val() - data.count_booking_seats);

                // смотрим, если кол-во билетов для бронирования меньше чем мест, то показываем итоговоечисло для пользователя
                if ( for_booking <= parseInt($('#limit_places').html()) ){
                    $('#limit_places').html(for_booking);
                }
            }

            $('#count_free_seats_for_booking').html(for_booking);
            if ( for_booking == 0 ) $('.HallHaveNoPlaces, #TB_sloi2').show();

            // Включаем выбор мест в зале
            enableSelectPlace();

        } else {
            alert(data.msg);
        }

    }, 'json');


    // бронирование билетов
    $('#buy_tikets').live('click', function() {

        var mas_plases  = '', count_place=0;

        $('.hall div.pl-5').each(function(){
            mas_plases = mas_plases + $(this).attr('lang') + '/';
            count_place++;
        });

        if (mas_plases == '') {

            $('#result_buy').show();  $('#result_buy').html('Вы не выбрали места');

        } else {

            $('#result_buy').hide();
            $('#result_buy_get').show();
            $('#buy_tikets').hide();
            ///$('#result_buy').load(

            $.get('/booking/do/' + $('#session_id').val() + '/' + mas_plases, function(data){

                if (data.error == 0) {

                    $('#result_buy').html( data.msg  );

                    // если все хорошо
                    $('#result_buy_get').hide();
                    $('#result_buy').show();

                    // выставляем флаг о невозможности бронирования
                    flag_bron=1;

                    // помечаем места как забронированные
                    $('.pl-5').each(function(){
                        $(this).attr("class","pl-4");
                        $(this).attr("alt","pl-4");
                    });

                } else {
                    alert('Ошибка#' + data.error + ': ' + data.msg);
                }



            }, 'json');
        }

    });


    $('.pl-5').each(function(){

        limit_places_get++;
        $("#count_tiket").html(declOfNum(limit_places_get));
        $("#hide_vis").show();

        // прибавляем к общей стоимости
        coast_tikets += parseInt($(this).attr('id'));
        $("#coast_tikets strong").html(coast_tikets+'р.');

        $("#hidden_enter_bron").append('<input type="hidden" name="place_hid[]" value="'+$(this).attr('lang')+'" id="place_hid-'+$(this).attr('lang')+'" />');
        $("#plice_list").append('<div id="lt-'+$(this).attr('lang')+'">'+$(this).attr('title')+', <u>'+( $(this).attr('id') ) +'р.</u></div>');
    });



    //отмена выбранного места по клику на крестике
    $('#plice_list li .cancel_bron').live('click', function(){

        var num = $(this).parent('li').children('.take_attr').attr('id');
        var take_atr = parseInt(num.replace(/\D+/g,""));


        $("#limit_now").html('');
        limit_places_get--;
        $("#count_tiket").html(declOfNum(limit_places_get));

        $('.pl-5').each(function(){

            if ($(this).attr('lang')== take_atr){

                $(this).attr("class", $(this).attr("alt"));

                var price = parseInt($('#price_group_' + $(this).attr('rel')).attr('alt'));
                coast_tikets -= price;
                $("#coast_tikets strong").html((coast_tikets)+'р.');

            }

        });

        $(this).parent('li').remove();

        if (limit_places_get==0){
            $("#hide_vis").hide();
        }
    });



});