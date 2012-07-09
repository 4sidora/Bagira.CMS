<?php

$TEMPLATE['main'] = <<<END
<script type="text/javascript" src="/css_mpanel/table.js"></script>

<input id="url_parram" type="hidden" value="%parram%">

<input id="del_title" type="hidden" value="%del_title%">
<input id="del_text" type="hidden" value="%del_text%">
<input id="del_title_multi" type="hidden" value="%del_title_multi%">
<input id="del_text_multi" type="hidden" value="%del_text_multi%">
<input id="select_checkbox" type="hidden" value="%select_checkbox%">


%up_line%



<div id="table">
	%content%
</div>

END;
                        //width="970"
$TEMPLATE['up_line'] = <<<END

<table border="0" width="95%" style="margin-bottom:10px;margin-left:15px;"><tr>

		%up_line%


</tr></table>


<div class="clear"></div>
END;

$TEMPLATE['search'] = <<<END

		<td>
			<input class="inputfield" type="text" style="width:98%;" id="table_search" value="%table_search%" />
		</td>

		<td width="50">
			<input type="button" name="search" id="goSearch" value="Искать" >
		</td>

        %filters_link%

END;

$TEMPLATE['filters_link'] = <<<END
        <td width="150">
            <form id="addit_search" name="addit_search" action="" method="post">
            	<input id="showfilter" name="showfilter" type="hidden" value="%showfilter%">
            </form>
			<a href="#" onclick="ShowFilters();">%sh_text%</a>
		</td>
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
	<table>

		%shapka%

		%items%

		<tr class="gray">
			<td class="first">%checkbox_multi%</td>
			<td colspan="5" class="second2" >
    			%rights_multi%
			</td>
			<td ></td>
		</tr>

	</table>
</form>

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
<tr class="gray">
    %first_column%
 	%columns%
</tr>
END;

// Первый столбец шапки (заглушка, если есть столбец с активностью) %width%
$TEMPLATE['first_column'] = <<<END
<td class="first"></td>
END;

// Обычный столбец
$TEMPLATE['column'] = <<<END
<td  %width%>%title% %column_order%</td>
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
<tr id="table_swich_%id%" name="%id%">
	%item_check%

    %item_vals%

</tr>
END;

// Первый столбец с галочкой и активностью
$TEMPLATE['item_check'] = <<<END

%checkbox%

%active%

END;

// Ячейка со значением объекта    %first%
$TEMPLATE['item_val'] = <<<END
<td style="cursor: pointer;" onClick="document.location.href = '%url%%parram%';">%value%</td>
END;

// Не кликабельная ячейка
$TEMPLATE['item_val_no_click'] = <<<END
<td>%value%</td>
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
		<div class="header"><font class="right_%class%" title="%hint%"></font></div>
	</a>
</td>
END;

// Множественное право
$TEMPLATE['right_multi'] = <<<END

<span class="iconall %class%_all" title="%hint%" alt="%hint%" name="%url%parram%parram%" %java%>%hint%</span>

END;

// Пиктограмка активности    activ_elem_%act%
$TEMPLATE['right_active'] = <<<END

<td class="second"><span class="act_active" title="%hint%" name="%url%%id%%parram%" id="pimpochka_%id%"></span></td>

END;

// Пиктограмка активности - не кликабельная
$TEMPLATE['right_active_noclick'] = <<<END

<td class="second"><span class="act_active" title="%hint%"></span></td>

END;


//		-		-		-	 Галочки		-		-		-		-		-		-

// Выделить объект
$TEMPLATE['checkbox'] = <<<END

<td class="first"><input type="checkbox" value="%id%" name="objects[%id%]" /></td>

END;

// Выделить все объекты
$TEMPLATE['checkbox_multi'] = <<<END
<input id="bigcheck" type="checkbox" name="toggle">
END;




// Сообщение "список пуст"
$TEMPLATE['empty_frame'] = <<<END
<div class="otstup"></div>
<div class="otstup"></div>

<center>%message%</center>

<div class="otstup"></div>
<div class="otstup"></div>
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