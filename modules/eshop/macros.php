<?php

class eshopMacros {

    public function button($goods_id, $templ_name = 'button') {

        $templ_file = '/eshop/'.$templ_name.'.tpl';
	    $TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
		    return page::errorNotFound('eshop.button', $templ_file);

        if ($goods = ormPages::get($goods_id, 'goods')) {

            page::assign('goods_id', $goods->id);
            page::assign('goods_price', $goods->price);

            if (reg::getKey('/eshop/only_reg') && user::isGuest()) {

                // кнопки нет, пользователь не зарегистрирован
                return page::parse($TEMPLATE['empty']);
            } else {

                // кнопка есть
                return page::parse($TEMPLATE['frame']);
            }
        }
    }

    public function miniBasket($templ_name = 'basket_mini') {

        $templ_file = '/eshop/'.$templ_name.'.tpl';
	    $TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
		    return page::errorNotFound('eshop.miniBasket', $templ_file);

        $count = basket::getCount();

        if ($count > 0) {
            page::assign('cost', basket::getTotalCost());
            page::assign('count', $count);
            return page::parse($TEMPLATE['frame']);
        } else
            return page::parse($TEMPLATE['empty']);
    }





    public function basket($templ_name = 'basket') {

        $templ_file = '/eshop/'.$templ_name.'.tpl';
	    $TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
		    return page::errorNotFound('eshop.basket', $templ_file);


        $fields = page::getFields('obj', $TEMPLATE['list']);

        $goods_list = basket::getGoodsData();

        $list = '';
        $num = $cost = 0;
        while(list($id, $goods) = each($goods_list)) {

            if ($obj = ormPages::get($id, 'goods')) {

                $num++;

                if (isset($fields['obj'])) {
                    reset($fields['obj']);
                    while(list($key, $val) = each($fields['obj']))
                        if ($val != 'url' && $val != 'class' && $val != 'num')
                            page::assign('obj.'.$val, $obj->__get($val));
                }

                page::assign('obj.num', $num);
                page::assign('class-first', ($num == 1) ? 'first' : '');
                page::assign('class-last', ($num == basket::getCount()) ? 'last' : '');
                page::assign('class-odd', ($num % 2 == 0) ? 'odd' : '');
                page::assign('class-even', ($num % 2 != 0) ? 'even' : '');
                page::assign('class-third', ($num % 3 == 0) ? 'third' : '');

                page::assign('obj.id', $goods['goods_id']);
                page::assign('obj.cost', $goods['cost']);
                page::assign('obj.count', $goods['count']);
                page::assign('obj.url', $obj->url);

                $cost_all = $goods['cost'] * $goods['count'];
                page::assign('obj.cost_all', $cost_all);

                $cost += $cost_all;

                $list .= page::parse($TEMPLATE['list']);
            }
        }

        if (!empty($list)) {
            page::assign('cost', $cost);
            page::assign('list', $list);
            return page::parse($TEMPLATE['frame']);
        } else
            return page::parse($TEMPLATE['empty']);
    }


    public function order($templ_name = 'order_steps') {

        $templ_file = '/eshop/'.$templ_name.'.tpl';
	    $TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
		    return page::errorNotFound('eshop.basket', $templ_file);

        if (user::isGuest()) {
            page::assign('order.cost', basket::getTotalCost());
            return page::parse($TEMPLATE['no_auth']);
        }

        if (!isset($_SESSION['order_step']))
            $_SESSION['order_step'] = 1;

        if (system::url(2) == 'back')
            $_SESSION['order_step'] --;

        switch ($_SESSION['order_step']) {

            case 1: // Доставка

                    $sel = new ormSelect('eshop_delivery_method');
                    $sel->fields('name, price, notice');

                    $list = '';
                    while ($obj = $sel->getObject()) {
                        page::assign('obj.id', $obj->id);
                        page::assign('obj.name', $obj->name);
                        page::assign('obj.price', $obj->price);
                        page::assign('obj.notice', $obj->notice);

                        $act = (isset($_SESSION['order']['delivery']) && $obj->id == $_SESSION['order']['delivery']) ? '_active' : '';

                        $list .= page::parse($TEMPLATE['delivery_list'.$act]);
                    }

                    page::assign('delivery_list', $list);

                    page::assign('order.name', (isset($_SESSION['order']['name'])) ? $_SESSION['order']['name'] : user::get('name'));
                    page::assign('order.surname', (isset($_SESSION['order']['surname'])) ? $_SESSION['order']['surname'] : user::get('surname'));
                    page::assign('order.phone', (isset($_SESSION['order']['phone'])) ? $_SESSION['order']['phone'] : user::get('phone'));
                    page::assign('order.address', (isset($_SESSION['order']['address'])) ? $_SESSION['order']['address'] : user::get('address'));
                    page::assign('order.notice', (isset($_SESSION['order']['notice'])) ? $_SESSION['order']['notice'] : '');

                    page::assign('order.cost', basket::getTotalCost());


                    return page::parse($TEMPLATE['delivery']);
            break;

            case 2: // Подтверждение

                // Расчитываем общую стоимость с учетом доставки
                if ($delivery = ormObjects::get($_SESSION['order']['delivery'])) {

                    page::assign('order.delivery', $delivery->name);
                    page::assign('order.delivery_price', $delivery->price);

                    $cost = $delivery->price + basket::getTotalCost();
                    page::assign('order.cost', $cost);
                }



                    page::assign('order.name', (isset($_SESSION['order']['name'])) ? $_SESSION['order']['name'] : user::get('name'));
                    page::assign('order.surname', (isset($_SESSION['order']['surname'])) ? $_SESSION['order']['surname'] : user::get('surname'));
                    page::assign('order.phone', (isset($_SESSION['order']['phone'])) ? $_SESSION['order']['phone'] : user::get('phone'));
                    page::assign('order.address', (isset($_SESSION['order']['address'])) ? $_SESSION['order']['address'] : user::get('address'));

                    return page::parse($TEMPLATE['aception']);
            break;

            default:
                unset($_SESSION['order_step']);
                system::redirect('/eshop/basket');
        }

    }

    public function orderOk($order_id, $templ_name = 'order_steps') {

        $templ_file = '/eshop/'.$templ_name.'.tpl';
	    $TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
		    return page::errorNotFound('eshop.orderOk', $templ_file);

        if ($obj = ormObjects::get($order_id, 'eshop_order')){

            $order = new eShopOrder($obj);


            page::assign('order.number', $order->getNumber());

            return page::parse($TEMPLATE['frame_ok']);
        }
    }

    public function orderView($order_id, $templ_name = 'order_list') {

        $templ_file = '/eshop/'.$templ_name.'.tpl';
	    $TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
		    return page::errorNotFound('eshop.orderVeiw', $templ_file);

        if ($obj = ormObjects::get($order_id, 'eshop_order')){

            $order = new eShopOrder($obj);

            page::globalVar('title', lang::get('ESHOP_TITLE', 9).$order->getNumber());
            page::globalVar('h1', lang::get('ESHOP_TITLE', 9).$order->getNumber());


            page::assign('order.id', $order->id);
            page::assign('order.number', $order->getNumber());
            page::assign('order.cost', $order->getTotalCost());
            page::assign('order.state', $order->_state);

            // Информация о доставке
            page::assign('order.delivery', $order->_delivery);
            page::assign('order.delivery_price', $order->getDeliveryPrice());
            page::assign('order.name', $order->delivery_name);
            page::assign('order.surname', $order->delivery_surname);
            page::assign('order.phone', $order->delivery_phone);
            page::assign('order.address', $order->delivery_address);

            return page::parse($TEMPLATE['frame_view']);
        }
    }

    public function orderList($templ_name = 'order_list') {

        $templ_file = '/eshop/'.$templ_name.'.tpl';
	    $TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
		    return page::errorNotFound('eshop.orderList', $templ_file);

        $sel = new ormSelect('eshop_order');
        $sel->where('parents', '=', user::get('id'));
        $sel->orderBy('name', desc);

        $list = '';
        while ($obj = $sel->getObject()) {

            $order = new eShopOrder($obj);

            page::assign('order.id', $order->id);
            page::assign('order.number', $order->getNumber());
            page::assign('order.cost', $order->getTotalCost());
            page::assign('order.state', $order->_state);
            page::assign('order.date', $order->date);

            $list .= page::parse($TEMPLATE['list']);
        }

        if (!empty($list)) {
            page::assign('list', $list);
            return page::parse($TEMPLATE['frame']);
        } else
            return page::parse($TEMPLATE['empty']);
    }

    public function goodsList($order_id, $templ_name = 'goods_list') {

        $templ_file = '/eshop/'.$templ_name.'.tpl';
	    $TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
		    return page::errorNotFound('eshop.goodsList', $templ_file);

        if ($order = ormObjects::get($order_id, 'eshop_order')){

            $fields = page::getFields('obj', $TEMPLATE['list']);

            $sel = new ormSelect('eshop_goods');
            $sel->where('parents', '=', $order_id);

            $list = '';
            $num = $cost = 0;
            while($goods = $sel->getObject())  {

                if ($obj = ormPages::get($goods->goods_id, 'goods')) {

                    $num++;

                    if (isset($fields['obj']))
                        while(list($key, $val) = each($fields['obj']))
                            if ($val != 'url' && $val != 'class' && $val != 'num')
                                page::assign('obj.'.$val, $obj->__get($val));

                    page::assign('obj.num', $num);
                    page::assign('class-first', ($num == 1) ? 'first' : '');
                    page::assign('class-last', ($num == $sel->getObjectCount()) ? 'last' : '');
                    page::assign('class-odd', ($num % 2 == 0) ? 'odd' : '');
                    page::assign('class-even', ($num % 2 != 0) ? 'even' : '');
                    page::assign('class-third', ($num % 3 == 0) ? 'third' : '');

                    page::assign('obj.id', $obj->id);
                    page::assign('obj.name', $obj->name );
                    page::assign('obj.cost', $goods->cost );
                    page::assign('obj.count', $goods->count);
                    page::assign('obj.url', $obj->url);

                    $cost_all = $goods->cost * $goods->count;
                    page::assign('obj.cost_all', $cost_all);

                    $cost += $cost_all;

                    $list .= page::parse($TEMPLATE['list']);
                }
            }

            if (!empty($list)) {
                page::assign('cost', $cost);
                page::assign('list', $list);
                return page::parse($TEMPLATE['frame']);
            } else
                return page::parse($TEMPLATE['empty']);
        }
    }

}

?>