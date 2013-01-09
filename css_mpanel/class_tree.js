
$(function () {


	// Обрабатываем события при наведении на строку
 	$("ul.classesTree li > div").mouseover(function() {

    	$(this).addClass("lineActive");
        $("#edits_"+$(this).attr("name")).show();

        return true;

    }).mouseout(function() {

        $("#edits_"+$(this).attr("name")).hide();
        $(this).removeClass("lineActive");

        return true;

    });

    // Удаление объекта
    $("body").on("click", ".del_button", function() {

        var id = $(this).attr('name');
        var url = $(this).attr('href');
        var obj_name = $('#line_class_'+id+' > a').text();
        var text = LangArray['CONSTR_CLASS_DEL_TEXT'];

    	ShowMessage(
	    	LangArray['CONSTR_CLASS_DEL_TITLE'],
            text.replace('&name&', obj_name),
            {
            	'Отмена': function() {
       				$(this).dialog('close');
       			},
       			'Удалить': function() {
		          	$.get(url, function(data){
		          		if (data == 'ok') {
				          	$('#li_class_'+id).remove();
			          	} else if (data != '') alert(data);
		          	});
       				$(this).dialog('close');
       			}
	       	}
	     );

	     return false;
    });

});