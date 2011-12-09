<?php

// Шаг: Авторизация или регистрация при заказе товара
$TEMPLATE['no_auth'] = <<<END



<ul class="cart">
    <li>Покупки</li>
    <li class="selected">&mdash;&nbsp;<span>Авторизация</span></li>
    <li>&mdash;&nbsp;<span>Доставка</span></li>
    <li>&mdash;&nbsp;<span>Оплата</span></li>
    <li>&mdash;&nbsp;<span>Подтверждение</span></li>

</ul>


<div class="clear"></div>
<div class="cartwrapper">



    <form action="/users/auth" method="post">
       	
            <label for="email">Ваш эл. ящик</label>
            <input type="text" id="email" name="login"/>
            <div class="clear"></div>


            <label for="password">Пароль</label>
            <input type="password" id="password"  name="passw"/>
             <div class="clear"></div>

            <a href="/users/recover" title="">Забыли пароль?</a>
            <input name="back_url" type="hidden" value="%current_url_pn%" />

<div class="clear"></div>


    <br/><br/><br/>
    Впервые на «B Mart»?<br/>Зарегистрируйтесь прямо сейчас
    <a href="/users/addstat" title="" class="button">Регистрация</a>



    <div class="clear"></div>
    <div class="totalsumma">
        <small>Сумма к оплате&nbsp;&nbsp;&nbsp;</small><span>%order.cost%</span>
        <b style="font-size:17px;">&nbsp;Р</b><span class="rubl" style="color:#231f20;">−</span><br/>
    </div>
        
    <div class="clear"></div>
    <a href="/eshop/basket" title="" class="back">Назад</a>
     <button class="ahead">Вперед</button>
    </form>
</div>
<div class="clear"></div>



END;


// Шаг: Выбор параметров доставки
$TEMPLATE['delivery'] = <<<END

<ul class="cart">
    <li>Покупки</li>
    <li>&mdash;&nbsp;<span>Авторизация</span></li>
    <li class="selected">&mdash;&nbsp;<span>Доставка</span></li>
    <li>&mdash;&nbsp;<span>Оплата</span></li>
    <li>&mdash;&nbsp;<span>Подтверждение</span></li>

</ul>
<div class="clear"></div>



<form action="/eshop/order_proc" method="post">

    <div class="cartwrapper">

        <h4>Зона доставки</h4>

         <ul class="zone">
             %delivery_list%
         </ul>

         <div class="clear"></div>
         <h4>Получатель</h4>
         <div class="clear"></div>

          <div class="small">

                <label for="delivery_surname">Фамилия</label>
                <input type="text" id="delivery_surname" name="delivery_surname" value="%order.surname%"/>
                <div class="clear"></div>

                <label for="delivery_name">Имя</label>
                <input type="text" id="delivery_name" name="delivery_name" value="%order.name%"/>
                <div class="clear"></div>

                <label for="delivery_phone">Мобильный телефон</label>
                <input type="text" id="delivery_phone" name="delivery_phone" value="%order.phone%"/>
                <div class="clear"></div>

                <label for="delivery_address">Адрес доставки</label>
                <input type="text" id="delivery_address" name="delivery_address" value="%order.address%"/>
                <div class="clear"></div>
             </div>

             <h4>Адрес доставки</h4>
             <div class="clear"></div>
             Улица, дом, корпус, строение, квартира, офис.
             <textarea name="delivery_notice">%order.notice%</textarea>

    </div>
    
<div class="clear"></div>
<div class="totalsumma">
    <small>Сумма к оплате&nbsp;&nbsp;&nbsp;</small><span>%order.cost%</span>
    <b style="font-size:17px;">&nbsp;Р</b><span class="rubl" style="color:#231f20;">−</span><br/>
</div>
<div class="clear"></div>
<a href="/eshop/basket" title="" class="back">Назад</a>

<button class="ahead">Вперед</button>
</div>
<div class="clear"></div>

   </form>
    

END;

$TEMPLATE['delivery_list'] = <<<END
        <li>
             <input type="radio" name="delivery" id="%obj.id%" value="%obj.id%"/>
             <label for="%obj.id%">%obj.name% (%obj.price%)</label>
        </li>
END;

$TEMPLATE['delivery_list_active'] = <<<END
        <li>
             <input type="radio" name="delivery" id="%obj.id%" value="%obj.id%" checked="checked"/>
             <label for="%obj.id%">%obj.name% (%obj.price%)</label>
        </li>
END;


// Последний шаг: Подтвеждение заказа
$TEMPLATE['aception'] = <<<END

<ul class="cart">
    <li><span>Покупки</span></li>
    <li>&mdash;&nbsp;<span>Авторизация</span></li>
    <li>&mdash;&nbsp;<span>Доставка</span></li>
    <li>&mdash;&nbsp;<span>Оплата</span></li>
    <li class="selected">&mdash;&nbsp;<span>Подтверждение</span></li>

</ul>
<div class="clear"></div>

<form action="/eshop/order_proc" method="post">

%eshop.basket(basket_order)%

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

<div class="info infolast">
    <div class="name"><h4>Способ оплаты</h4></div>
    <div class="value">Наличные</div>
    <div class="clear"></div>
</div>

<div class="totalsumma">
    <small>Сумма к оплате&nbsp;&nbsp;&nbsp;</small><span>%order.cost%</span><b style="font-size:17px;">&nbsp;Р</b>
    <span class="rubl" style="color:#231f20;">−</span><br/>

</div>

<div class="clear"></div>

<a href="/eshop/order/back" title="" class="back">Назад</a>
<button class="cartbutton infobutton">Все правильно</button>

</form>    

END;

// Сообщение заказ оформлен
$TEMPLATE['frame_ok'] = <<<END

    Ваш заказ принят! Спасибо! <br /><br />

Более подробную информацию о вашем заказе №%order.number% вы сможете посмотреть в <a href="/eshop/order-list">личном кабинете</a>.

END;


?>