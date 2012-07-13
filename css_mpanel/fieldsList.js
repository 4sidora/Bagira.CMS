
var currGroup, currField;

// Добавление нового разделителя
function addSepar(group_id) {

	$.get($('#admin_url').val()+'/constructor/separator_proc_add/'+group_id, function(data){

		        if (data.error == '0') {

		        	$("#fgroup_"+group_id).append(data.data);

                    doSort();

                    $(".fieldsSortable").sortable( "refreshPositions" );
		        	//setEvents();

		        } else {
		        	alert(data.data);
		   		}
	}, 'json');

}

// Работа с формой Сепаратора
function changeSepar(obj_id, state, type) {

    $("#divForm").dialog( "destroy" );

    if (state == 'upd') {
    	currField = obj_id;
    	$('#divForm').attr('title', LangArray['CONSTR_SEPAR_TITLE_UPD']);
    } else {
    	currField = 'new';
		currGroup = obj_id;
		$('#divForm').attr('title', LangArray['CONSTR_SEPAR_TITLE_ADD']);
    }

	$("#divForm").dialog({
			autoOpen: false,
			width: 345,
			modal: true,
			buttons: {
				'Отмена': function() {
					$(this).dialog('close');
				},
				'Сохранить': function() {
					$("#ajaxSeparForm").submit();
				}
			}
	});
    $("#divForm").html('<img src="/css_mpanel/images/loading-small.gif" width="32" height="32" border="0" style="padding:141px;">');
    $('#divForm').dialog('open');


    $("#divForm").load($('#admin_url').val()+'/constructor/separator_'+state+'/'+obj_id, function(){

        if ($("#divForm").text() != ''){


            // Слайдер отступа
            $( "#max_size_span" ).text(($("#max_size").val() == 0) ? LangArray['CONSTR_SIZE_DEF'] : $("#max_size").val() + 'px');
            $( "#slider-range" ).slider({
                range: "min",
                value: $("#max_size").val(),
                min: 0,
                max: 60,
                slide: function( event, ui ) {
                    $("#max_size").val(ui.value);
                    $("#max_size_span").text((ui.value == 0) ? LangArray['CONSTR_SIZE_DEF'] : ui.value + 'px');
                }
            });


	    	$("#ajaxSeparForm").submit(function(){

                $.post($('#admin_url').val()+'/constructor', $("#ajaxSeparForm").serialize(), function(data) {

                    if (data.error == 0) {

                        if (currField == 'new') {
                            $("#fgroup_"+currGroup).append(data.data);
                        } else {
                            $("#field_"+currField).html(data.data);
                        }

                        $(".fieldsSortable").sortable( "refreshPositions" );
                        $("#divForm").dialog('destroy');

                    } else
                        alert(data.data);

		        }, 'json');

                return false;
            });

			$('#fname').focus();

		} else alert(LangArray['CONSTR_FORM_LOAD_ERROR']);

    });
}



// Работа с формой ПОЛЯ
function changeField(obj_id, state, type) {

    $("#divForm").dialog( "destroy" );
    
    if (state == 'upd') {
    	currField = obj_id;
    	$('#divForm').attr('title', LangArray['CONSTR_FIELD_TITLE_UPD']);
    } else {
    	currField = 'new';
		currGroup = obj_id;
		$('#divForm').attr('title', LangArray['CONSTR_FIELD_TITLE_ADD']);
    }

	$("#divForm").dialog({
			autoOpen: false,
			width: 345,   //710
			modal: true,
			buttons: {
				'Отмена': function() {
					$(this).dialog('close');
				},
				'Сохранить': function() {
					$("#ajaxFieldForm").submit();
				}
			}
	});
    $("#divForm").html('<img src="/css_mpanel/images/loading-small.gif" width="32" height="32" border="0" style="padding:141px;">');
    $('#divForm').dialog('open');


    $("#divForm").load($('#admin_url').val()+'/constructor/field_'+state+'/'+obj_id, function(){

        if ($("#divForm").text() != ''){

	    	$("#ajaxFieldForm").RSV({
		          customErrorHandler: smFieldRVS,
		          rules: [
		          	"required,fname,"+LangArray['CONSTR_FIELD_FORM_ERROR_1'],
		           	"required,fsname,"+LangArray['CONSTR_FIELD_FORM_ERROR_2']
		          ]
		    });

            // Слайдер высоты
            $("#max_size_span").text(($("#max_size").val() == 0) ? LangArray['CONSTR_SIZE_DEF'] : $("#max_size").val() + 'px');
            $("#slider-range").slider({
                range: "min",
                value: $("#max_size").val(),
                min: 0,
                max: 700,
                step: 50,
                slide: function( event, ui ) {
                    $("#max_size").val(ui.value);
                    $("#max_size_span").text((ui.value == 0) ? LangArray['CONSTR_SIZE_DEF'] : ui.value + 'px');
                }
            });

            $("body").on("change", "#fname", function (){
		    	Translit("#fname", "#fsname");
		    });

		    $("#type").change(function (){

		    	$("#divList").hide();
		    	$("#divRelType").hide();
		    	$("#divSize").hide();
                $("#divSizeSlide").hide();
		    	$("#quick_add2").hide();

		    	if ($("#hint").val() == LangArray['CONSTR_FIELD_LIST_HINT'])
		    			$("#hint").val('');

		    	if ($(this).val() == 90 || $(this).val() == 95 || $(this).val() == 97) {

                    if ($(this).val() == 90 || $(this).val() == 95)
                    	$("#divRelType").show();

		    		$("#divList").show();
		    		$("#quick_add2").show();
		    		if ($("#hint").val() == '' && $(this).val() == 95)
		    			$("#hint").val(LangArray['CONSTR_FIELD_LIST_HINT']);

				} else if ($(this).val() > 69 && $(this).val() < 86)
		    		$("#divSize").show();

                else if ($(this).val() == 55 || $(this).val() == 60)
                    $("#divSizeSlide").show();


		    });

			$('#fname').focus();

		} else alert(LangArray['CONSTR_FORM_LOAD_ERROR']);

    });
}

// Обработчик отправки запроса на изменение ПОЛЯ
function smFieldRVS(f, errorInfo) {

    if (errorInfo.length > 0) {

	    alert(errorInfo[0][1]);
        $(errorInfo[0][0]).focus();

    } else {

        $.post($('#admin_url').val()+'/constructor', $("#ajaxFieldForm").serialize(), function(data) {

		        if (data.error == 0) {
                 
                  //  $(".fieldsSortable").sortable( "disable" );
		        	if (currField == 'new') {
                    	$("#fgroup_"+currGroup).append(data.data);

                        doSort();

		        	} else {
                        $("#field_"+currField).html(data.data);

		        	}
		        //	$(".fieldsSortable").sortable( "enable" );
                    $(".fieldsSortable").sortable( "refreshPositions" );

		        	$("#divForm").dialog('destroy');
		        	//setEvents();

		        } else {
		        	alert(data.data);
		   		}
            
		}, 'json');

    }
    return false;
}

function delField(obj_id) {

    ShowMessage(LangArray['CONSTR_FIELD_DEL_TITLE'], LangArray['CONSTR_FIELD_DEL_TEXT'],
      	{
            'Отмена': function() { $(this).dialog('close'); },
	        'Удалить элемент': function() {
                  $.get($('#admin_url').val()+'/constructor/field_del/'+obj_id, function(data){
                       // alert(data);

                   if (data == 'ok')
                    	$("#field_"+obj_id).remove();
                    else
                    	 ShowMessage(LangArray['CONSTR_FIELD_DEL_TITLE'], LangArray['CONSTR_FIELD_DEL_ERROR']);
                  });
                  $(this).dialog('close');
		}
       });

}





// Работа с формой ГРУППЫ   --------------------------------------------------------
function changeGroup(obj_id, state) {

     if (state == 'upd') {
    	currGroup = obj_id;
    	$('#divForm').attr('title', LangArray['CONSTR_GROUP_TITLE_ADD']);
    } else {
		currGroup = 'new';
		$('#divForm').attr('title', LangArray['CONSTR_GROUP_TITLE_UPD']);
    }

    $("#divForm").dialog({
			autoOpen: false,
			width: 350,
			modal: true,
			buttons: {
				'Отмена': function() {
					$(this).dialog('close');
				},
				'Сохранить': function() {
					$("#ajaxGroupForm").submit();
				}
			}
	});
    $("#divForm").html('<img src="/css_mpanel/images/loading-small.gif" width="32" height="32" border="0" style="padding:65px;padding-right:140px;padding-left:140px;">');
    $('#divForm').dialog('open');
    $("#divForm").load($('#admin_url').val()+'/constructor/fgroup_'+state+'/'+obj_id, function(){
        if ($("#divForm").text() != ''){

	    	$("#ajaxGroupForm").RSV({
		          customErrorHandler: smGroupRVS,
		          rules: [
		          	"required,group_name,"+LangArray['CONSTR_GROUP_FORM_ERROR_1'],
		           	"required,group_sname,"+LangArray['CONSTR_GROUP_FORM_ERROR_2']
		          ]
		    });

            $("body").on("change", "#group_name", function (){
		    	Translit("#group_name", "#group_sname");
		    });

			$('#group_name').focus();

		} else alert(LangArray['CONSTR_FORM_LOAD_ERROR']);
    });
}

// Обработчик отправки запроса на изменение группы
function smGroupRVS(f, errorInfo) {

    if (errorInfo.length > 0) {

	    alert(errorInfo[0][1]);
        $(errorInfo[0][0]).focus();

    } else {

        $.post($('#admin_url').val()+'/constructor', $("#ajaxGroupForm").serialize(), function(data) {

		        if (data.error == 0) {

		        	if (currGroup == 'new') {
                    	$("#groupsSortable").append(data.data);
		        	} else {
                        $("#group_"+currGroup+" .title").html(data.data);
		        	}

		        	$("#groupsSortable").sortable( "refresh" );
		        	$("#divForm").dialog('destroy');
		        	//setEvents();

		        } else  {
		        	alert(data.data);
		   		}

		}, 'json');
    }
    return false;
}

function delGroup(group_id) {

    ShowMessage(LangArray['CONSTR_GROUP_DEL_TITLE'], LangArray['CONSTR_GROUP_DEL_TEXT'],
      	{
	         'Отмена': function() { $(this).dialog('close'); },
	         'Удалить элемент': function() {
	               $.get($('#admin_url').val()+'/constructor/fgroup_del/'+group_id, function(data){

	                 if (data == 'ok')
	                 	$("#group_"+group_id).remove();
	                 else
	                 	 ShowMessage(LangArray['CONSTR_GROUP_DEL_TITLE'], LangArray['CONSTR_GROUP_DEL_ERROR']);
	               });
	               $(this).dialog('close');
			}
       });

}

function doSort() {
    // Сортировака списка полей
    $(".fieldsSortable").sortable({
        connectWith: '.fieldsSortable',
        handle: $('.fieldsSortable > li > .titl > a, .fieldsSortable > li > .titl > div'),
        placeholder: '.fieldsSortable li',
        stop: function(event, ui) {

            var field_id = $(ui.item).attr("name");
            var group_id = $(ui.item).parent().attr("name");

            var num = curr_pos = 0;
            $("#fgroup_"+group_id+" > li").each(function(){
                num++;
                if ("field_" + field_id == this.id)
                    curr_pos = num;
            });

            $.get($('#admin_url').val()+'/constructor/field_moveto/'+field_id+'/'+curr_pos+'/'+group_id, function(data){
                if (data != 'ok') alert(LangArray['CONSTR_FIELD_MOVETO_ERROR']);
                //alert(data);
            });
        }
    });
}

$(function () {

    // Сортировака списка групп
	$("#groupsSortable").sortable({
			handle: '.title',
			placeholder: 'ui-state-highlight',
			stop: function(event, ui) {

			    var group_id = $(ui.item).attr("name");

                var num = curr_pos = 0;
                $("#groupsSortable > li").each(function(){
                	num++;
                 	if ("group_" + group_id == this.id)
                 		curr_pos = num;
                });

			    $.get($('#admin_url').val()+'/constructor/fgroup_moveto/'+group_id+'/'+curr_pos, function(data){
			    	if (data != 'ok') alert(LangArray['CONSTR_GROUP_MOVETO_ERROR']);
			    	//alert(data);
			    });
			}
	});

    doSort();

	//setEvents();

});
           /*
// Обрабатываем события при наведении на строку
function setEvents(){

	$(".fieldsSortable li").mouseover(function() {
			$(this).find(".edits").show();
	}).mouseout(function() {
			$(this).find(".edits").hide();
	});

	$("#groupsSortable .title").mouseover(function() {
			$(this).find(".groupEdits").show();
	}).mouseout(function() {
	     $(this).find(".groupEdits").hide();
	});

}
*/