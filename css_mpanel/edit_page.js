function translite(val){
            var r = new RegExp("[^A-Za-zА-Яа-я0-9_ -]*", "g");
            var RArrayL = "абвгдеёжзийклмнопрстуфхцчшщьыъэюя -";
            var RArrayU = "АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЬЫЪЭЮЯ -";
            var res = "";
            var tttt = String();
            var str = String();
            var arr = new Array("a", "b", "v", "g", "d", "e", "yo", "zh", "z", "i", "y", "k", "l", "m", "n", "o", "p", "r", "s", "t", "u", "f", "h", "ts", "ch", "sh", "shch", "", "y", "", "e", "yu", "ya", "-", "-");

            tttt = val.replace(r, "");
            for (var i = 0; i < tttt.length; ++i) {
                  p = RArrayL.search(tttt.substring(i, i+1));
                  if (p == -1)
                     p = RArrayU.search(tttt.substring(i, i+1));
                  if (p != -1)
                     res = res + arr[p];
                  else {
                    str = tttt.substring(i, i+1);
                    res = res + str;
                  }
            }
            return res.replace('--', "").replace('--', '-').toLowerCase();
}


jQuery(document).ready(function() {

	$("#name").live('change', function (){

            if ($("#title").val() == $("#old_name_val").val())
                $("#title").val($(this).val());

            if ($("#h1").val() == $("#old_name_val").val())
                $("#h1").val($(this).val());

            if ($("#pseudo_url").val() == translite($("#old_name_val").val()))
                $("#pseudo_url").val(translite($(this).val()));

            $("#old_name_val").val($(this).val());

	});

	$("#showLink, #hideLink").click(function (){
                 //   #pseudo_url,
	    var divs = $("#h1, #title, #keywords, #description, #tags").parent();

		if ($(this).attr('id') == 'hideLink') {

	 		$(divs).hide();
	   		$("#showLink").show();
	        $("#hideLink").hide();
	        $.get($('#admin_url').val()+'/showhide/0');

		} else {

	     	$(divs).show();
	      	$("#showLink").hide();
	       	$("#hideLink").show();
	       	$.get($('#admin_url').val()+'/showhide/1');
		}
	});



});



// Функции для работы с шаблонами страниц

// Показать форму быстрого добавления шаблона
function AddNewTemplate(elem, id) {
    id = (id == 1)? 'template2_id' : 'template_id';

   	if ($("#"+id).css("display") == "none") {

   		$("#"+id).show();
   		$("#"+id+"_edit_block").hide();
    	$("#"+id+"_new_val").val('');
    	$("#"+id+"_new_val2").val('');

    	$(elem).removeClass('del_value').addClass('add_value');

   	} else {

    	$("#"+id).hide();
    	$("#"+id+"_edit_block").show();
    	$("#"+id+"_new_val").focus();

    	$(elem).removeClass('add_value').addClass('del_value');
    }

    return false;
}



