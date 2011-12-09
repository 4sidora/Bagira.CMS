<?php

class __subscribe {

	public function __construct() {
    	ui::checkClasses('subscription', 'subscribe_msg', 'subscribe_user');
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
			$right = 'subscribe_proc_upd';

		} else if (system::action() == "add") {

			// Если это добавление нового объекта
            $right = 'subscribe_proc_add';
			$obj = new ormObject();
            $obj->setClass('subscription');

            $obj->back_email = domains::curDomain()->getEmail();
            $obj->back_name = domains::curDomain()->getSiteName();
            $obj->template = 'default';
            $obj->active = 1;
		}

        // Если произошли ошибки, перенаправляем на главную страницу модуля
		if ($obj->issetErrors())
			system::redirect('/subscription');

        // Устанавливаем кнопки для формы
        ui::setCancelButton('/subscription');
		ui::newButton(lang::get('BTN_SAVE'), "javascript:sendForm('save');");
        ui::newButton(lang::get('BTN_APPLY'), "javascript:sendForm('apply');");

        // Создаем форму и выводим ее на страницу
        $form = new ormEditForm($obj, $right);

        $form->replaceField('template', $this->templList('template', $obj->template));
        $form->replaceField('name_format', ui::SelectBox('name_format', lang::get('SUBSCRIBE_NAMEFORMAT'), $obj->name_format, 440));

        return $form->getHTML();
 	}


 	private function templList($name, $value, $size = 420) {
 		$array = ARRAY();
        $old_file = '';
        $patch = TEMPL_DIR.'/subscription/mails';

        if ($handle = opendir($patch)) {
        	while (false !== ($file = readdir($handle))) {

         		if (substr($file, strlen($file)-3, 3) == 'tpl'){


                    include_once($patch.'/'.$file);

                    $file_name = substr($file, 0, strlen($file)-4);

                    $title = (!empty($TEMPLATE['name'])) ? $TEMPLATE['name'] : $file_name;

                   /* if (!empty($TEMPLATE['name']) && $old_file != $file_name) {
	                    $title = $TEMPLATE['name'];
                        $old_file = $file_name;
                    } else $title = $file_name;  */


                    $temp[0] = $file_name;
                    $temp[1] = $title;
                    $array[] = $temp;

                    $TEMPLATE['name'] = '';

                }
           	}

            closedir($handle);
        }

        if (empty($value))
        	$value = 'default';

        return ui::SelectBox($name, $array, $value, $size);
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

	            // Говорим какой объект нужно изменить
				$obj = ormObjects::get(system::POST('obj_id'));

			} else if (system::action() == "proc_add") {

	            // Говорим какой объект нужно создать
				$obj = new ormObject();
	            $obj->setClass('subscription');

			}

	        // Если произошли ошибки, перенаправляем на главную страницу модуля
			if ($obj->issetErrors())
				system::redirect('/subscription');

	        // Присваиваем пришедшие значения полям в объекте
	        $obj->loadFromPost($mini_action);

            $obj->lang = languages::curId();
            $obj->domain = domains::curId();

	        // Сохраняем изменения
	        $obj_id = $obj->save();

	        // Если объект не сохранился, выводим пользователю текст ошибки.
	        if ($obj_id === false) {

	            system::savePostToSession();
		    	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), $obj->getErrorListText());
		    	ui::selectErrorFields($obj->getErrorFields());

	            $obj_id = (empty($_POST['obj_id'])) ? '' : $_POST['obj_id'];
		    	system::redirect('/subscription/subscribe_'.$mini_action.'/'.$obj_id);
		 	}

	        // Если данные изменились корректно перенаправляем на соответствующию страницу
			if ($_POST['parram'] == 'apply')
				system::redirect('/subscription/subscribe_upd/'.$obj_id);
			else
				system::redirect('/subscription');


  	}

  	// удаление объекта
  	public function del() {

		if (system::issetUrl(2) && is_numeric(system::url(2))) {

            // Одиночное удаление
			if ($obj = ormObjects::get(system::url(2)))
				if ($obj->isInheritor('subscription'))
					$obj->toTrash();

			echo 'delete';

        } else if (isset($_POST['objects'])) {

        	// Множественное удаление
        	while(list($id, $val) = each($_POST['objects']))
                if ($obj = ormObjects::get($id))
                	if ($obj->isInheritor('subscription'))
                		$obj->toTrash();

            echo 'delete';
        }

        system::stop();

  	}


  	// изменение активности объекта
  	public function act() {

        if (system::issetUrl(2) && is_numeric(system::url(2))) {

            // Одиночное изменение
	    	$obj = ormObjects::get(system::url(2));

	    	if ($obj->isInheritor('subscription')){
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

			    	if ($obj->isInheritor('subscription')){
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


  	// Просмотр истории изменения
  	public function history() {

        if (system::url(2) == 0)
			system::redirect('/subscription');

		if ($obj = ormObjects::get(system::url(2))) {

	        ui::setBackButton('/subscription');

	        ui::setNaviBar(lang::right('subscribe_history'));
			ui::setHeader($obj->name);

	        return ui::getHistoryTable($obj->id);
    	}

	    system::redirect('/subscription');

    }


}

?>