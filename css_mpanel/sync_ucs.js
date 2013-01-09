

var send_flag, max_movies_flag, max_halls_flag, new_movies, procent, curURL, baseURL, curFunct, stopIndex;


function clearDB(){

    ShowMessage(
        'Очистка данных',
        'Вы уверены в том, что хотите очистить БД? <br/><br/> Будут удалены все места в зале, сеансы и ценовые группы. Действие не обратимо.',
        {
            'Отмена': function() {
                $(this).dialog('close');
            },
            'Очистить': function() {
                document.location.replace('/mpanel/booking/sync_proc/clear');
                $(this).dialog('close');
            }
        }
    );

};


function addInfo(title, count, $selector) {

    if (count == 0) {
        notice = 'Обновлений нет.';
        num = '';
    } else {
        notice = 'Добавлено '+count+' новых объектов.';
        num = '+' + count;
    }

    $($selector).html(num);

    $("#ucs_check_list").show().append('<span>'+title+'<br /><small>'+notice+'</small></span>');
}


function errorProc(){

    var yes = confirm(LangArray['SEARCH_MSG_1']);

    if (yes) {
        
        send_flag--;

        $.ajax({            url: curURL,
            success: curFunct,
            error: errorProc
        });
    }
}



function startSync() {

    stopIndex = false;
    baseURL = $("#admin_url").val()+'/booking/sync_proc/';

    $("#count_message").html(LangArray['SEARCH_MSG_2']);
    $("#probar").show().css('width', '0%');
    $("#prbar").show();

    curURL = baseURL + 'start';

    $.get(curURL, function(data) {

        if (data.error) {

            ShowMessage(
                'Синхронизация остановлена',
                'Невозможно запустить синхронизацию, т.к. она уже запущена другим пользователем.',
                {
                    'OK': function() {
                        $(this).dialog('close');
                        document.location.replace('/mpanel/booking/sync');
                    }
                }
            );

        } else {

            send_flag = 0;

            max_movies_flag = data.movies;
            new_movies = data.new_movies;
            max_halls_flag = data.halls;

            addInfo('Синхронизация залов завершена.', data.new_halls, '#new_halls');

            sendSeatsProc();
        }

    }, 'json');


    $('#progressBar').dialog('open');
    $("#progressBar").dialog({
        bgiframe: true,
        resizable: false,
        height:140,
        width:400,
        modal: true,
        overlay: {
            backgroundColor: '#000',
            opacity: 0.5
        },
        buttons: {
            'Остановить синхронизацию': function() {
                stopIndex = true;
                $("#sync_restart_msg").show();
                $("#ucs_check_list span").remove();
                $(this).dialog('close');
            }
        }
    });

}


function sendSeatsProc(){

    if (send_flag < max_halls_flag) {

        if (send_flag == 0)
            $("#count_message").html('Обработано 0% мест в залах. Не закрывайте окно!');

        if (!stopIndex) {

            curURL = baseURL + 'seats/' + send_flag;
            curFunct = sendSeatsProc;

            $.ajax({
                url: curURL,
                success: curFunct,
                error: errorProc
            });
        }

        send_flag ++;
        procent = (send_flag / (max_halls_flag / 100)).toFixed(0);

    } else {

        curURL = baseURL + 'count_seats';

        $.get(curURL, function(data) {

            addInfo('Синхронизация мест в залах завершена.', data.count, '#new_seats');

            send_flag = 0;
            sendSessionsProc();            

        }, 'json');

        procent = 100;
    }

    $("#count_message").html('Обработано '+procent+'% мест в залах. Не закрывайте окно!');
    $("#probar").css('width', procent+'%');
}

function sendSessionsProc(){

    if (send_flag < max_movies_flag) {

        if (send_flag == 0) {
            addInfo('Синхронизация фильмов завершена.', new_movies, '#new_movies');
            $("#count_message").html('Обработано 0% сеансов. Не закрывайте окно!');
        }

        if (!stopIndex) {

            curURL = baseURL + 'sessions/' + send_flag;
            curFunct = sendSessionsProc;

            $.ajax({
                url: curURL,
                success: sendSessionsProc,
                error: errorProc
            });
        }

        send_flag ++;
        procent = (send_flag / (max_movies_flag / 100)).toFixed(0);

    } else {

        curURL = baseURL + 'count_sessions';

        $.get(curURL, function(data) {

            send_flag = 0;
            max_sessions_flag = data.steps;

            addInfo('Синхронизация сеансов завершена.', data.new_count, '#new_sessions');
            
            sendSessionsPriceProc();

        }, 'json');

        procent = 100;

    }

    $("#count_message").html('Обработано '+procent+'% сеансов. Не закрывайте окно!');
    $("#probar").css('width', procent+'%');
}

function sendSessionsPriceProc(){

    if (send_flag < max_sessions_flag) {

        if (send_flag == 0)
            $("#count_message").html('Обработано 0% ценовых групп. Не закрывайте окно!');

        if (!stopIndex) {

            curURL = baseURL + 'session_price/' + send_flag;
            curFunct = sendSessionsPriceProc;

            $.ajax({
                url: curURL,
                success: sendSessionsPriceProc,
                error: errorProc
            });
        }

        send_flag ++;
        procent = (send_flag / (max_sessions_flag / 100)).toFixed(0);

        $("#count_message").html('Обработано '+procent+'% ценовых групп. Не закрывайте окно!');

    } else {

        curURL = baseURL + 'stop';

        $.get(curURL, function(data) {

            addInfo('Синхронизация ценовых групп завершена.', data.new_count, '#new_prices');

            $('#sync_ucs_date').html(data.stop_time);
            $("#sync_restart_msg").hide();

            $("#back_url").show();
            $("#prbar").hide();
            $("#count_message").html('Синхронизация завершена... Можете закрыть окно.');
            $('#progressBar').dialog('option', 'buttons', { "Закрыть": function() {
                $("#ucs_check_list span").remove();
                $(this).dialog("close");
            } });

        }, 'json');

        procent = 100;
    }

    $("#probar").css('width', procent+'%');
}

