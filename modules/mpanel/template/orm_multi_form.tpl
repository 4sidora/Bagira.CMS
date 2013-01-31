<?php


$TEMPLATE['form_frame'] = <<<END
<script language="javascript">
jQuery(document).ready(function() {

    var rules = [];
	%java_rules%
	rules.push("function,checkRequred");

	$("#%form_name%Form").RSV({
 		customErrorHandler: ShowMessageRVS,
        rules: rules
    });

});

%addit_function%

function checkRequred(){
	var fields = [%requred_field%];
	var ret = true;
    var num = 0;
    while (num < fields.length) {

        if ($("#name_"+fields[num][1]).val() != '') {

            if (fields[num].length > 4 && $("#objectsLinkList_" + fields[num][0] + " > li").length == 0) {

                var field = document.getElementById(fields[num][0]);
                ret = [[ field, fields[num][2] ]];
                break;

            }

            if ($("#"+fields[num][0]).val() == fields[num][3]) {

                var field = document.getElementById(fields[num][0]);
                ret = [[ field, fields[num][2] ]];
                break;
            }
        }
	    num++;
  	}
  	return ret;
}

</script>

<div class="onetabs">
    <div class="ins" style="padding-left:20px;">

<form id="%form_name%Form" name="%form_name%Form" action="" method="post" enctype="multipart/form-data">

 	%html%

 	<input name="parram" id="parramForm" type="hidden" value="">
 	<input name="right" type="hidden" value="%mright%">
</form>

	</div><!-- end ins-->
    <div class="clear"></div>
</div><!-- end tab -->
END;

$TEMPLATE['frame'] = <<<END
<script language="javascript">
var new_field_%form_name% = '';
jQuery(document).ready(function() {

    new_field_%form_name% = '<div class="el_lines">'+$("#mainlist_%form_name% > .el_lines:last").html()+'</div><div class="clear"></div>';

    $(".new").live('change', function(){
    	if ($(this).val() != '') {
            var num = parseInt($("#newobjs_%form_name%").val()) + 1;
			var re = /new1/g;
			new_form = new_field_%form_name%.replace(re, 'new'+num);
			$("#mainlist_%form_name%").append(new_form);
			$("#newobjs_%form_name%").val(num);
			$(this).removeClass("new");
        }
    });

});
</script>

<div class="el_lines" style="height:20px;">
     %columns%
     %column_del%
</div>

<div class="clear"></div>

<div id="mainlist_%form_name%">

    %lines%

</div>
    <input name="class_%form_name%" type="hidden" value="%mclass%">
 	<input id="newobjs_%form_name%" type="hidden" value="1">
 	%params%
END;


$TEMPLATE['params'] = <<<END
<input name="params_%form_name%[]" type="hidden" value="%val%">
END;


$TEMPLATE['colums'] = <<<END
<div style="float:left; width:%width%px;"><b>%title%</b></div>
END;

$TEMPLATE['colum_del'] = <<<END
<div style="width:85px; margin-left:14px; float:left;"><b>X</b></div>
END;

// Подсказка
$TEMPLATE['acronym'] = <<<END
	<acronym title="%hint%" style="cursor:help;">%title% </acronym>
END;


$TEMPLATE['lines'] = <<<END
<div class="el_lines">
	%object% %del_check%
</div>
<div class="clear"></div>
END;

$TEMPLATE['del_check'] = <<<END
<div style="width:20px;margin-left:10px;" class="cell_el">
	<input name="delete_%obj_id%" type="checkbox" value="1">
</div>
END;







$TEMPLATE['field_frame'] = <<<END
<div style="width:%width%px;height:auto !important;min-height:20px;display:block;" class="cell_el">%content%</div>
END;

// Строка	E-mail    URL
$TEMPLATE['field_10'] = $TEMPLATE['field_15'] = $TEMPLATE['field_20'] = <<<END
<input class="input%new%" type="text" name="obj%form_name%[%obj_id%][%field.sname%]" id="%field.sname%_%obj_id%" value="%field.value%" style="width:%width%px;">
END;

// Дата
$TEMPLATE['field_25'] = <<<END
<input class="inputfield check_date" type="text" name="obj%form_name%[%obj_id%][%field.sname%]" id="%field.sname%_%obj_id%" value="%field.date%" style="width:%width%px;">
END;

// Время
$TEMPLATE['field_30'] = <<<END
<input class="inputfield check_time" type="text" name="obj%form_name%[%obj_id%][%field.sname%]" id="%field.sname%_%obj_id%" value="%field.time%" style="width:%width%px;">
END;

// Дата   и  Время
$TEMPLATE['field_32'] = <<<END
<input class="input check_date" type="text" name="obj%form_name%[%obj_id%][%field.sname%_date]" id="%field.sname%_%obj_id%" value="%field.date%" style="width:100px;">
<input class="input check_time" type="text" name="obj%form_name%[%obj_id%][%field.sname%_time]" id="%field.sname%_time_%obj_id%" value="%field.time%" style="width:50px;">
END;

// Пароль
$TEMPLATE['field_35'] = <<<END
<input class="input" type="password" name="obj%form_name%[%obj_id%][%field.sname%]" id="%field.sname%_%obj_id%" value="" style="width:%width%px;" autocomplete="off">
END;

// Число        Число с точкой
$TEMPLATE['field_40'] = $TEMPLATE['field_45'] = $TEMPLATE['field_47'] = <<<END
<input class="input" type="text" name="obj%form_name%[%obj_id%][%field.sname%]" id="%field.sname%_%obj_id%" value="%field.value%" style="width:%width%px;">
END;

// Флажок
$TEMPLATE['field_50'] = <<<END
<center>%element%</center>
END;

// Большой текст
$TEMPLATE['field_55'] = <<<END
<textarea name="obj%form_name%[%obj_id%][%field.sname%]" id="%field.sname%_%obj_id%" style="width:%width%px;height:65px;">%field.value%</textarea>
END;

// HTML – текст
$TEMPLATE['field_60'] = <<<END
<textarea name="obj%form_name%[%obj_id%][%field.sname%]" id="%field.sname%_%obj_id%" style="width:%width%px;height:65px;">%field.value%</textarea>
END;

// Файл   Список файлов   Изображение    Видео    Флеш-ролик
$TEMPLATE['field_70'] = $TEMPLATE['field_75'] = $TEMPLATE['field_80'] = $TEMPLATE['field_85'] = <<<END
%element%
END;

// Список файлов
$TEMPLATE['field_73'] = <<<END
 %element%
END;



// Выпадающий список
$TEMPLATE['field_90'] = <<<END
%element%
%plus%
END;

$TEMPLATE['field_90_plus'] = <<<END

<input type="text" class="addinput" id="%field.sname%_%obj_id%_new_val" name="%field.sname%_%obj_id%_new_val" style="display:none;width:%width_plu%px;">

<span class="ok_value" onClick="return doAddNewHandbook('%field.sname%_%obj_id%');" style="display:none;"></span>
<span class="add_value" id="add_new_%field.sname%_%obj_id%" onClick="return AddNewHandbook(this, '%field.sname%_%obj_id%');"></span>

END;



// Список со множественным выбором
$TEMPLATE['field_95'] = <<<END

%element%
%plus%

END;

$TEMPLATE['field_95_plus'] = <<<END


<textarea id="%field.sname%_%obj_id%_new_val" name="%field.sname%_new_val" class="minitext" style="display:none;width:%width_plu%px;" wrap="on"></textarea>

<span class="ok_value" onClick="return doAddNewHandbook('%field.sname%_%obj_id%');" style="display:none;"></span>
<span class="add_value" id="add_new_%field.sname%_%obj_id%" onClick="return AddNewHandbook(this, '%field.sname%_%obj_id%');"></span>

END;




// Выбор страницы
$TEMPLATE['field_100'] = <<<END

        %element%

END;


// Теги
$TEMPLATE['field_105'] = <<<END

<input class="input%new%" type="text" name="obj%form_name%[%obj_id%][%field.sname%]" id="%field.sname%_%obj_id%" value="%field.value%" style="width:%width%px;">

END;






?>