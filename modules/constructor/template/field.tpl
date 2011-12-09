<?php

$TEMPLATE['frame'] = <<<END
<form id="ajaxFieldForm" class="popupForm">


	<div class="field_box">
	    <label>%text.1% </label>
	    <input class="input" type="text" name="fname" id="fname" value="%obj.fname%" />
	</div>

	<div class="field_box">
	%text.2%
	<input class="input" type="text" name="fsname" id="fsname" value="%obj.fsname%" />
	</div>

	<div class="field_box">
	%text.3%
	<input class="input" type="text" name="hint" id="hint" value="%obj.hint%" />
	</div>

	<div class="field_box">
	<label>%text.4% </label>
	%type%
	</div>



	<div class="field_box" id="divRelType" %sh3%>
	<label>%text.15%</label>
	%reltype%
	</div>

	<div class="field_box" id="divList" %sh%>
	<label> %text.5% </label>
	%list_id%
	</div>

    <div class="field_box" id="divSizeSlide"  %sh4%>
        <label> %text.19% – <span id="max_size_span"><span></label>
        <div class="otstup"></div>
        <div id="slider-range"></div>
	</div>


	<div class="field_box" id="divSize" %sh2%>
	<label> %text.12% </label>
	<input class="input" type="text" name="max_size" id="max_size" value="%obj.max_size%">
	</div>

      <div style="float:left;display:block;width:150px;">
         %view%     <br />
         %inherit%  <br />
         %system%   <br />
       </div>

       <div style="float:left;display:block;width:150px;">
          %required% <br />
          %uniqum% <br />
          %search%   <br />
          %filter%  <br />
           %spec%  <br />
          <div id="quick_add2" %sh%>%quick_add%</div>
       </div>


 		<input name="right" type="hidden" value="%right%">
 		<input name="obj_id" type="hidden" value="%obj.id%">

</form>
END;


/*
  <div class="leftcolum">  </div>
<div class="rightcolum">
<h5>Тип поля</h5>

<ul class="typefields">
    <li style="background-position:8px -727px;" id="1">Строка - Текстовое поле</li>
    <li style="background-position:8px -752px;" id="2">E-mail - Текстовое поле</li>
    <li style="background-position:8px -781px;" id="3">URL - Текстовое поле</li>
    <li style="background-position:8px -813px;" id="4">Дата - Календарь</li>
    <li style="background-position:8px -842px;" id="5">Время - Текстовое поле</li>
    <li style="background-position:8px -873px;" id="5">Дата и время - Календарь</li>
    <li style="background-position:8px -900px;" id="6">Пароль - Поле с паролем</li>
    <li style="background-position:8px -927px;" id="6">Число - Числовое поле</li>
    <li style="background-position:8px -952px;" id="1">Число с точкой - Числовое поле</li>
    <li style="background-position:8px -977px;" id="1">Цена - Числовое поле</li>
    <li style="background-position:8px -1001px;" id="1">Логический - Галочка</li>
    <li style="background-position:8px -1033px;" id="1">Большой текст - Большое текстовое поле</li>
    <li style="background-position:8px -8000px;" id="1">Файл - Выбор файла</li>
    <li style="background-position:8px -8000px;" id="1">Список файлов</li>
    <li style="background-position:8px -8000px;" id="1">Изображение - Выбор файла</li>
    <li style="background-position:8px -8000px;" id="1">Видео - Выбор файла</li>
    <li style="background-position:8px -8000px;" id="1">Флеш-ролик - Выбор файла</li>
    <li style="background-position:8px -1064px;" id="1">Справочник - Выпадающий список</li>
    <li style="background-position:8px -1093px;" id="1">Справочник - Множественный выбор</li>
    <li style="background-position:8px -8000px;" id="1">Подчиненый справочник</li>
    <li style="background-position:8px -8000px;" id="1">Ссылка на дерево</li>
    <li style="background-position:8px -8000px;" id="1">Теги</li>
</ul>
<input type="hidden" name="" id="typefield" />
<script>
var moresearch = 0;
$('.typefields li').click(function(){
	$('.typefields li').each(function () { $(this).attr('class', '');  });
	$("#typefield").val( $(this).attr('id') ) ;
	$(this).attr('class', 'active');

})
</script>

</div>

*/

$TEMPLATE['frame_name'] = <<<END
<form id="ajaxFieldForm" class="popupForm">

    <div class="field_box">
	    <label>%text.1% </label>
	    <input class="input" type="text" name="fname" id="fname" value="%obj.fname%">
	</div>

    <div class="field_box">
	    <label>%text.2% </label>
	    <input class="input" type="text" name="fsname" id="fsname" value="%obj.fsname%" disabled>
	</div>

    <div class="field_box">
	    <label>%text.3% </label>
	    <input class="input" type="text" name="hint" id="hint" value="%obj.hint%">
	</div>		

	<div class="field_box">
	    <label>%text.4% </label>
	    %type%
	</div>


    <div style="float:left;display:block;width:150px;">
         %view%     <br />
         %system%   <br />
    </div>

    <div style="float:left;display:block;width:150px;">
          %required% <br />
          %uniqum% <br />
          %search%   <br />
          %filter%  <br />
    </div>

 	<input name="right" type="hidden" value="%right%">
    <input name="obj_id" type="hidden" value="%obj.id%">

</form>
END;

?>