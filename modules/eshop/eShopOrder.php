<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	
*/

class eShopOrder {

    private $obj;

    private $cost = 0;
    private $total_cost = 0;
    private $delivery_price = 0;
    private $change_state = false;

    private $tabu_list = array('name', 'state', 'date', 'email', 'delivery', 'payment', 'payment_date',
                               'payment_cost', '');
    
    function __construct($order = '') {

        if (($order instanceof ormObject) && $order->isInheritor('eshop_order')) {

            $this->obj = $order;

        } else {

            $this->obj = new ormObject();
            $this->obj->setClass('eshop_order');
            $this->obj->setParent(user::get('id'));

            $this->obj->name = $this->getNewNumber();
            $this->obj->state = reg::getKey('/eshop/fisrt_state');
            $this->obj->date = date('d-m-Y H:i:s');
            $this->obj->email = user::get('email');
        }
    }


    // Вернет экземпляр существующего заказа с указанным ID
    /*  Для конструкций вида:
     *
     *  if ($obj = eShopOrder::get(344)) {
     *      // Что-то делаем...
     *  }
     */
    static function get($order_id) {

        if ($order = ormObjects::get($order_id, 'eshop_order')) {
            $obj = new eShopOrder($order);
            return $obj;       
        }
    }

    // Вернет максимальный не занятый номер заказа
    private function getNewNumber() {

        // Определям номер нового заказа
        $sel = new ormSelect('eshop_order');
        $sel->fields('name');
        $sel->orderBy('name', desc);
        $sel->limit(1);

        if ($last_order = $sel->getObject())
            $number = $last_order->name + 1;
        else
            $number = 1;

        return $number;
    }

    // Вернет номер заказа в красивой форме. Например, 000322.
    public function getNumber() {
        if (!empty($this->obj)) {
            return substr('00000', 0, 5-strlen($this->obj->name)).$this->obj->name;
        }
    }

    // Вернет стоимость товаров
    public function getCost() {
        if (!empty($this->obj)) {

            if (empty($this->cost)) {

                // Считаем стоимость товаров
                $sel = new ormSelect('eshop_goods');
                $sel->fields('cost, count');
                $sel->where('parents', '=', $this->obj->id);
                while($goods = $sel->getObject())
                    $this->cost += $goods->count * $goods->cost;
            }

            return $this->cost;
        }
    }

    // Вернет общую стоимость заказа
    public function getTotalCost() {
        if (!empty($this->obj)) {
            
            if (empty($this->total_cost)) {

                // Считаем стоимость товаров
                $this->total_cost = $this->getCost();

                // Добавляем стоимость доставки
                $this->total_cost += $this->getDeliveryPrice();
            }

            return $this->total_cost;
        }
    }

    // Устанавливает текущий статус заказа
    public function setState($value) {

        if (!empty($this->obj)) {

            $this->change_state = ($value != $this->obj->__get('state'));
            $this->obj->__set('state', $value);

        }
    }

    // Устанавливает способ доставки
    public function setDelivery($value) {

        if (!empty($this->obj)) {
            $this->obj->__set('delivery', $value);
        }
    }

    // Вернет стоимость доставки
    public function getDeliveryPrice() {

        if (!empty($this->obj) && empty($this->delivery_price)) {
            if ($delivery = ormObjects::get($this->obj->delivery, 'eshop_delivery_method')) {
                $this->delivery_price = $delivery->price;
            }
        }

        return $this->delivery_price;
    }


    // Вернет ID пользователя, который сделал данный заказ
    public function getUserId() {
        if (!empty($this->obj))
            return $this->obj->getParentId();
        else
            return 0;
    }


    // Устанавливает способ оплаты
    public function setPayment($value) {

        if (!empty($this->obj)) {
            //$this->obj->__set('delivery', $value);
        }
    }


    public function __set($name, $value) {

        if (!empty($this->obj) && !in_array($name, $this->tabu_list))
            $this->obj->__set($name, $value);
    }

    public function __get($name) {

        if (!empty($this->obj))
            return $this->obj->__get($name);
        else
            return '';
    }

    private function sendStateMsg() {

        if (!empty($this->obj))

            if ($this->change_state && ($state = ormObjects::get($this->obj->state)) && $state->info) {

                page::assign('order.id', $this->obj->id);
                page::assign('order.number', $this->getNumber());
                page::assign('order.cost', $this->getTotalCost());

                // Информация о доставке
                page::assign('order.delivery', $this->obj->_delivery);
                page::assign('order.delivery_price', $this->getDeliveryPrice());
                page::assign('order.name', $this->obj->delivery_name);
                page::assign('order.surname', $this->obj->delivery_surname);
                page::assign('order.phone', $this->obj->delivery_phone);
                page::assign('order.address', $this->obj->delivery_address);

                page::assign('username', user::get('surname').' '.user::get('name'));
                page::assign('goods_list', page::macros('eshop')->goodsList($this->obj->id, 'goods_list_email'));

                $text = str_replace(array('{', '}'), '%', $state->email_msg);
                $title = str_replace(array('{', '}'), '%', $state->email_title);

                    $mail = new phpmailer();
                    $mail->WordWrap = 50;
                    $mail->IsHTML(true);
                    $mail->From = domains::curDomain()->getEmail();
                    $mail->FromName = domains::curDomain()->getSiteName();
                    $mail->Subject = page::parse($title);
                    $mail->Body = page::parse($text);

                    // Отправляем письмо пользователю
                    $mail->AddAddress($this->obj->email);
                    $mail->Send();

                // Отправляем письмо администратору
                if ($state->id == reg::getKey('/eshop/fisrt_state') && reg::getKey('/eshop/dubl_to_email')) {
                    $mail->ClearAddresses();
                    $mail->AddAddress(domains::curDomain()->getEmail());
                    $mail->Send();
                }
            }
    }

    public function save() {

        if (basket::getCount() > 0) {

            $order_id = $this->obj->save();

            if ($order_id) {

                // Заказ сохранен добавляем в него товары

                $goods_list = basket::getGoodsData();
                while(list($id, $goods) = each($goods_list)) {

                    $obj = new ormObject();
                    $obj->setClass('eshop_goods');
                    $obj->setParent($order_id);

                    $obj->goods_id = $goods['goods_id'];
                    $obj->count = $goods['count'];
                    $obj->cost = $goods['cost'];
                    
                    $obj->save();
                }

                // Если нужно, отправляем сообщение для текущего статуса
                $this->sendStateMsg();
                
                basket::clear();
                return $order_id;

            } else {

                //echo $this->obj->getErrorListText();
                // Произошла ошибка

            }

        } else if (!empty($this->obj)) {

            // Изменяем данные заказа

            $order_id = $this->obj->save();

            if ($order_id) {

                
                // Если нужно, отправляем сообщение для текущего статуса
                $this->sendStateMsg();
                return $order_id;

            } else {

                //echo $this->obj->getErrorListText();
                // Произошла ошибка

            }

        } else {

            // Корзина путая, создать новый заказ не возможно

        }

        return false;
    }

    // Удаляем заказ из БД
    function delete() {

        if (!empty($this->obj)) {

            if (($state = ormObjects::get($this->obj->state)) && $state->is_delete) {
                $this->obj->delete();     
                return true;
            }
        }

        return false;
    }



}

?>