<?php


$TEMPLATE['frame'] = <<<END

<table width="600" border=1>

    <tr>
        <td>№ заказа</td>
        <td>Дата</td>
        <td>Сумма заказа</td>
        <td>Текущий статус</td>
        <td></td>

    </tr>

    %list%


</table>
END;


$TEMPLATE['list'] = <<<END

    <tr height="40">
        <td><a href="/eshop/order-view/%order.id%">%order.number%</a></td>
        <td>%core.fdate(d.m.Y, %order.date%)%</td>
        <td>%order.cost%</td>
        <td>%order.state%</td>
        <td><a href="/eshop/order-del/%order.id%">удалить</a></td>
    </tr>

END;


$TEMPLATE['empty'] = <<<END

Вы еще не сделали ни одного заказа.
END;





// Просмотр информации о заказе
$TEMPLATE['frame_view'] = <<<END


%eshop.goodsList(%order.id%)%

<div class="info">

    <div class="name"><h4>Получатель</h4></div>
    <div class="value">%order.surname% %order.name%</div>
    <div class="clear"></div>

    <div class="name">Мобильный телефон</div>
    <div class="value">%order.phone%</div>
    <div class="clear"></div>
</div>


<div class="info">
    <div class="name"><h4>Доставка</h4></div>
    <div class="value">&nbsp;</div>
    <div class="clear"></div>

    <div class="name">Зона доставки</div>
    <div class="value">%order.delivery%</div>
    <div class="clear"></div>

    <div class="name">Адрес</div>
    <div class="value">%order.address%</div>
    <div class="clear"></div>
    
    <div class="name">Стоимость доставки</div>
    <div class="value">
        %order.delivery_price%<b style="font-size:11px; font-weight:normal">&nbsp;&nbsp;Р</b>
        <span class="rubl" style="color:#424242;">−</span>
    </div>

    <div class="clear"></div>
</div>

<div class="totalsumma">
    <small>Сумма к оплате&nbsp;&nbsp;&nbsp;</small><span>%order.cost%</span><b style="font-size:17px;">&nbsp;Р</b>
    <span class="rubl" style="color:#231f20;">−</span><br/>

</div>


<div class="info infolast">
    <div class="name"><h4>Статус заказа</h4></div>
    <div class="value"><b>%order.state%</b></div>
    <div class="clear"></div>
</div>

<div class="clear"></div>

<a href="/eshop/order-list" title="" class="back">Назад</a>

END;


?>