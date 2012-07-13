<?php

$TEMPLATE['main'] = <<<END

<div class="otstup"></div>

<script type="text/javascript" src="/css_mpanel/table.js"></script>

<input id="url_parram" type="hidden" value="%parram%">

<input id="table_parent_id" type="hidden" value="%table_parent_id%">
<input id="del_title" type="hidden" value="%del_title%">
<input id="del_text" type="hidden" value="%del_text%">
<input id="del_title_multi" type="hidden" value="%del_title_multi%">
<input id="del_text_multi" type="hidden" value="%del_text_multi%">
<input id="select_checkbox" type="hidden" value="%select_checkbox%">
<input id="moresearch" type="hidden" value="%moresearch%">

%up_line%


<div id="table" class="basictable">

	%content%

</div>

<div class="otstup"></div>
END;

$TEMPLATE['search'] = <<<END
<div class="moresearch %mores%" >
	 <span class="ms1"></span>
	 <span class="ms2"></span>
	 <span class="ms3"></span>
	 <span class="ms4"></span>

	 <div class="search">
		<button>&nbsp;</button>
		<input type="text" id="table_search" value="%table_search%"/>
	    <a href="#" onClick="stopSearch(); return false;"></a>
	 </div>

      %filters%
</div>

<div class="otstup"></div>
END;

$TEMPLATE['without_search'] = <<<END
<div class="moresearch %mores%" >
	 <span class="ms1"></span>
	 <span class="ms2"></span>
	 <span class="ms3"></span>
	 <span class="ms4"></span>

      %filters%
</div>

<div class="otstup"></div>
END;

$TEMPLATE['filters'] = <<<END

    %filters_link%

	%filters%
END;

$TEMPLATE['filters_link'] = <<<END
	<span class="expsearch">%sh_text%</span>
	<div class="clear"></div>
END;

$TEMPLATE['print_link'] = <<<END

        <td width="100">
			<a href="%current_url%=print" target="_blank" style="float:right;padding-top:10px;">Печать</a>
			<img src="/css_mpanel/images/printv.jpg" width="30" height="30" border=0 style="float:right;">
		</td>
END;

$TEMPLATE['frame'] = <<<END

%rights%

<form name="checked_form" id="checked_form" method="post">
	<table border="0" cellspacing="0" cellpadding="0" width="100%">

		%shapka%

		%items%

	</table>
</form>

<div class="foter_table">
	%checkbox_multi%
       %rights_multi%
</div>
  <div class="clear"></div>


  %navbar%


END;

$TEMPLATE['navibar'] = <<<END
  %navbar%

<div class="navigate2">
	&nbsp;&nbsp;&nbsp; Выводить по: &nbsp;
	%max_count%
</div>
END;

//		-		-		-	 Оформление шапки		-		-		-		-		-		-

// Фрейм шапки
$TEMPLATE['shapka'] = <<<END
<tr>
    %first_column%
 	%columns%
</tr>
END;

// Первый столбец шапки (заглушка, если есть столбец с активностью)
$TEMPLATE['first_column'] = <<<END
<td class="td_border_lines" width="%width%">&nbsp;</td>
END;

// Обычный столбец
$TEMPLATE['column'] = <<<END
<td class="td_border_lines" %width%>%title% %column_order%</td>
END;

// Пиктограммка сортировки столбца
$TEMPLATE['column_order'] = <<<END
&nbsp;&nbsp;
<img onclick="orderBy(this, '%field%', '%sort2%')"
	src="/css_mpanel/images/sort_%sort%.gif" class="cursor" width="12" height="12" border="0">
END;


//		-		-		-	 Оформление элементов списка		-		-		-		-		-		-

// Оформление объекта
$TEMPLATE['items'] = <<<END
<tr class="table_swich" id="table_swich_%id%" name="%id%">
 	%item_check%

 	%item_vals%
</tr>
END;

// Первый столбец с галочкой и активностью
$TEMPLATE['item_check'] = <<<END
<td class="cell_gamename">

	<table cellpadding="0" border="0"><tbody><tr>

		%checkbox%

		%active%

	</tr></tbody></table>

</td>
END;

// Ячейка со значением объекта
$TEMPLATE['item_val'] = <<<END
<td class="cell_gamename%first%" style="cursor: pointer;"
	onClick="document.location.href = '%url%%parram%';">
	 %value%
</td>
END;

// Не кликабельная ячейка
$TEMPLATE['item_val_no_click'] = <<<END
<td class="cell_gamename%first%">%value%</td>
END;


//		-		-		-	 Оформление прав (кнопочек)		-		-		-		-		-		-

// Фрейм для списка одиночных прав
$TEMPLATE['rights'] = <<<END
<div id="table_edits" style="display:none;">

	<table cellspacing="0" cellpadding="0" border="0" align="right"><tbody><tr>

		%rights%

	</tr></tbody></table>

</div>
END;

// Одиночное право
$TEMPLATE['right'] = <<<END
<td width="40">
	<a %del_button% class="icon_edit" name="%url%" href=""%java%>
		<div class="table_header"><font class="right_%class%" title="%hint%"></font></div>
	</a>
</td>
END;

// Множественное право
$TEMPLATE['right_multi'] = <<<END
<i class="pointer">
	<a title="%hint%" alt="%hint%" name="%url%parram%parram%" class="right_%class%_multi"%java%>%hint%</a>
</i>
END;

// Пиктограмка активности
$TEMPLATE['right_active'] = <<<END
<td width="25">
	<i class="pointer">
		<a title="%hint%" name="%url%%id%%parram%" id="pimpochka_%id%" class="ks_act activ_elem_%act%"></a>
	</i>
</td>
END;

// Пиктограмка активности - не кликабельная
$TEMPLATE['right_active_noclick'] = <<<END
<td width="25">
	<i class="pointer">
		<a title="%hint%" class="activ_elem_%act%"></a>
	</i>
</td>
END;


//		-		-		-	 Галочки		-		-		-		-		-		-

// Выделить объект
$TEMPLATE['checkbox'] = <<<END
<td width="25">
	<i class="pointer2"><input type="checkbox" value="%id%" name="objects[%id%]"></i>
</td>
END;

// Выделить все объекты
$TEMPLATE['checkbox_multi'] = <<<END
<i><input id="bigcheck" type="checkbox" name="toggle"></i>
END;




// Сообщение "список пуст"
$TEMPLATE['empty_frame'] = <<<END
<div class="emptytable"><div class="emptyi">
	%message%
</div></div>
END;


// *		*		*		*	NAVBAR	*		*		*		*		*

$TEMPLATE['navigation'] = <<<END
<div class="navigate">
    %left_block%

	%pages%

	%right_block%

	<input id="count_page" type="hidden" value="%count_page%">
</div>
END;

$TEMPLATE['left_block'] = <<<END
&larr; Ctrl&nbsp;&nbsp;<a class="cursor table_page_navig" name="%num%">Предыдущая</a>    &nbsp;&nbsp;&nbsp;&nbsp;
END;

$TEMPLATE['right_block'] = <<<END
<a class="cursor table_page_navig" name="%num%">Следующая</a>&nbsp;&nbsp;Ctrl &rarr;
END;

$TEMPLATE['pages_a'] = <<<END
<strong id="pn_curnum" name="%page_num%">%page_num%</strong>&nbsp;&nbsp;&nbsp;&nbsp;
END;

$TEMPLATE['pages_na'] = <<<END
<a class="cursor table_page_navig" name="%page_num%">%page_num%</a>&nbsp;&nbsp;&nbsp;&nbsp;
END;






?>