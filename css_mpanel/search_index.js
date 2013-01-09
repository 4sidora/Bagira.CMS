

var send_flag, send_max_flag, procent, curURL, stopIndex;

function startIndex() {

    stopIndex = false;
    curURL = $("#admin_url").val()+'/search/index_proc/';

	$("#count_message").html(LangArray['SEARCH_MSG_2']);
	$("#probar").css('width', '0%');

	$.get(curURL + 'start', function(data) {
		send_flag = 0;
		send_max_flag = data;
		sendProc(0);
	});

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
	     	'Остановить индексацию': function() {
	     	    stopIndex = true;
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

       if (!stopIndex)
		   $.ajax({
		      url: curURL + send_flag,
		      success: sendProc,
		      error: errorProc
		   });

	   send_flag ++;
	   procent = (send_flag / (send_max_flag / 100)).toFixed(0);

	   $("#count_message").html(LangArray['SEARCH_MSG_4']+send_flag+LangArray['SEARCH_MSG_5']+send_max_flag+LangArray['SEARCH_MSG_6']);

	} else {

		$.get(curURL + 'info', function(data) {
			$("#count_pages").text(data.pages);
			$("#index_date").text(data.data);
			$("#count_words").text(data.words);
		}, 'json');

       	procent = 100;
       	$("#back_url").show();
	   	$("#count_message").html(LangArray['SEARCH_MSG_3']);
	   	$('#progressBar').dialog('option', 'buttons', { "Закрыть": function() { $(this).dialog("close"); } });
	}

	$("#probar").css('width', procent+'%');
}