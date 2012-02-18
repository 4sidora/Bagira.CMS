<?php

class __user {

    public function __construct() {
    	ui::checkClasses('subscription', 'subscribe_msg', 'subscribe_user');
    }

    //
	public function defAction() {

        if (!system::issetUrl(2))
        	system::redirect('/subscription');

 		if ($parent = ormObjects::get(system::url(2))) {
	 		ui::setNaviBar(lang::right('user'));
	        ui::setHeader($parent->name);
        }

        ui::newButton(lang::get('SUBSCRIBE_BTN_ADD3'), '/subscription/user_add/'.system::url(2));
        ui::newButton(lang::get('SUBSCRIBE_BTN_ADD4'), '/subscription/user_addlist/'.system::url(2));
        ui::setBackButton('/subscription');

	    $sel = new ormSelect('subscribe_user');
        $sel->where('parents', '=', system::url(2));

        $table = new uiTable($sel);

        $table->showSearch(true);
        $table->moreParam(system::url(2));

        $table->addColumn('name', lang::get('SUBSCRIBE_USER_TT1'), 300);
        $table->addColumn('second_name first_name', lang::get('SUBSCRIBE_USER_TT2'), 300);

        $table->defaultRight('user_upd');
        $table->addRight('user_upd', 'edit', single);
        $table->addRight('user_del', 'drop', multi);

        return $table->getHTML();
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
			if (!($obj = ormObjects::get(system::url(2))))
				system::redirect('/subscription');

			$parent_id = $obj->parent_id;
			$right = 'user_proc_upd';

		} else if (system::action() == "add") {

			// Если это добавление нового объекта
			$parent_id = system::url(2);
            $right = 'user_proc_add';

			$obj = new ormObject();
            $obj->setClass('subscribe_user');
            $obj->setParent($parent_id);

		}

        // Если произошли ошибки, перенаправляем на главную страницу модуля
		if ($obj->issetErrors())
			system::redirect('/subscription');

        // Устанавливаем кнопки для формы
        ui::setCancelButton('/subscription/user/'.$parent_id);
		ui::newButton(lang::get('BTN_SAVE'), "javascript:sendForm('save');");
        ui::newButton(lang::get('BTN_APPLY'), "javascript:sendForm('apply');");


        // Создаем форму и выводим ее на страницу
        $form = new ormEditForm($obj, $right);

        if (($user = ormObjects::get($obj->user_id)) && user::issetRight('user_upd', 'users'))  {
            $un = $user->surname.' '.$user->name.' ('.$user->login.')';
        	$form->replaceField('user_id', '<a href="'.system::au().'/users/user_upd/'.$user->id.'">'.$un.'</a>');
        } else
        	$form->replaceField('user_id', '', true);

        return $form->getHTML();
 	}





 	// обработчик добавления объекта
  	public function proc_add() {
		$this->proc_upd();
  	}

 	// обработчик изменения объекта
  	public function proc_upd() {

            // Обработчик для еденичного изменения класса
	        $mini_action = substr(system::action(), -3);

	        if (system::action() == "proc_upd") {

				$obj = ormObjects::get(system::POST('obj_id'));

				$parent_id = $obj->parent_id;

			} else if (system::action() == "proc_add") {

	            $obj = new ormObject();
		        $obj->setClass('subscribe_user');
                $parent_id = system::POST('obj_id');
			}

	        // Если произошли ошибки, перенаправляем на главную страницу модуля
			if ($obj->issetErrors())
				system::redirect('/subscription');

	        // Присваиваем пришедшие значения полям в объекте
	        $obj->loadFromPost();

	        // Сохраняем изменения
	        $obj_id = $obj->save();

            if ($obj->issetErrors(29)) {

            	// Если указанный e-mail уже существует, пытаемся найти его и подписать на рассылки.
                $sel = new ormSelect('subscribe_user');
                $sel->where('name', '=', system::POST('name', isString));
                $sel->limit(1);

                if ($obj = $sel->getObject()) {

                    $obj->tabuList('subscribes');
                    $obj->loadFromPost();

					reset($_POST['subscribes']);
					while(list($key, $val) = each($_POST['subscribes']))
						$obj->setNewParent($val);

					$obj_id = $obj->save();
              	}
       		}


       		// Если объект не сохранился, выводим пользователю текст ошибки.
       		if ($obj_id === false) {

	            system::savePostToSession();
		    	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), $obj->getErrorListText());
		    	ui::selectErrorFields($obj->getErrorFields());

	            $obj_id = (empty($_POST['obj_id'])) ? '' : $_POST['obj_id'];
		    	system::redirect('/subscription/user_'.$mini_action.'/'.$obj_id);
		 	}

	        // Если данные изменились корректно перенаправляем на соответствующию страницу
			if ($_POST['parram'] == 'apply')
				system::redirect('/subscription/user_upd/'.$obj_id);
			else
				system::redirect('/subscription/user/'.$parent_id);


  	}


  	// удаление объекта
  	public function del() {

		if (system::issetUrl(2) && is_numeric(system::url(2))) {

            // Одиночное удаление
			if (mailingProcess::delEmailById(system::url(2), system::url(3)));
				echo 'delete';

        } else if (isset($_POST['objects'])) {


        	// Множественное удаление
        	while(list($id, $val) = each($_POST['objects']))
                mailingProcess::delEmailById($id, system::url(3));

            echo 'delete';
        }

        system::stop();

  	}






  	// форма добавления списка подписчиков
	public function addlist() {

        // Устанавливаем кнопки для формы
        ui::setCancelButton('/subscription/user/'.system::url(2));
		ui::newButton(lang::get('BTN_SAVE'), "javascript:sendForm('save');");

        if (file_exists(MODUL_DIR.'/subscription/template/add_userlist.tpl'))
        	include(MODUL_DIR.'/subscription/template/add_userlist.tpl');

        // Добавляем поле "Подписан на рассылку"
        $html = ui::SelectBox(
        	'subscribes',
        	ormObjects::getObjectsByClass('subscription'),
        	system::url(2), 400, '', '', 'selectbox_multi_big'
        );

        page::assignArray(lang::get('SUBSCRIBE_TEXT'));
        page::assign('parent_id', system::url(2));

        return page::parse($TEMPLATE['frame']);
 	}

 	// обработчик добавления списка подписчиков
  	public function proc_addlist() {

            $count = 0;

            if (!empty($_POST['emails']) && !empty($_POST['subscribes'])) {

                preg_match_all ("/[-0-9a-z_\.]+@[-0-9a-z^\.]+\.[a-z]{2,4}/i" , $_POST['emails'], $mas);

                while (list($num, $email) = each ($mas[0])) {

                    $id = mailingProcess::addEmail($email, $_POST['subscribes']);

                    if ($id) $count ++;
	 			}
    		}

            ui::MessageBox(lang::get('SUBSCRIBE_ADDED_EMAILS').ruNumbers::decl($count, lang::get('SUBSCRIBE_AE')).'.');

	        system::redirect('/subscription/user/'.system::POST('parent_id', isInt));


  	}

}

?>