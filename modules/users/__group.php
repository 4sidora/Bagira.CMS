<?php

class __group {

	public function __construct() {
    	ui::checkClasses('user_group', 'user');
    }

    // форма добавления
	public function add() {
		return $this->upd();
 	}

 	// форма редактирования
	public function upd() {

        // Указываем для какого объекта строить форму
        if (system::action() == "upd") {

			// Если это редактирование
			$obj = ormObjects::get(system::url(2));

			// Заголовок страницы
		    ui::setNaviBar(lang::right('group_upd'));
	        ui::setHeader($obj->name);

			$right = 'group_proc_upd';

		} else if (system::action() == "add") {

			// Если это добавление нового объекта
			$obj = new ormObject();
			$obj->setParent(0);
            $obj->setClass('user_group');

            // Устанавливает параметры по умолчанию
            $obj->active = 1;

            $right = 'group_proc_add';
		}

        // Если произошли ошибки, перенаправляем на главную страницу модуля
		if (!$obj->isInheritor('user_group'))
			system::redirect('/users');

        // Устанавливаем кнопки для формы
        ui::setCancelButton('/users/grouplist');
		ui::newButton(lang::get('BTN_SAVE'), "javascript:sendForm('save');");
        ui::newButton(lang::get('BTN_APPLY'), "javascript:sendForm('apply');");

        // Создаем форму и выводим ее на страницу
        $form = new ormEditForm($obj, $right);

        $form->attachJavaScript('/css_mpanel/group_upd.js');
        $form->addInBottomTabs('base', rights::getListForModuls(system::url(2), 0));

        // Здесь можно переопределить стандартные параметры формы редактирования
        // ...

        return $form->getHTML();
 	}


    // обработчик добавления объекта
  	public function proc_add() {
		$this->proc_upd();
  	}

 	// обработчик изменения объекта
  	public function proc_upd() {

        $mini_action = substr(system::action(), -3);

        if (system::action() == "proc_upd") {

            // Говорим какой объект нужно изменить
			$obj = ormObjects::get(system::POST('obj_id'));

		} else if (system::action() == "proc_add") {

            // Говорим какой объект нужно создать
			$obj = new ormObject();
            $obj->setClass('user_group');

		}

        // Если произошли ошибки, перенаправляем на главную страницу модуля
		if (!$obj->isInheritor('user_group'))
			system::redirect('/users');

        // Присваиваем пришедшие значения полям в объекте
        $obj->loadFromPost($mini_action);

        // Сохраняем изменения
        $obj_id = $obj->save();

        // Если объект не сохранился, выводим пользователю текст ошибки.
        if ($obj_id === false) {

            system::savePostToSession();
	    	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), $obj->getErrorListText());
	    	system::redirect('/users/group_'.$mini_action.'/'.$_POST['obj_id']);

	 	} else rights::setListForModuls($obj_id, 0);

        // Если данные изменились корректно перенаправляем на соответствующию страницу
		if ($_POST['parram'] == 'apply')
			system::redirect('/users/group_upd/'.$obj_id);
		else
			system::redirect('/users');

  	}

  	// изменение активности объекта
  	public function act() {

        if (!isset($_POST['objects']) && system::issetUrl(2) && is_numeric(system::url(2))) {

            // Одиночное изменение
	    	$obj = ormObjects::get(system::url(2));

	    	if ($obj->isInheritor('user_group') && $obj->id != 32 && $obj->id != 48){
				$obj->active = ($obj->active) ? false : true;
				$obj->save();

				if (!$obj->issetErrors())
					echo ($obj->active) ? 'active' : 'no_active';
	        }

        } else if (isset($_POST['objects'])) {

        	// Множественное изменение
        	$invert = true;

        	while(list($id, $val) = each($_POST['objects'])) {

        		if (is_numeric($id)) {

        			$obj = ormObjects::get($id);

			    	if ($obj->isInheritor('user_group') && $obj->id != 32 && $obj->id != 48){
						$obj->active = ($obj->active) ? false : true;
						$obj->save();

						if ($obj->issetErrors())
							$invert = false;
			        }
				}
        	}

        	if ($invert) echo 'invert';
        }

        system::stop();

  	}

  	// удаление объекта
  	public function del() {

        if (!isset($_POST['objects']) && system::issetUrl(2) && is_numeric(system::url(2))) {

            // Одиночное удаление
			$obj = ormObjects::get(system::url(2));

	    	if ($obj->isInheritor('user_group') && $obj->id != 32 && $obj->id != 48) {
				$obj->toTrash();
				echo 'delete';
            }

        } else if (isset($_POST['objects'])) {

        	// Множественное удаление
        	while(list($id, $val) = each($_POST['objects'])) {

        		if (is_numeric($id)) {

        			$obj = ormObjects::get($id);

			    	if ($obj->isInheritor('user_group') && $obj->id != 32 && $obj->id != 48)
						$obj->toTrash();
				}
        	}
            echo 'delete';
        }

        system::stop();
  	}
}

?>