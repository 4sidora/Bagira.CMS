<?php

class __val {

	public function __construct() {
    	ui::checkClasses('handbook');
    }

	// форма добавления объекта
	public function add() {
		return $this->upd();
 	}

 	// форма редактирования объекта
	public function upd() {

        // Указываем для какого объекта строить форму
        if (system::action() == "upd") {

			// Если это редактирование
			$obj = ormObjects::get(system::url(2));
			$class_id = $obj->getClass()->id();
			$right = 'val_proc_upd';

		} else if (system::action() == "add") {

			// Если это добавление нового объекта
            $right = 'val_proc_add';
            $class_id = system::url(2);

			$obj = new ormObject();
            $obj->setClass($class_id);
		}

        // Если произошли ошибки, перенаправляем на главную страницу модуля
		if ($obj->issetErrors())
			system::redirect('/reference');

        // Устанавливаем кнопки для формы
        ui::setCancelButton('/reference/values/'.$class_id);
		ui::newButton(lang::get('BTN_SAVE'), "javascript:sendForm('save');");
        ui::newButton(lang::get('BTN_APPLY'), "javascript:sendForm('apply');");

        // Создаем форму и выводим ее на страницу
        $form = new ormEditForm($obj, $right);
        return $form->getHTML();
 	}

 	// обработчик добавления объекта
  	public function proc_add() {
		$this->proc_upd();
  	}

 	// обработчик изменения объекта
  	public function proc_upd() {

        if (isset($_POST['objchange'])) {

            // Обработчик мультиформы
            $form = new ormMultiForm('change');

            $class = ormClasses::get($form->getParam(0));
	        if (!$class->isInheritor('handbook') || $class->getSName() == 'handbook')
	        	system::redirect('/reference');

            if (!user::issetRight('val_add'))
	        	$form->withoutAdditions();

	        if (!user::issetRight('val_del'))
				$form->withoutRemoving();

	        $form->process();

        	if ($_POST['parram'] == 'apply')
				system::redirect('/reference/values/'.$class->id());
			else
				system::redirect('/reference');

        } else {

            // Обработчик для еденичного изменения класса
	        $mini_action = substr(system::action(), -3);

	        if (system::action() == "proc_upd") {

	            // Говорим какой объект нужно изменить
				$obj = ormObjects::get(system::POST('obj_id'));

			} else if (system::action() == "proc_add") {

	            // Говорим какой объект нужно создать
				$obj = new ormObject();
	            $obj->setClass(system::POST('class_id'));

			}

	        // Если произошли ошибки, перенаправляем на главную страницу модуля
			if ($obj->issetErrors())
				system::redirect('/reference/values/'.system::POST('class_id'));

	        // Присваиваем пришедшие значения полям в объекте
	        $obj->loadFromPost($mini_action);

	        // Сохраняем изменения
	        $obj_id = $obj->save();

	        // Если объект не сохранился, выводим пользователю текст ошибки.
	        if ($obj_id === false) {

	            system::savePostToSession();
		    	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), $obj->getErrorListText());
		    	ui::selectErrorFields($obj->getErrorFields());

	            $obj_id = (empty($_POST['obj_id'])) ? system::POST('class_id') : system::POST('obj_id');
		    	system::redirect('/reference/val_'.$mini_action.'/'.$obj_id);
		 	}

	        // Если данные изменились корректно перенаправляем на соответствующию страницу
			if ($_POST['parram'] == 'apply')
				system::redirect('/reference/val_upd/'.$obj_id);
			else
				system::redirect('/reference/values/'.$obj->getClass()->id());
		}

  	}

  	// удаление объекта
  	public function del() {

		if (system::issetUrl(2) && is_numeric(system::url(2))) {

            // Одиночное удаление
			if ($obj = ormObjects::get(system::url(2)))
				$obj->toTrash();
			echo 'delete';

        } else if (isset($_POST['objects'])) {

        	// Множественное удаление
        	while(list($id, $val) = each($_POST['objects']))
                if ($obj = ormObjects::get($id))
                	$obj->toTrash();

            echo 'delete';
        }

        system::stop();

  	}


}

?>