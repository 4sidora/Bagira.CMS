<?php

$TEMPLATE['frame'] = <<<END
<table width="500" border=1>

    <tr>
        <td>№</td>
        <td>Товар</td>
        <td>Количество</td>
        <td>Сумма</td>

    </tr>

    %list%

</table>
END;

$TEMPLATE['list'] = <<<END

    <tr>
        <td>%obj.num%.</td>
        <td><a href="%obj.url%">%obj.name%</a></td>
        <td>%obj.count%</td>
        <td>%obj.cost_all%</td>
    </tr>

END;

$TEMPLATE['empty'] = <<<END

    У данного заказа отсутствуют товары!!!
END;


?>