<?php

$TEMPLATE['frame'] = <<<END

%javascript%
<script language="javascript">
  %addit_function%
jQuery(document).ready(function() {

    var rules = [];

	%java_rules%

	$("#changeForm").RSV({
          customErrorHandler: ShowMessageRVS,
          rules: rules
    });

    $("#name").focus();

    $("#showClassList").click(function(){

	     if ($("#page_class_list").css('display') == 'none')
	     	$("#page_class_list").show();
	     else
	     	$("#page_class_list").hide();

	   return false;
	});

	$(document).click(function(e){
 		if ($(e.target).attr('id') != 'page_class_list' && $(e.target).attr('id') != 'showClassList')
	    	$("#page_class_list").hide();
 	});

});

</script>

<form id="changeForm" name="changeForm" action="" method="post" enctype="multipart/form-data">

    %form%

 	<input name="parram" id="parramForm" type="hidden" value="">
 	<input name="right" type="hidden" value="%right%">
 	<input name="obj_id" type="hidden" value="%obj.id%">
 	<input name="class_id" type="hidden" value="%obj.class_id%">
</form>

END;

$TEMPLATE['javascript'] = <<<END
<script type="text/javascript" src="%url%"></script>
END;

$TEMPLATE['form_with_tabs'] = <<<END
<div id="tabs">

	<ul>
    	%groups%
    </ul>

	%fields%
</div>
END;

$TEMPLATE['simple_form'] = <<<END
<div class="onetabs">
    <div class="ins">
	%fields%
    </div><!-- end ins-->
    <div class="clear"></div>
</div><!-- end tab -->
END;


$TEMPLATE['group'] = <<<END
<li><a href="#tabs-%group.sname%">%group.name%</a><ins></ins></li>
END;

$TEMPLATE['fields_frame'] = <<<END
<div id="tabs-%group.sname%">
	<div class="ins">

 		%fields%

	</div><!-- end ins-->
	<div class="clear"></div>
</div>
END;

// Подсказка
$TEMPLATE['acronym'] = <<<END
	<acronym title="%field.hint%" style="cursor:help;">%field.name% </acronym>
END;

$TEMPLATE['showhide'] = <<<END

  <span id="showLink" %sh1%>%sh_text1%<b></b></span>
  <span id="hideLink" %sh2%>%sh_text2%<b></b></span>

	%class_list%

	<div class="clear"></div>
END;

$TEMPLATE['class_list'] = <<<END
  <span class="options_abs">
       <div class="options">
       <a class="combo" href="#" id="showClassList"><span>%class_name%</span></a>
           <div id="page_class_list">%class_list%</div>
       </div>
   </span>

END;


// Разделитель
$TEMPLATE['field_0'] = <<<END
	<div class="otstup"%size%></div>
END;

// Разделитель с текстом
$TEMPLATE['field_0_text'] = <<<END
    <div class="clear"></div>
<div class="line_abc"%size%><i><b>%title%</b></i></div>
END;

// Стандартный
$TEMPLATE['field_standart'] = <<<END
    <div class="fieldBox">
        <label for="%field.sname%" class="%field.dotted% %field.zvezd%" title="%field.hint%"><b></b>%field.name%</label>
    	%content%
    </div>
END;

$TEMPLATE['read_only'] = <<<END
%val% <input id="%field.sname%" name="%field.sname%" type="hidden" value="%value%">
END;

// Строка	E-mail    URL
$TEMPLATE['field_10'] = $TEMPLATE['field_15'] = $TEMPLATE['field_20'] = <<<END

	<div class="fieldBox"%sh_page%>
        <label for="%field.sname%" class="%field.dotted% %field.zvezd%" title="%field.hint%"><b></b>%field.name%</label>
    	<input class="input" type="text" name="%field.sname%" id="%field.sname%" value="%field.value%">
    </div>

END;

// Дата
$TEMPLATE['field_25'] = <<<END

    <div class="fieldBox">
        <label for="%field.sname%" class="%field.dotted% %field.zvezd%" title="%field.hint%"><b></b>%field.name%</label>
    	<input class="input check_date" type="text" name="%field.sname%" id="%field.sname%" value="%field.date%" style="width:160px;">
    </div>

END;

// Время
$TEMPLATE['field_30'] = <<<END
 	<div class="fieldBox">
        <label for="%field.sname%" class="%field.dotted% %field.zvezd%" title="%field.hint%"><b></b>%field.name%</label>
    	<input class="input" type="text" name="%field.sname%" id="%field.sname%" value="%field.time%" style="width:160px;">
    </div>
END;

// Дата   и  Время
$TEMPLATE['field_32'] = <<<END
 	<div class="fieldBox">
        <label for="%field.sname%" class="%field.dotted% %field.zvezd%" title="%field.hint%"><b></b>%field.name%</label>
         <div class="clear"></div>
    	<input class="input check_date" type="text" name="%field.sname%_date" id="%field.sname%_date" value="%field.date%" style="float:left;width:100px;">
   		<input class="input check_time" type="text" name="%field.sname%_time" id="%field.sname%_time" value="%field.time%" style="float:left;width:50px;">
    </div>
END;

// Пароль
$TEMPLATE['field_35'] = <<<END
 	<div class="fieldBox">
        <label for="%field.sname%" class="%field.dotted% %field.zvezd%" title="%field.hint%"><b></b>%field.name%</label>
    	<input class="input" type="password" name="%field.sname%" id="%field.sname%" value="" autocomplete="off">
    </div>
END;

// Число        Число с точкой
$TEMPLATE['field_40'] = $TEMPLATE['field_45'] = <<<END

	<div class="fieldBox">
        <label for="%field.sname%" class="%field.dotted% %field.zvezd%" title="%field.hint%"><b></b>%field.name%</label>
    	<input class="input" type="text" name="%field.sname%" id="%field.sname%" value="%field.value%">
    </div>

END;

// Цена
$TEMPLATE['field_47'] =  <<<END
	<div class="fieldBox">
        <label for="%field.sname%" class="%field.dotted% %field.zvezd%" title="%field.hint%"><b></b>%field.name%</label>
    	<input class="input" type="text" name="%field.sname%" id="%field.sname%" value="%field.value%">
    </div>
END;

// Флажок
$TEMPLATE['field_50'] = <<<END


    <div class="fieldBox">
    	<label for="%field.sname%" class="%field.dotted% %field.zvezd%" title="%field.hint%"><b></b></label>
     	%element%
     	<label class="chb_lab" for="%field.sname%">%field.name%</label>
    </div>

END;

// Большой текст
$TEMPLATE['field_55'] = <<<END


<div class="fieldBox" style="width: 950px;">
	<label for="%field.sname%" class="%field.dotted% %field.zvezd%" title="%field.hint%"><b></b>%field.name%</label>
    <div class="redactor" >
    	<textarea name="%field.sname%" id="%field.sname%" style="height: %field.max_size%px; width: 100%;">%field.value%</textarea>
    </div>
</div>

END;

// HTML – текст
$TEMPLATE['field_60'] = <<<END
<div class="fieldBox" style="width: 950px;">
	<label for="%field.sname%" class="%field.dotted% %field.zvezd%" title="%field.hint%"><b></b>%field.name%</label>
    <div class="redactor" >
    	<textarea name="%field.sname%"  id="%field.sname%" style="height: %field.max_size%px; width: 100%;">%field.value%</textarea>
    </div>
</div>

<script type="text/javascript">
    CKEDITOR.replace('%field.sname%', {
        filebrowserBrowseUrl : '/css_mpanel/elfinder/index.html',
        filebrowserWindowHeight : '480',
        height: '%field.max_size%'
    });
</script>


END;

// Файл   Список файлов   Изображение    Видео    Флеш-ролик
$TEMPLATE['field_70'] = $TEMPLATE['field_75'] = $TEMPLATE['field_80'] = $TEMPLATE['field_85'] = <<<END

	<div class="fieldBox">
    	<label for="%field.sname%" class="%field.dotted% %field.zvezd%" title="%field.hint%"><b></b>%field.name%</label>
        %element%
 	</div>
END;

// Список файлов
$TEMPLATE['field_73'] = <<<END

    <div class="fieldBox" style="width:100%;">
    	<label for="%field.sname%" class="%field.dotted% %field.zvezd%" title="%field.hint%"><b></b>%field.name%</label>
        %element%
 	</div>
END;



// Выпадающий список
$TEMPLATE['field_90'] = <<<END

    <div class="fieldBox">
    	<label for="%field.sname%" class="%field.dotted% %field.zvezd%" title="%field.hint%"><b></b>%field.name%</label>
        <div class="clear"></div>
        %element%

        %plus%
 	</div>

END;

$TEMPLATE['field_90_plus'] = <<<END

<input type="text" class="addinput" id="%field.sname%_new_val" name="%field.sname%_new_val" style="display:none;width:380px;">

<span class="ok_value" onClick="return doAddNewHandbook('%field.sname%');" style="display:none;"></span>
<span class="add_value" id="add_new_%field.sname%" onClick="return AddNewHandbook(this, '%field.sname%');"></span>

END;


// Список со множественным выбором
$TEMPLATE['field_95'] = <<<END

    <div class="fieldBox">
    	<label for="%field.sname%" class="%field.dotted% %field.zvezd%" title="%field.hint%"><b></b>%field.name%</label>
        <div class="clear"></div>
        %element%
        %plus%
 	</div>

END;

$TEMPLATE['field_95_plus'] = <<<END


<textarea id="%field.sname%_new_val" name="%field.sname%_new_val" class="minitext" style="display:none;" wrap="on"></textarea>

<span class="ok_value" onClick="return doAddNewHandbook('%field.sname%');" style="display:none;"></span>
<span class="add_value" id="add_new_%field.sname%" onClick="return AddNewHandbook(this, '%field.sname%');"></span>

END;


// Подчиненный справочник
$TEMPLATE['field_97'] = <<<END

	<div class="clear"></div>
    <span class="leftmargin">
        <label for="%field.sname%" class="%field.dotted% %field.zvezd%" title="%field.hint%"><b></b>%field.name%</label>
	    %element%
	</span>
	<div class="clear"></div>
END;

// Выбор страницы
$TEMPLATE['field_100'] = <<<END

    <div class="fieldBox">
        <label for="%field.sname%" class="%field.dotted% %field.zvezd%" title="%field.hint%"><b></b>%field.name%</label>
        %element%
    </div>

END;

// Теги
$TEMPLATE['field_105'] = <<<END

	<div class="fieldBox"%sh_page%>
        <label for="%field.sname%" class="%field.dotted% %field.zvezd%" title="%field.hint%"><b></b>%field.name%</label>
    	<input class="input" type="text" name="%field.sname%" id="%field.sname%" value="%field.value%">
    </div>

END;

?>