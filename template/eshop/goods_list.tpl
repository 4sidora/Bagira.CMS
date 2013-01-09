<?php

$TEMPLATE['frame'] = <<<END

<ul class="shoplist">
    <li class="header">
        <div class="number">&nbsp;</div>
        <div class="image">&nbsp;</div>
        <div class="name"><small>Товар</small></div>
        <div class="quantity"><small>Количество</small></div>

        <div class="summ"><small>Сумма</small></div>
        <div class="clear"></div>
    </li>

    %list%

</ul>
<div class="clear"></div>
<div class="totalsumma">
    <small>Сумма заказа&nbsp;&nbsp;&nbsp;</small><span>%cost%</span><b style="font-size:17px;">&nbsp;Р</b>
    <span class="rubl" style="color:#231f20;">−</span><br/>
</div>
<div class="clear"></div>

END;

$TEMPLATE['list'] = <<<END

    <li>
        <div class="number">%obj.num%.</div>
        <div class="image">
             %structure.getProperty(image, %obj.id%, basket_img)%
        </div>
        <div class="name">
            <small>%structure.getProperty(name, %obj.parent_id%)%</small><br/>
            <a href="%obj.url%" title="">%obj.name%</a>
        </div>
        <div class="quantity">%obj.count%</div>
        <div class="summ">
            <span>%obj.cost_all%</span><b style="font-size:19px;">&nbsp;Р</b>
            <span class="rubl" style="color:#231f20;">−</span>
        </div>
        <div class="clear"></div>
    </li>

END;

$TEMPLATE['empty'] = <<<END

    У данного заказа отсутствуют товары!!!
END;


?>