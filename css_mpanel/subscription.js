

var send_flag, send_max_flag, procent, curURL, stopMailing;

function showForm() {

		$("#divForm").dialog({
				autoOpen: false,
				width: 350,
				modal: true,
				buttons: {
					'Отмена': function() {
						$(this).dialog('close');
					},
					'Разослать сообщения': function() {
						$(this).dialog('close');
						startMailing();
					}
				}
		});
	    $('#divForm').dialog('open');

}

function startMailing() {

    stopMailing = false;
    curURL = $("#admin_url").val() + '/subscription/msg_proc_send/' + $("#release_id").val() + '/';

	$("#count_message").html(LangArray['SEARCH_MSG_2']);
	$("#probar").css('width', '0%');

	$.post(
		curURL + 'start',
		{
			"subject" : $("#subject").val(),
			"part" : $("#part").val()
		},
		function(data) {
            if (data.error == 0) {

				send_flag = data.start * 1 - 1;
				send_max_flag = data.count * 1;
				if (send_flag < 0) send_flag = 0;

				sendProc(0);

			} else alert('error!');
		},
        'json'
	);

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
	     	'Остановить рассылку': function() {
	     	    stopMailing = true;
	     	    $("#epn").text($("#part").val());
        		$("#part_count").hide();
        		$("#part_count_text").show();
	     		$(this).dialog('close');
	     	}
	     }
	});

}

function errorProc(){

	var yes = confirm(LangArray['SEARCH_MSG_1']);

	if (yes) {
	   $.ajax({
	      url: curURL + send_flag,
	      success: sendProc,
	      error: errorProc
	   });
	}
}

function sendProc(parram){

	if (send_flag < send_max_flag) {

       send_flag ++;

       if (!stopMailing)
		   $.ajax({
		      url: curURL + send_flag,
		      type: "POST",
		      success: sendProc,
		      error: errorProc
		   });

	   procent = (send_flag / (send_max_flag / 100)).toFixed(0);

	   $("#count_message").html(LangArray['SUBSCR_MSG_4'] + procent + LangArray['SUBSCR_MSG_5']);

	} else {

        // Завершаем работу с рассылкой
        $.get(curURL + 'stop', function (data) {

	        if ($('#error_part_num').val() != 0) {
	        	$("#part_count").show();
	        	$("#part_count_text").hide();
	        }
	        var part = $("#part").val() * 1 + 1;
	        if (part > ($("#count_part").val() * 1)) part = 1;
	        $("#part").val(part);
	        $("#pn").text(part);

	       	procent = 100;
	       	$("#back_url").show();
		   	$("#count_message").html(LangArray['SUBSCR_MSG_3']);
		   	$('#progressBar').dialog('option', 'buttons', { "Закрыть": function() { $(this).dialog("close"); } });

	 	});
	}

	$("#probar").css('width', procent+'%');
}