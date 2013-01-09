<?php

$TEMPLATE['frame'] = <<<END
<div class="registration">
%error%
   <form id="changeForm" name="changeForm" action="/users/add_proc_stat" method="post"   onsubmit="return RegForm();">
        <label for="cartemail">Ваша электронная почта</label><input id="reg_login" name="login" type="text"/><br/>
        <label for="cartpassword">Пароль</label><input type="password" id="reg_password" name="password" /><br/>
        <div class="small">
             <h4>Личные данные</h4>
            <label for="cartname">Имя</label><input type="text" id="reg_name" name="name"  /><br/>
            <label for="cartsurename">Фамилия</label><input type="text" id="reg_surname" name="surname" /><br/>
            <div class="choosesex">
                <label>Пол</label>
                <input type="radio" id="male" value="30" checked="checked" name="sex"/><label class="radio"  for="male">Мужской</label>
                <input type="radio" id="female" value="31"  name="sex"/><label class="radio" for="female">Женский</label>
                <div class="clear"></div> 
            </div>    
            <label for="bday">Дата рождения</label>
                %birthday_day%
                %birthday_mounth%
                %birthday_year%
            <div class="clear"></div>
         	    %captcha%
             
         </div>	
         <button>Готово</button>
    <input name="parram" id="parramForm" type="hidden" value="" />
 	<input name="obj_id" type="hidden" value="29" />
 	<input name="class_id" type="hidden" value="0" />
    <input name="back_url" type="hidden" value="%current_url%" />
    </form>
</div>  
END;


$TEMPLATE['captcha'] = <<<END
<br />
<label for="code">Введите код</label>
    <img src="/core/random_image" alt="Странные цифры"/><br/>
    <input id="code" name="random_image"  type="text"/>
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
<div class="form_update">
	%fields%
	<div class="clear"></div>
</div>
END;


$TEMPLATE['group'] = <<<END
<li><a href="#tabs-%group.sname%">%group.name%</a><ins></ins></li>
END;

$TEMPLATE['fields_frame'] = <<<END
<div id="tabs-%group.sname%">

      %fields%

<div class="clear"></div>
</div>
END;

// Подсказка
$TEMPLATE['acronym'] = <<<END
	<acronym title="%field.hint%" style="cursor:help;">%field.name% </acronym>
END;

$TEMPLATE['showhide'] = <<<END
<div id="linkDiv">
  <a href="#" id="showLink" %sh1%>%sh_text1% <b style="text-decoration:none !important">&darr;</b></a>
  <a href="#" id="hideLink" %sh2%>%sh_text1% <b style="text-decoration:none !important">&uarr;</b></a>

	%class_list%

</div>
END;

$TEMPLATE['class_list'] = <<<END
  <a href="#" id="showClassList">%class_name%<b style="text-decoration:none !important">&darr;</b></a>
  <div style="position:relative;float:right;">
		<div id="page_class_list">%class_list%</div>
  </div>
END;


// Разделитель
$TEMPLATE['field_0'] = <<<END
	<div class="otstup"></div>
END;

// Стандартный
$TEMPLATE['field_standart'] = <<<END
	<div class="fieldBox">
 		<div class="fieldTitle">%field.name% %field.zvezd%</div>
   		<div class="fieldPole">%content%</div>
 	</div>
END;

// Строка	E-mail    URL
$TEMPLATE['field_10'] = $TEMPLATE['field_15'] = $TEMPLATE['field_20'] = <<<END
	<div class="fieldBox"%sh_page%>
 		<div class="fieldTitle">%field.name% %field.zvezd%</div>
   		<div class="fieldPole">
   			<input class="inputfield" type="text" name="%field.sname%" id="%field.sname%" value="%field.value%">
   		</div>
 	</div>
END;

// Дата
$TEMPLATE['field_25'] = <<<END
<script language="javascript" type="text/javascript">
jQuery(document).ready(function() {
       $("#%field.sname%").datepicker({ dateFormat: 'dd.mm.yy' });
       $("#%field.sname%").datepicker($.datepicker.regional["ru"]);
});
</script>

	<div class="fieldBox">
 		<div class="fieldTitle">%field.name% %field.zvezd%</div>
   		<div class="fieldPole">
   			<input class="inputfield" type="text" name="%field.sname%" id="%field.sname%" value="%field.date%" style="width:160px;">
   		</div>
 	</div>
END;

// Время
$TEMPLATE['field_30'] = <<<END
	<div class="fieldBox">
 		<div class="fieldTitle">%field.name% %field.zvezd%</div>
   		<div class="fieldPole">
   			<input class="inputfield" type="text" name="%field.sname%" id="%field.sname%" value="%field.time%" style="width:160px;">
   		</div>
 	</div>
END;

// Дата   и  Время
$TEMPLATE['field_32'] = <<<END
<script language="javascript" type="text/javascript">
jQuery(document).ready(function() {
       $("#%field.sname%_date").datepicker({ dateFormat: 'dd.mm.yy' });
});
</script>
	<div class="fieldBox">
 		<div class="fieldTitle">%field.name% %field.zvezd%</div>
   		<div class="fieldPole">
   			<input class="inputfield" type="text" name="%field.sname%_date" id="%field.sname%_date" value="%field.date%" style="width:100px;">
   			<input class="inputfield" type="text" name="%field.sname%_time" id="%field.sname%_time" value="%field.time%" style="width:50px;">
   		</div>
 	</div>
END;

// Пароль
$TEMPLATE['field_35'] = <<<END
	<div class="fieldBox">
 		<div class="fieldTitle">%field.name% %field.zvezd%</div>
   		<div class="fieldPole">
   			<input class="inputfield" type="password" name="%field.sname%" id="%field.sname%" value="">
   		</div>
 	</div>
END;

// Число        Число с точкой
$TEMPLATE['field_40'] = $TEMPLATE['field_45'] = <<<END
	<div class="fieldBox">
 		<div class="fieldTitle">%field.name% %field.zvezd%</div>
   		<div class="fieldPole">
   			<input class="inputfield" type="text" name="%field.sname%" id="%field.sname%" value="%field.value%">
   		</div>
 	</div>
END;

// Цена
$TEMPLATE['field_47'] =  <<<END
	<div class="fieldBox">
 		<div class="fieldTitle">%field.name% %field.zvezd%</div>
   		<div class="fieldPole">
   			<input class="inputfield" type="text" name="%field.sname%" id="%field.sname%" value="%field.value%">
   		</div>
 	</div>
END;

// Флажок
$TEMPLATE['field_50'] = <<<END
	<div class="fieldBox">
 		<div class="fieldTitle">&nbsp;</div>
 		<div class="fieldPole" style="height:25px;">
 			%element%<label for="%field.sname%">%field.name%</label>
 		</div>
 	</div>
END;

// Большой текст
$TEMPLATE['field_55'] = <<<END
	<div class="clear"></div>

    <div class="fieldTitle">%field.name% %field.zvezd%</div>
	<textarea name="%field.sname%" id="%field.sname%" style="width:95%;height:150px;">%field.value%</textarea>

	<div class="clear"></div>
END;

// HTML – текст
$TEMPLATE['field_60'] = <<<END
<script type="text/javascript" src="/css_mpanel/redactor/redactor.js"></script>

<script type="text/javascript">
	$(document).ready(function(){
		$('#%field.sname%').redactor({ focus: false });
	});
</script>

	<div class="clear"></div>
    <div class="fieldTitle">%field.name% %field.zvezd%</div>
	<textarea name="%field.sname%" id="%field.sname%" style="width:95%;height:200px;">%field.value%</textarea>
	<div class="clear"></div>
END;

// Файл   Список файлов   Изображение    Видео    Флеш-ролик
$TEMPLATE['field_70'] = $TEMPLATE['field_75'] = $TEMPLATE['field_80'] = $TEMPLATE['field_85'] = <<<END
	<div class="fieldBox" style="*height:80px;">
 		<div class="fieldTitle">%field.name% %field.zvezd%</div>
   		<div class="fieldPole"> %element%</div>
 	</div>
END;

$TEMPLATE['upload_file_field'] = <<<END
<input id="file_%field.sname%" name="file_%field.sname%" type="file" value="">
END;

// Список файлов
$TEMPLATE['field_73'] = <<<END
	<div class="fieldBox" style="width:100%;">
 		<div class="fieldTitle">%field.name% %field.zvezd%</div>
   		<div class="fieldPole"> %element%</div>
 	</div>
END;



// Выпадающий список
$TEMPLATE['field_90'] = <<<END
	<div class="fieldBox">
 		<div class="fieldTitle">%field.name% %field.zvezd%</div>
   		<div class="fieldPole">%element%</div>
 	</div>
END;

// Выпадающий список
$TEMPLATE['field_95'] = <<<END
	<div class="fieldBox">
 		<div class="fieldTitle">%field.name% %field.zvezd%</div>
   		<div class="fieldPole">%element%</div>
 	</div>
END;


 /*

$LANG['CONSTR_TYPE_LIST'][65] = 'Позиция в списке';

$LANG['CONSTR_TYPE_LIST'][90] = '';
$LANG['CONSTR_TYPE_LIST'][95] = 'Список c множественным выбором';
$LANG['CONSTR_TYPE_LIST'][100] = 'Ссылка на дерево';
$LANG['CONSTR_TYPE_LIST'][105] = 'Теги';
  */

?>