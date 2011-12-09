

function restoreObj(obj) {

	if ($(obj).attr("class") == "right_restore_multi") {
 		if (issetChecked()){
      	 	$(obj).addClass("load_animate_multi");

		    $.post($(obj).attr("name"), $("#checked_form").serialize(), function(data){
		    	if (data == "delete") reloadTable();
		    });
      	}
	} else {
       	$.get($(obj).attr("href"), function(data){
         	if (data == "delete") reloadTable();
       });
	}
    return false;
}


function clearTrash() {

	ShowMessage(
	    	$("#clearTrashTitle").val(),
            $("#clearTrashText").val(),
            {
            	'Отмена': function() {
       				$(this).dialog('close');
       			},
       			'Очистить корзину': function() {
		          	$.get($('#admin_url').val()+'/trash/object_del', function(data){
		          		if (data == 'delete') reloadTable();
		          	});
       				$(this).dialog('close');
       			}
	       	}
	     );
}


