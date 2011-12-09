<?php

class controller {

    public function defAction() {

		return ormPages::get404();

 	}

    public function basketAction() {

        page::globalVar('title', lang::get('ESHOP_TITLE', 1));
        page::globalVar('h1', lang::get('ESHOP_TITLE', 1));

        return page::macros('eshop')->basket();
    }

    public function change_basketAction() {


        $goods_list = basket::getGoodsData();
        $num = $cost = 0;
        while(list($id, $goods) = each($goods_list)) {
            if (isset($_POST['basket_check'][$id]) && isset($_POST['basket_count'][$id])) {

                $count = system::checkVar($_POST['basket_count'][$id], isInt);
                if (empty($count))
                    $count = 1;
           
                basket::changeGoods($id, $count);

            } else {
                basket::delGoods($id);
            }
        }

        if (system::POST('is_order', isBool))
            system::redirect('/eshop/order');
        else
            system::redirect('/eshop/basket');
    }


    // Добавить товар в корзину
    public function to_basketAction() {
        
        if ($goods = ormPages::get(system::url(2), 'goods')) {

            $count = (system::issetUrl(3)) ? system::url(3) : 1;

            basket::addGoods($goods, $count);
        }

        system::redirect('/eshop/basket');
    }

    // Страница оформления заказа
    public function orderAction () {

        page::globalVar('title', lang::get('ESHOP_TITLE', 1));
        page::globalVar('h1', lang::get('ESHOP_TITLE', 1));

        return page::macros('eshop')->order();

    }

    // Обработчик для шагов оформления заказа
    public function order_procAction () {


        if (!user::isGuest() && isset($_SESSION['order_step'])) {

            switch ($_SESSION['order_step']) {

                case 1: // Доставка

                    $_SESSION['order']['name'] = system::POST('delivery_name', isString);
                    $_SESSION['order']['surname'] = system::POST('delivery_surname', isString);
                    $_SESSION['order']['phone'] = system::POST('delivery_phone', isString);
                    $_SESSION['order']['address'] = system::POST('delivery_address', isString);
                    $_SESSION['order']['notice'] = system::POST('delivery_notice', isText);

                    $_SESSION['order']['delivery'] = system::POST('delivery', isInt);

                    if (empty($_SESSION['order']['delivery']) || empty($_SESSION['order']['name']) ||
                        empty($_SESSION['order']['surname']) || empty($_SESSION['order']['phone']) ||
                        empty($_SESSION['order']['address'])) {

                        // Ошибка: не все поля заполнены


                    } else {
                        
                        // Все отлично, переходим к следующему шагу
                        $_SESSION['order_step'] = 2;
                    }

                    break;

                case 2: // Подтвеждение заказа, сохраняем данные в БД

                    $order = new eShopOrder();

                    // Информация о доставке
                    $order->setDelivery($_SESSION['order']['delivery']);
                    $order->delivery_name = $_SESSION['order']['name'];
                    $order->delivery_surname = $_SESSION['order']['surname'];
                    $order->delivery_phone = $_SESSION['order']['phone'];
                    $order->delivery_address = $_SESSION['order']['address'];
                    $order->delivery_notice = $_SESSION['order']['notice'];

                    $order_id = $order->save();

                    if ($order_id) {

                        // Заказ сохранен
                        unset($_SESSION['order_step']);
                        unset($_SESSION['order']);

                        system::redirect('/eshop/ok/' . $order_id);

                    } else {

                        // Произошла ошибка
                        
                    }

                    system::redirect('/eshop/order');

                break;

            }

        }
        system::redirect('/eshop/order');

    }

    public function okAction() {

        page::globalVar('title', lang::get('ESHOP_TITLE', 10));
        page::globalVar('h1', lang::get('ESHOP_TITLE', 10));

        return page::macros('eshop')->orderOk(system::url(2));
    }

    public function order_listAction() {

        page::globalVar('title', lang::get('ESHOP_TITLE', 8));
        page::globalVar('h1', lang::get('ESHOP_TITLE', 8));

        return page::macros('eshop')->orderList();
    }

    public function order_viewAction() {

        return page::macros('eshop')->orderView(system::url(2));
    }

    public function order_delAction() {

        if ($order = eShopOrder::get(system::url(2))) {
            if ($order->getUserId() == user::get('id'))
                $order->delete();
        }

        system::redirect('/eshop/order-list');
    }
}

?>