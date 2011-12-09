<?php

$MODNAME['eshop'] = 'Интернет-магазин';


$RIGHT['eshop']['list'] = 'Список заказов';
$RIGHT['eshop']['order_view'] = 'Просмотр заказа';
$RIGHT['eshop']['order_del'] = 'Удаление заказа';

$RIGHT['eshop']['state'] = 'Статусы заказов';
$RIGHT['eshop']['state_add'] = 'Добавление статуса заказа';
$RIGHT['eshop']['state_upd'] = 'Изменение статуса заказа';
$RIGHT['eshop']['state_del'] = 'Удаление статуса заказа';

$RIGHT['eshop']['delivery'] = 'Способы доставки';
$RIGHT['eshop']['delivery_add'] = 'Добавление способа доставки';
$RIGHT['eshop']['delivery_upd'] = 'Изменение способа доставки';
$RIGHT['eshop']['delivery_del'] = 'Удаление способа доставки';

$RIGHT['eshop']['payment'] = 'Способы оплаты';
$RIGHT['eshop']['payment_add'] = 'Добавление способа оплаты';
$RIGHT['eshop']['payment_upd'] = 'Изменение способа оплаты';
$RIGHT['eshop']['payment_del'] = 'Удаление способа оплаты';

$RIGHT['eshop']['settings'] = 'Настройки модуля';





$LANG['ESHOP_ADD_STATE'] = 'Добавить статус';
$LANG['ESHOP_ADD_DELIVERY'] = 'Добавить способ доставки';
$LANG['ESHOP_ADD_PAYMENT'] = 'Добавить способ оплаты';


$LANG['ESHOP_TEXT_SETTINGS'][1] = 'Покупают только зарегистрированные пользователи';
$LANG['ESHOP_TEXT_SETTINGS'][2] = 'Запретить заказ товаров с количеством меньше чем';
$LANG['ESHOP_TEXT_SETTINGS'][3] = 'Дублировать информацию о заказах на системный эл.ящик';
$LANG['ESHOP_TEXT_SETTINGS'][4] = 'Первоначальный статус заказа:';
$LANG['ESHOP_TEXT_SETTINGS'][5] = 'Установить НДС';



$LANG['ESHOP_MACROS_HINT'] = '
<div class="fieldBox">
В тексте письма вы можете использовать следующие макросы:<br/>
{username} - Имя пользователя<br/>
{goods_list} - Список заказанных товаров<br/>
{order.id} - Внутренний номер заказа (используется для ссылок)<br/>
{order.number} - Номер заказа в интернет-магазине<br/>
{order.cost} - Общая стоимость заказа<br/>
{order.delivery} - Способ доставки<br/>
{order.delivery_price} - Стоимость доставки<br/>
{order.name} - Имя получатель заказа<br/>
{order.surname} - Фамилия получатель заказа<br/>
{order.phone} - Телефон получателя<br/>
{order.address} - Адрес получателя<br/>
</div>
';



?>