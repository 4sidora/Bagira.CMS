<?php

$TEMPLATE['frame'] = <<<END

    	<ul class="cart">
        	<li class="selected"><span>Покупки</span></li>
            <li>&mdash;&nbsp;<span>Авторизация</span></li>
            <li>&mdash;&nbsp;<span>Доставка</span></li>
            <li>&mdash;&nbsp;<span>Оплата</span></li>
            <li>&mdash;&nbsp;<span>Подтверждение</span></li>
        </ul>
        <div class="clear"></div>


%eshop.miniBasket()%

<form action="/eshop/change_basket" method="POST">
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
            
        	<small>Сумма к оплате&nbsp;&nbsp;&nbsp;</small><span>%cost%</span>
            <b style="font-size:17px;">&nbsp;Р</b>
            <b class="rubl" style="color:#231f20;">−</b><br/>

            <input type="hidden" name="is_order" value="1">

        	<button class="cartbutton">Оформить заказ</button>
        </div>
</form>

END;

$TEMPLATE['list'] = <<<END

            <li>
            	<div class="number">
                    <input type="checkbox" name="basket_check[%obj.id%]" value="1" checked="checked" />
                    %obj.num%.
                </div>

            	<div class="image">
                    %structure.getProperty(image, %obj.id%, basket_img)%
            	</div>

                <div class="name">
                	<small>%structure.getProperty(name, %obj.parent_id%)%</small><br/>
                	<a href="%obj.url%" title="">%obj.name%</a>
                </div>

            	<div class="quantity">
                    <input name="basket_count[%obj.id%]" type="text" value="%obj.count%"/>
                </div>

                <input class="price" type="hidden" value="%obj.cost%"/>

                <div class="summ">
                    <span>%obj.cost_all%</span><b style="font-size:19px;">&nbsp;Р</b>
                    <b class="rubl" style="color:#231f20;">−</b>
                </div>
                
                <div class="clear"></div>
            </li>

END;

$TEMPLATE['empty'] = <<<END

    Корзина пустая, вам нужно что-нибудь заказать.

END;

?>