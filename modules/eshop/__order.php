<?php

class __order {

    public function __construct() {
    	ui::checkClasses('handbook');
    }

    function view(){

        // Устанавливаем кнопки для формы
        ui::setCancelButton('/eshop/list');
        ui::newButton(lang::get('BTN_SAVE'), "javascript:sendForm('save');");
	    ui::newButton(lang::get('BTN_APPLY'), "javascript:sendForm('apply');");

        if (file_exists(MODUL_DIR.'/eshop/template/order.tpl'))
        	include(MODUL_DIR.'/eshop/template/order.tpl');


        if (!$obj = ormObjects::get(system::url(2), 'eshop_order'))
            system::redirect('/eshop/list');

        $order = new eShopOrder($obj);


        // Вывод информации о товарах
        function getGoodsName($val, $obj) {
            if ($obj = ormPages::get($val, 'goods')) {
                return '<a href="'.$obj->url.'" target="_blank">'.$obj->name.'</a>';
            }
        }

        function getPrice($val, $obj) {
            return '&nbsp;&nbsp;&nbsp;&nbsp;'.$obj->cost * $obj->count.' руб.';
        }

        $sel = new ormSelect('eshop_goods');
        $sel->where('parents', '=', $obj->id);

        $table = new ormMultiForm('goods');
        $table->setData($sel);
        $table->insideForm();
        $table->addColumn('goods_id', 0, 400, 0, false, 'getGoodsName');
        $table->addColumn('cost', 0, 100, 0, false);
        $table->addColumn('count', 0, 100);
        $table->addColumn('id', 'Общая стоимость', 100, 0, false, 'getPrice');
        $table->withoutAdditions();
        page::assign('goods_list', $table->getHTML());


        page::assign('order.number', $order->getNumber());
        page::assign('order.cost', $order->getCost());
        page::assign('order.cost_all', $order->getTotalCost());
        page::assign('order.delivery_price', $order->getDeliveryPrice());

        page::assign('order.delivery', $order->_delivery);

        $obj->parseAllFields();

        page::assign('obj.id', $order->id);
        page::assign('obj.date', date('d.m.Y в H:i', strtotime($obj->date)));

        page::assign('user_link', '/mpanel/users/user_upd/'.$order->getUserId());

        ui::SelectBox('state', ormObjects::getObjectsByClass('eshop_status'), $obj->state, 200);
        ui::CheckBox('is_payment', 1, $obj->is_payment, 'Оплачено');


        // Создаем форму и выводим ее на страницу
       // $form = new ormEditForm($obj, $right);
        //return $form->getHTML();
        return page::parse($TEMPLATE['frame']);
    }

    function proc_view(){

        // Говорим какой объект нужно изменить
		if (!$obj = ormObjects::get(system::POST('obj_id'), 'eshop_order'))
            system::redirect('/eshop/list');

        $order = new eShopOrder($obj);


        $order->setState(system::POST('state', isInt));

        
        $order->is_payment = system::POST('is_payment', isBool);
        if (system::POST('is_payment', isBool))
            $order->payment_date = date('Y-m-d H:i:s');

        $order->delivery_name = system::POST('delivery_name', isString);
        $order->delivery_surname = system::POST('delivery_surname', isString);
        $order->delivery_phone = system::POST('delivery_phone', isString);
        $order->delivery_address = system::POST('delivery_address', isText);
        $order->delivery_notice = system::POST('delivery_notice', isText);
        $order->notice = system::POST('notice', isText);

        $form = new ormMultiForm('goods');        
        $form->withoutAdditions();
        $form->process();

        // Сохраняем изменения
        $obj_id = $order->save();

        // Если объект не сохранился, выводим пользователю текст ошибки.
        if ($obj_id === false) {

            //system::savePostToSession();
	    	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), $obj->getErrorListText());
	    	ui::selectErrorFields($obj->getErrorFields());

            system::redirect('/eshop/order_view/'.system::POST('obj_id'));
	 	}


        // Если данные изменились корректно перенаправляем на соответствующию страницу
		if ($_POST['parram'] == 'apply')
			system::redirect('/eshop/order_view/'.$obj_id);
        else
        	system::redirect('/eshop/list');

    }

    // удаление объекта
  	public function del() {

        if (system::issetUrl(2) && is_numeric(system::url(2))) {

            // Одиночное удаление
	    	if ($obj = eShopOrder::get(system::url(2))){
				$obj->delete();
				echo 'delete';
            }

        } else if (isset($_POST['objects'])) {

        	// Множественное удаление
        	while(list($id, $val) = each($_POST['objects'])) {

        		if ($obj = eShopOrder::get($val))
					$obj->delete();
		
        	}
            echo 'delete';
        }

        system::stop();
  	}


}

?>