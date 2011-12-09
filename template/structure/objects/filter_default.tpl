<?php

$TEMPLATE['frame'] = <<<END

<div class="filter">
    <h4>Выбор по параметрам:</h4>


    <form id="filterForm" name="filterForm" action="" method="post">

        %list%

        <input name="target" type="hidden" value="%target%">

        <div class="clear"></div>
        <button>Показать подходящие товары</button>

    </form>
</div>


END;

$TEMPLATE['list'] = <<<END

%filter% 

END;

$TEMPLATE['separator'] = <<<END

END;

$TEMPLATE['empty'] = <<<END
END;

// Обычный фильтр
$TEMPLATE['filter_text'] = <<<END
<div class="column2">
    <h3>%field.name%</h3>
    <input type="text" name="%field.sname%" id="%field.sname%" value="%field.value%">
</div>
END;


// Выбор числового промежутка или стоимости
$TEMPLATE['filter_beetwen_int'] = <<<END

<div class="column2">
    <h3>%field.name%</h3>
    от <input type="text" name="%field.sname%" id="%field.sname%" value="%field.value%" class="price">
    до <input type="text" name="%field.sname%2" id="%field.sname%" value="%field.value2%" class="price">
</div>

END;

// Выбор даты
$TEMPLATE['filter_beetwen_date'] = <<<END

<div class="column2">
    <h3>%field.name%</h3>
    <input type="text" name="%field.sname%" id="%field.sname%" value="%field.value%">
    <input type="text" name="%field.sname%2" id="%field.sname%" value="%field.value2%">
</div>

END;

// Да \ Нет
$TEMPLATE['filter_boolean'] = <<<END

<div class="column2">

    <div class="check">
        <label for="%field.sname%">%field.name%</label>
        <input name="%field.sname%" type="checkbox"  id="%field.sname%" value="1" %field.checked% >
        <div class="clear"></div>
    </div>
           
</div>

END;


// Списки значений
$TEMPLATE['filter_relation'] = <<<END
<div class="column2">
    <h3>%field.name%</h3>
    <select name="%field.sname%">%list%</select>
</div>
END;

$TEMPLATE['filter_relation_list_null'] = <<<END
    <option value="0"%obj.selected%></option>
END;

$TEMPLATE['filter_relation_list'] = <<<END
    <option value="%obj.id%"%obj.selected%>%obj.name%</option>
END;

$TEMPLATE['filter_relation_separator'] = <<<END

END;


?>