

jQuery(document).ready(function() {

	// постраничная навигация (горячие кнопки)
	$(document).keydown( function (event) {
        var curnum = parseInt($("#pn_curnum").attr('name'));
		if (window.event) event = window.event;
		if (event.ctrlKey)
		  	switch (event.keyCode ? event.keyCode : event.which ? event.which : null) {
		   		case 0x25:
		      		if (curnum > 1)
		      			reloadTable({ 'page_num': curnum - 1 });
		        break;
		        case 0x27:
		            if (curnum < $("#count_page").val())
		            	reloadTable({ 'page_num': curnum + 1 });
		        break;
		     }
	});

    // Поиск
	$("#table_search").keydown(function(e) {
		if (e.which == 13) {
			reloadTable({'table_search': $("#table_search").val()});
			return false;
		}
	});



	$('.expsearch').click(function(){
		if ($('#moresearch').val() == '1'){
		    $(this).text('расширенный поиск');
			$('.moresearch').attr('class', 'moresearch');
			$.post($("#current_url").val(), {'showfilter' : 0});
			$('#moresearch').val(0);
		} else {
		    $(this).text('обычный поиск');
			$('.moresearch').attr('class', 'moresearch mores');
			$.post($("#current_url").val(), {'showfilter' : 1});
			$('#moresearch').val(1);
		}

	})


	// Фильтры
	$("ins > input").keydown(function(e) {
		if (e.which == 13) {
            
			reloadTable($("#filter_form").serialize());
           // alert(456);
			return false;

		}
	});


    $(".selectbox_filter").change(function() {
    	reloadTable($("#filter_form").serialize());
	});


	setTableEvents();

});

function stopSearch(){
    $("#table_search").val('');
    $("ins > input").val('');
    $("code ins:eq(3)").remove();
    $(".selectbox_filter").val(0);
	reloadTable({'clear_search': 1});
	$("#table_search").focus();
	return false;
}

function ShowFilters(){
	$("#showfilter").val((($("#showfilter").val() == 1) ? 0 : 1));
	$("#addit_search").submit();
	return false;
}


function issetChecked() {

	var check = false;
 	$(".pointer2 > input:checkbox").each(function() {
  		if ($(this).attr("checked")) check = true;
  	});

   	if (!check)
    	ShowAlert($("#select_checkbox").val());

    return check;
}

function orderBy(obj, field, parram) {
    $(obj).attr("src", "/css_mpanel/tree/images/throbber.gif");
    reloadTable({ 'field': field, 'parram': parram });
}

function reloadTable(parram) {

    $.post($("#current_url").val(), parram, function(data){
		$("#table").html(data);
	});
}


// Устанавливаем события на основные элементы управления
function setTableEvents(){

 	// Обрабатываем события при наведении на строку

    $("body").on("mouseover", ".table_swich", function() {

    	var cur_id = $(this).attr("name");
        var cord = getOffsetRect(this);
		var addit = ($.browser.version == "7.0") ? 2 : 1;
        if ($.browser.version == "8.0") addit = 2;

        $("#table_edits").show();
        $("#table_edits").css("top", cord.top - 129 + addit);
        $("#table_edits").height($(".table_swich").height()-($(".table_swich").height()/4));
        $("#table_edits").css({paddingTop: +($(".table_swich").height()/4)+"px"});
        $("#table_edits").attr("name", cur_id);

        $("a.icon_edit").each(function(){
        	this.href = this.name + cur_id + $("#url_parram").val();
        });

        $(this).css({ backgroundColor: "#ffeeac"});

        return false;

	}).on("mouseout", ".table_swich", function() {

	    var cur_id = $(this).attr("name");

        $("#table_edits").hide();

        $(this).css({ backgroundColor: ""});

        return false;

	});


	// Обрабатываем события при наведении на блок с иконками
    $("body").on("mouseover", "#table_edits", function() {

	    $("#table_swich_"+$(this).attr("name")).css({ backgroundColor: "#ffeeac"});
     	$(this).show();

        return true;

    }).on("mouseout", "#table_edits", function() {

    	$("#table_swich_"+$(this).attr("name")).css({ backgroundColor: ""});
     	$(this).hide();

        return true;
	});

	// Постраничная навигация
    $("body").on("change", "#max_count", function() {
    	reloadTable({ 'max_count': $(this).val() });
	});

    $("body").on("click", ".table_page_navig", function() {
        reloadTable({ 'page_num': $(this).attr('name') });
		return false;
	});



    // Активация чекбоксов
    $("body").on("click", "#bigcheck", function() {
        var checked = ($(this).attr("checked")) ? true : false;
        $(".pointer2 > input:checkbox").attr("checked", checked);
    });

    $("body").on("click", ".pointer2 > input:checkbox", function() {
        var check = true;
        $(".pointer2 > input:checkbox").each(function() {
            if (!$(this).attr("checked")) check = false;
        });
        $("#bigcheck").attr("checked", check);
    });

    // Активность объекта
    $("body").on("click", ".ks_act", function() {

        var knopa = $(this);
        var id = $("#table_edits").attr("name");
        knopa.addClass("load_animate");

        $.get(knopa.attr("name"), function(data){

            var img = $("#phtml_"+id+"_"+$('#table_parent_id').val()+" > .active_div > .activate");
            var act = (data == 'active') ? '1' : '0';
            img.attr("src", '/css_mpanel/tree/images/file'+act+'.gif');
            img.attr("name", act);

            if (data == 'active') {
                knopa.removeClass('activ_elem_0');
                knopa.addClass('activ_elem_1');
            } else if (data == 'no_active') {
                knopa.removeClass('activ_elem_1');
                knopa.addClass('activ_elem_0');
            } else if (data != '') alert(data);

            knopa.removeClass("load_animate");
        });
    });

    // Множественное изменение активности
    $("body").on("click", ".right_active_multi", function() {

    	if (issetChecked()) {

            var knopa = $(this);
            knopa.addClass("load_animate_multi");

          	$.post(knopa.attr("name"), $("#checked_form").serialize(), function(data){

          		if (data == 'invert') {

		          	$(".pointer2 > input:checkbox").each(function() {
		          		if ($(this).attr("checked")) {

                            var img = $("#phtml_"+$(this).val()+"_"+$('#table_parent_id').val()+" > .active_div > .activate");
                            var act = ($(img).attr("name") == '0') ? '1' : '0';
             				img.attr("src", '/css_mpanel/tree/images/file'+act+'.gif');
                            img.attr("name", act);

		          		    var pimpochka = $("#pimpochka_"+$(this).val());
		          			if (pimpochka.hasClass("activ_elem_0")) {
				             	pimpochka.removeClass('activ_elem_0');
				             	pimpochka.addClass('activ_elem_1');
				            } else {
				                pimpochka.removeClass('activ_elem_1');
				             	pimpochka.addClass('activ_elem_0');
				            }
		             	}
		          	});

	          	} else alert(data);

	          	knopa.removeClass("load_animate_multi");
          	});
    	}
    });

    // Удаление объекта
    $("body").on("click", "#del_button", function() {

        var id = $("#table_edits").attr("name");
        var obj_name = $('#table_swich_'+id+' > td.td_name').text();
        if (obj_name == '')
        	obj_name = $('#table_swich_'+id+' > td.first').text();


    	ShowMessage(
	    	$("#del_title").val(),
            str_replace('&name&', obj_name, $("#del_text").val()),
            {
            	'Отмена': function() {
       				$(this).dialog('close');
       			},
       			'Удалить': function() {
		          	$.get($("#del_button").attr("href"), function(data){
		          		if (data == 'delete') {

		          		    if ($.tree) {
						    	$(".main_line").each(function(){
						        	if ($(this).attr('name') == id)
						          		$.tree.focused().remove("#phtml_"+id+"_"+$(this).attr('rel'));
						        });
		          		    }
				          		//$.tree.focused().remove("#phtml_"+id);
				          	reloadTable();
			          	} else if (data != '') alert(data);
		          	});
       				$(this).dialog('close');
       			}
	       	}
	     );

	     return false;
    });

    // Множественное удаление объектов
    $("body").on("click", ".right_drop_multi", function() {

		if (issetChecked()) {

            var knopa = $(this);

            ShowMessage(
	            $("#del_title_multi").val(),
	            $("#del_text_multi").val(),
	            {
	            	'Отмена': function() {
	       				$(this).dialog('close');
	       			},
	       			'Удалить': function() {

	       			    knopa.addClass("load_animate_multi");

			          	$.post(knopa.attr("name"), $("#checked_form").serialize(), function(data){

			          		if (data == 'delete') {
					          	if ($.tree)
						          	$(".pointer2 > input:checkbox").each(function() {
						          		if ($(this).attr("checked")) {
						          			var obj_id = $(this).val();

						          			$(".main_line").each(function(){
						          			    if ($(this).attr('name') == obj_id)
						          					$.tree.focused().remove("#phtml_"+obj_id+"_"+$(this).attr('rel'));
						          			});

						          		}
						          		//	$.tree.focused().remove("#phtml_"+$(this).val());
						          	});
					          	reloadTable();
				          	} else alert(data);
			          	});

	       				$(this).dialog('close');
	       			}
	       		}
	       	);
  		}
    });


}




