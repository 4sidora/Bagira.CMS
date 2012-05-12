

function trySelectAll() {

	var check = true;
 	$(".check_view").each(function() {
  		if ($(this).attr("checked") == false) check = false;
  	});
    $("#bigcheck_view").attr("checked", check);
}

jQuery(document).ready(function() {

	// Активация чекбоксов для ПРОСМОТРА
    $("body").on("click", "#bigcheck_view", function() {
          $(".check_view").attr("checked", $(this).attr("checked"));
          if (!$(this).attr("checked")) {
          	$(".check_edit").attr("checked", false);
          	$("#bigcheck_edit").attr("checked", false);
          }
    });

    $("body").on("click", ".check_view", function() {
          if (!$(this).attr("checked")) {
          	$("#cd_edit_"+$(this).val()).attr("checked", false);
          	$("#bigcheck_edit").attr("checked", false);
          }
          trySelectAll();
    });


    // Активация чекбоксов для РЕДАКТИРОВАНИЯ
    $("body").on("click", "#bigcheck_edit", function() {
          $(".check_edit").attr("checked", $(this).attr("checked"));
          if ($(this).attr("checked")) {
          	$(".check_view").attr("checked", true);
          	$("#bigcheck_view").attr("checked", true);
          }
    });

    $("body").on("click", ".check_edit", function() {

          if ($(this).attr("checked")) {
          	$("#cd_view_"+$(this).val()).attr("checked", true);
            trySelectAll();
          }

          var check = true;
          $(".check_edit").each(function() {
          	if ($(this).attr("checked") == false) check = false;
          });
          $("#bigcheck_edit").attr("checked", check);
    });




	$('#find_user').autocomplete({
	    serviceUrl: $("#current_url").val(),
	    minChars: 2,
	    delimiter: /(,|;)\s*/,
	    maxHeight: 400,
	    width: 300,
	    zIndex: 9999,
	    deferRequestBy: 300,
	    onSelect: function(data, value){

	    	$.post($("#current_url").val(), {'user_name': data}, function(html) {

	    		var id = $(".check_edit", html).val();

	    		if (!$("#cd_edit_"+id).val()) {

	    			$(html).appendTo('#new_lines');
	    			$("#bigcheck_edit").attr("checked", false);

	    		} else
	    			ShowAlert(LangArray['STRUCTURE_RIGHT_MSG']);

	    		$('#find_user').val('');

	    	});
	    },
	});


});











