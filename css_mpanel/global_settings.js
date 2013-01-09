jQuery(document).ready(function() {

    var rules = [];
	rules.push("function,checkFields");

	$("#changeForm").RSV({
 		customErrorHandler: ShowMessageRVS,
        rules: rules
    });


    $(".header_tree").click(function(){

        var obj_id = $(this).attr('name');

        $('#divForm').attr('title', LangArray['SETTINS_DOM_TITLE']);
	    $("#divForm").dialog({
				autoOpen: false,
				width: 350,
				position: ["center",50],
				modal: true,
				buttons: {
					'Отмена': function() {
						$(this).dialog('close');
					},
					'Сохранить': function() {
						saveDomain(obj_id);
					}
				}
		});

	    $("#divForm").html('<img src="/css_mpanel/images/loading-small.gif" width="32" height="32" border="0" style="padding:65px;padding-right:140px;padding-left:140px;">');
	    $('#divForm').dialog('open');


	    $("#divForm").load($('#admin_url').val()+'/core/change/edit/'+obj_id, function(){
	        if ($("#divForm").text() == '')
	        	alert(LangArray['CONSTR_FORM_LOAD_ERROR']);
	    });

    });
});

function saveDomain(obj_id){

    $.post(
        $('#admin_url').val()+'/core/change/edit/'+obj_id,
        $("#changeDomainForm").serialize(),
        function(responseText) {
		    $("#divForm").dialog('destroy');
		}
    );
}

// Показываем сообщение о том, что нужно подумать по поводу удаления языков
function saveConfig(){
    $("#parramForm").val('save');
    var send_form = true;

	$(".delete_langs::checked").each(function(){
    	send_form = false;
    	ShowMessage(LangArray['SETTINS_DEL_LANG'], LangArray['SETTINS_DEL_LANG2'],
    			{
					'Отмена': function() {
						$(this).dialog('destroy');
					},
					'Удалить': function() {
						$(this).dialog('destroy');
						checkDelDomains();
					}
				});
    });

    if (send_form)
	   	checkDelDomains();
}

// Показываем сообщение о том, что нужно подумать по поводу удаления доменов
function checkDelDomains(){
    var send_form = true;

    $(".delete_domains::checked").each(function(){
    	send_form = false;
    	ShowMessage(LangArray['SETTINS_DEL_DOMAIN'], LangArray['SETTINS_DEL_DOMAIN2'],
    			{
					'Отмена': function() {
						$(this).dialog('destroy');
					},
					'Удалить': function() {
						$(this).dialog('destroy');
						$("#changeForm").submit();
					}
				});
    });

    if (send_form)
	   	$("#changeForm").submit();
}

// Проверка введенных данных
function checkFields(){

	var ret = true;

    // Проверка для изменяемых доменов
    $(".d_name").each(function(){
    	if ($(this).val() == '')
    		ret = [[ $(this).attr('id'), LangArray['SETTINS_DOM_NAME'] ]];
    });

    $(".d_sitename").each(function(){
    	if ($(this).val() == '')
    		ret = [[ $(this).attr('id'), LangArray['SETTINS_DOM_SITENAME'] ]];
    });

    $(".d_email").each(function(){
    	if ($(this).val() == '')
    		ret = [[ $(this).attr('id'), LangArray['SETTINS_DOM_EMAIL'] ]];
    	else if (!isValidEmail($(this).val()))
		    ret = [[ $(this).attr('id'), LangArray['SETTINS_DOM_EMAIL2'] ]];
    });

    // Проверка для добавляемых доменов
    $(".oldnew").each(function(){

    	if ($(this).val() != '') {

    	    $(this).parent().parent().find(".new_d_sitename").each(function(){
		    	if ($(this).val() == '')
		    		ret = [[ $(this).attr('id'), LangArray['SETTINS_DOM_SITENAME'] ]];

		    });

            if (ret == true)
		    $(this).parent().parent().find(".new_d_email").each(function(){
		    	if ($(this).val() == '')
		    		ret = [[ $(this).attr('id'), LangArray['SETTINS_DOM_EMAIL'] ]];
				else if (!isValidEmail($(this).val()))
		    		ret = [[ $(this).attr('id'), LangArray['SETTINS_DOM_EMAIL2'] ]];

		    });
    	}
    })


    // Проверка для изменяемых языков
    $(".l_name").each(function(){
    	if ($(this).val() == '')
    		ret = [[ $(this).attr('id'), LangArray['SETTINS_LANG_NAME'] ]];
    });

    $(".l_prefix").each(function(){
    	if ($(this).val() == '')
    		ret = [[ $(this).attr('id'), LangArray['SETTINS_LANG_PREFIX'] ]];
    });

    // Проверка для добавляемых доменов
    $(".oldnew").each(function(){
    	if ($(this).val() != '') {
    	    $(this).parent().parent().find(".new_l_prefix").each(function(){
		    	if ($(this).val() == '')
		    		ret = [[ $(this).attr('id'), LangArray['SETTINS_LANG_PREFIX'] ]];
		    });
    	}
    })

  	return ret;
}
