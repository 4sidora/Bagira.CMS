<?php

class __list {

    // вывод списка
	public function defAction() {

        if (!system::issetUrl(2))
        	system::redirect('/constructor');

        $class = ormClasses::get(system::url(2));

        if (!$class->isInheritor('handbook') || $class->getSName() == 'handbook')
        	system::redirect('/constructor');

        ui::setHeader(lang::get('CONSTR_LIST').'"'.$class->getName().'"');
        ui::setBackButton('/constructor');

        $count = 0;
        $fields = $class->loadFields();
        while(list($key, $field) = each($fields))
			if ($field['f_view'] == 1) $count ++;

        $sel = new ormSelect($class->getSName());

        if ($count > 3) {
	        ui::newButton(lang::get('BTN_NEW_LIST'), '/constructor/list_add/'.system::url(2));
	        //$objects = ormObjects::getObjectsByClass(system::url(2));



	        $table = new uiTable($sel);
	        $table->showSearch(true);
	        $table->addColumn('name', 'Имя объекта');

	        $table->defaultRight('list_upd');
	        $table->addRight('list_upd', 'edit', single);
	        $table->addRight('list_del', 'drop', multi);

	        return $table->getHTML();

        } else {

			ui::newButton(lang::get('BTN_SAVE'), "javascript:sendForm('save');");
	        ui::newButton(lang::get('BTN_APPLY'), "javascript:sendForm('apply');");

	        $form = new ormMultiForm('change');
	        $form->setData($sel);
	        $form->setRight('list_proc_upd');
	        $form->moreParam(system::url(2));
             /*
	       // $form->addColumn('name', '', 150, '', 0);
	       // $form->addColumn('nositeli', '', 120, '', 0);
            $form->addColumn('e_mail', '', 200, 'правпы', 1);
            $form->addColumn('spiska', '', 150, '', 1);
            $form->addColumn('url', '', 150, '', 1);

            function email($val, $obj){            	return '<a href="mailto:'.$val.'">'.$val.'</a>';
            }
                    */

	        //$form->withoutAdditions();
			//$form->withoutRemoving();

	        return $form->getHTML();
        }
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
			$right = 'list_proc_upd';

		} else if (system::action() == "add") {

			// Если это добавление нового объекта
            $right = 'list_proc_add';
            $class_id = system::url(2);

			$obj = new ormObject();
            $obj->setClass($class_id);
		}
                //print_r($obj->getErrorList());
        // Если произошли ошибки, перенаправляем на главную страницу модуля
		if ($obj->issetErrors())
			system::redirect('/constructor');

        // Устанавливаем кнопки для формы
        ui::setCancelButton('/constructor/list/'.$class_id);
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
	        	system::redirect('/constructor');

	        $form->process();

        	if ($_POST['parram'] == 'apply')
				system::redirect('/constructor/list/'.$class->id());
			else
				system::redirect('/constructor');

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
				system::redirect('/constructor/list/'.system::POST('class_id'));

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
		    	system::redirect('/constructor/list_'.$mini_action.'/'.$obj_id);
		 	}

	        // Если данные изменились корректно перенаправляем на соответствующию страницу
			if ($_POST['parram'] == 'apply')
				system::redirect('/constructor/list_upd/'.$obj_id);
			else
				system::redirect('/constructor/list/'.$obj->getClass()->id());
		}

  	}

  	// удаление объекта
  	public function del() {

		if (system::issetUrl(2) && is_numeric(system::url(2))) {

            // Одиночное удаление
			ormObjects::get(system::url(2))->toTrash();
			echo 'delete';

        } else if (isset($_POST['objects'])) {

        	// Множественное удаление
        	while(list($id, $val) = each($_POST['objects']))
                ormObjects::get($id)->toTrash();

            echo 'delete';
        }

        system::stop();

  	}


}

?>