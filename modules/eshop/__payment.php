<?php

class __payment {

    public function __construct() {
    	ui::checkClasses('handbook');
    }

	// вывод списка
	public function defAction() {

        ui::addLeftButton(lang::right('list'), 'list');
        ui::addLeftButton(lang::right('state'), 'state');
        ui::addLeftButton(lang::right('delivery'), 'delivery');
        ui::addLeftButton(lang::right('payment'), 'payment');


        ui::newButton(lang::get('ESHOP_ADD_PAYMENT'), '/eshop/payment_add');

        $sel = new ormSelect('eshop_payment_method');

        $table = new uiTable($sel);
        $table->addColumn('name', 'Название', 500);

        $table->defaultRight('payment_upd');
        $table->addRight('payment_upd', 'edit', single);
        $table->addRight('payment_del', 'drop', multi);

        return $table->getHTML();

 	}


    function add(){
        return $this->upd();
    }

    function upd(){


        // Устанавливаем кнопки для формы
        ui::setCancelButton('/eshop/payment');
        ui::newButton(lang::get('BTN_SAVE'), "javascript:sendForm('save');");
	    ui::newButton(lang::get('BTN_APPLY'), "javascript:sendForm('apply');");

        if (system::action() == "upd") {

            if (!$obj = ormObjects::get(system::url(2), 'eshop_payment_method'))
                system::redirect('/eshop/payment');

            $right = 'payment_proc_upd';

        } else if (system::action() == "add") {

            $obj = new ormObject();		
            $obj->setClass('eshop_payment_method');
            
            $right = 'payment_proc_add';
        }

        page::assign('right', $right);

        // Создаем форму и выводим ее на страницу
        $form = new ormEditForm($obj, $right);
        return $form->getHTML();
    }

    function proc_add() {
        $this->proc_upd();
    }

    function proc_upd(){

        $mini_action = substr(system::action(), -3);

        if (system::action() == "proc_upd") {

            // Говорим какой объект нужно изменить
			if (!$obj = ormObjects::get(system::POST('obj_id'), 'eshop_payment_method'))
                system::redirect('/eshop/payment');

		} else if (system::action() == "proc_add") {

            // Говорим какой объект нужно создать
			$obj = new ormObject();
            $obj->setClass('eshop_payment_method');

		}

        // Если произошли ошибки, перенаправляем на главную страницу модуля
		if ($obj->issetErrors())
			system::redirect('/eshop/payment');

        // Присваиваем пришедшие значения полям в объекте
        $obj->loadFromPost();

        // Сохраняем изменения
        $obj_id = $obj->save();

        // Если объект не сохранился, выводим пользователю текст ошибки.
        if ($obj_id === false) {

            system::savePostToSession();
	    	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), $obj->getErrorListText());
	    	ui::selectErrorFields($obj->getErrorFields());

            $class = ($mini_action == 'add') ? '' : $_POST['obj_id'];
            system::redirect('/eshop/payment_'.$mini_action.'/'.$class);

	 	}

        // Если данные изменились корректно перенаправляем на соответствующию страницу
		if ($_POST['parram'] == 'apply')
			system::redirect('/eshop/payment_upd/'.$obj_id);
        else
        	system::redirect('/eshop/payment');

    }

    // удаление объекта
  	public function del() {

        if (system::issetUrl(2) && is_numeric(system::url(2))) {

            // Одиночное удаление
	    	if ($obj = ormObjects::get(system::url(2), 'eshop_payment_method')){
				$obj->toTrash();
				echo 'delete';
            }

        } else if (isset($_POST['objects'])) {

        	// Множественное удаление
        	while(list($id, $val) = each($_POST['objects'])) {

        		if ($obj = ormObjects::get($val, 'eshop_payment_method'))
					$obj->toTrash();
		
        	}
            echo 'delete';
        }

        system::stop();
  	}


}

?>