<?php

class __user {

	public function __construct() {
    	ui::checkClasses('user_group', 'user');
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

			// Заголовок страницы
		    ui::setNaviBar(lang::get('TEXT_EDIT').$obj->getClass()->getPadej(1));
	        ui::setHeader($obj->login);

	        $class_list = '';

			$group_id = (system::issetUrl(3)) ? system::url(3) : $obj->getParentId();
			$right = 'user_proc_upd';

		} else if (system::action() == "add") {

            $class_name = (system::issetUrl(3)) ? system::url(3) : 'user';

			// Если это добавление нового объекта
			$group_id = system::url(2);
            $right = 'user_proc_add';

            $obj = new ormObject();
			$obj->setParent($group_id);
            $obj->setClass($class_name);
            $obj->active = 1;

            // Формируем список классов для быстрого изменения класса объекта
		    $types = ormClasses::get('user')->getAllInheritors();
	        $class_list = '';
		    while(list($id, $name) = each($types))
	            if ($bc = ormClasses::get($id)) {
	                $url = system::au().'/users/user_add/'.$group_id.'/'.$bc->getSName();
			    	$class_list .= '<a href="'.$url.'" style="line-height:17px;">'.$bc->getName().'</a><br />';
                }

            // Заголовок страницы
			if ($group = ormObjects::get($group_id)) {
		        ui::setNaviBar($group->name, '/users/userlist/'.$group_id);
		        ui::setHeader(lang::get('TEXT_ADD').$obj->getClass()->getPadej(1));
	        }
		}

        // Если произошли ошибки, перенаправляем на главную страницу модуля
		if (!$obj->isInheritor('user'))
			system::redirect('/users/userlist');

        // Устанавливаем кнопки для формы
        if (empty($group_id))
        	ui::setCancelButton('/users/grouplist');
        else
  			ui::setCancelButton('/users/userlist/'.$group_id);

		ui::newButton(lang::get('BTN_SAVE'), "javascript:sendForm('save');");
        ui::newButton(lang::get('BTN_APPLY'), "javascript:sendForm('apply');");

        // Создаем форму и выводим ее на страницу
        $form = new ormEditForm($obj, $right);
        $form->setORMList($class_list);

        // Изменяем вид поля "Модуль по умолчанию"
        $modules = user::getModulesForObject($obj);
        $form->replaceField('def_modul', ui::SelectBox('def_modul', $modules, $obj->def_modul, 400));



        // Добавляем вкладку "Права доступа"
        $tab_content = '<div style="margin-left:20px;width: 950px;">'.lang::get('USERS_TEXT_RIGHT_HINT').rights::getListForModuls($obj->id, 1).'</div>';
        $form->attachJavaScript('/css_mpanel/users_upd.js');
        $form->newTabs(lang::get('USERS_TABS_RIGHT'), $tab_content);

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

            $class_name = 'user';
            $class = ormClasses::get($_POST['class_id']);
   			if (is_a($class, 'ormClass') && $class->isInheritor('user'))
   				$class_name = $class->getSName();

            // Говорим какой объект нужно создать
			$obj = new ormObject();
            $obj->setClass($class_name);

		}

        // Если произошли ошибки, перенаправляем
		if ($obj->issetErrors())
			system::redirect('/users/userlist/'.$_POST['groups'][0]);

        // Присваиваем полям в объекте пришедшие значения из POST
        $obj->loadFromPost($mini_action);

        if ($obj->newVal('active'))
        	$obj->error_passw = 0;

        if ($obj->id == user::get('id'))
        	$obj->active = 1;

        // Сохраняем изменения
        $obj_id = $obj->save();

        // Если объект не сохранился, выводим пользователю текст ошибки.
        if ($obj_id === false) {

            system::savePostToSession();
	    	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), $obj->getErrorListText());
	    	ui::selectErrorFields($obj->getErrorFields());

            $obj_id = (empty($_POST['obj_id'])) ? $_POST['groups'][0] : $_POST['obj_id'];
	    	system::redirect('/users/user_'.$mini_action.'/'.$obj_id);

	 	} else {
            rights::setListForModuls($obj_id, 1);

            cache::delete('user'.$obj_id);
        }

        // Если данные изменились корректно перенаправляем на соответствующию страницу
		if ($_POST['parram'] == 'apply')
			system::redirect('/users/user_upd/'.$obj_id);
		else
			system::redirect('/users/userlist/'.$_POST['groups'][0]);

  	}

  	// изменение активности объекта
  	public function act() {

        if (system::issetUrl(2) && is_numeric(system::url(2))) {

            // Одиночное изменение
	    	$obj = ormObjects::get(system::url(2));

	    	if ($obj->isInheritor('user') && $obj->id != user::get('id')){
				$obj->active = ($obj->active) ? false : true;
				$obj->error_passw = 0;
				$obj->save();

                cache::delete('user'.$obj->id);

				$this->sendMailED($obj);

				if (!$obj->issetErrors())
					echo ($obj->active) ? 'active' : 'no_active';
	        }

        } else if (isset($_POST['objects'])) {

        	// Множественное изменение
        	$invert = true;

        	while(list($id, $val) = each($_POST['objects'])) {

        		if (is_numeric($id)) {

        			$obj = ormObjects::get($id);

			    	if ($obj->isInheritor('user') && $obj->id != user::get('id')){
						$obj->active = ($obj->active) ? false : true;
						$obj->error_passw = 0;
						$obj->save();

                        cache::delete('user'.$obj->id);

                        $this->sendMailED($obj);

						if ($obj->issetErrors())
							$invert = false;
			        }
				}
        	}

        	if ($invert) echo 'invert';
        }

        system::stop();
  	}

    // Отправляем письмо пользователю о вкл.\выкл. его акаунта
  	private function sendMailED($user) {

    	$mail_name = ($user->active == 1) ? 'enable' : 'disable';
    	page::assign('login', $user->login);
        page::assign('name', $user->name);
       	system::sendMail('/users/mails/'.$mail_name.'.tpl', $user->email);
  	}

  	// удаление объекта
  	public function del() {

        if (system::issetUrl(2) && is_numeric(system::url(2))) {

            // Одиночное удаление
			$obj = ormObjects::get(system::url(2));

	    	if ($obj->isInheritor('user') && $obj->id != user::get('id')) {
				$this->sendMailDel($obj);

                cache::delete('user'.$obj->id);

				$obj->toTrash();
				echo 'delete';
            }

        } else if (isset($_POST['objects'])) {

        	// Множественное удаление
        	while(list($id, $val) = each($_POST['objects'])) {

        		if (is_numeric($id)) {

        			$obj = ormObjects::get($id);

			    	if ($obj->isInheritor('user') && $obj->id != user::get('id')) {
			    		$this->sendMailDel($obj);
                        cache::delete('user'.$obj->id);
						$obj->toTrash();
					}
				}
        	}
            echo 'delete';
        }

        system::stop();
  	}

    // Отправляем письмо пользователю об удаление его акаунта
  	private function sendMailDel($user) {

     	page::assign('login', $user->login);
        page::assign('name', $user->name);
       	system::sendMail('/users/mails/delete.tpl', $user->email);
  	}


}

?>