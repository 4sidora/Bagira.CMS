<?php

class __msg {

    public function __construct() {
    	ui::checkClasses('subscription', 'subscribe_msg', 'subscribe_user');
    }

    //
	public function defAction() {

 		if ($parent = ormObjects::get(system::url(2))) {
	 		ui::setNaviBar(lang::right('msg'));
	        ui::setHeader($parent->name);
        } else system::redirect('/subscription');

        ui::setBackButton('/subscription');
        ui::newButton(lang::get('SUBSCRIBE_BTN_ADD2'), '/subscription/msg_add/'.system::url(2));

        function getState($date, $obj){

            if ($obj->error_part_num > 0) {

                $time = time() - strtotime($obj->last_subscribe);

                if ($time > 4)
            		return str_replace('%part%', $obj->error_part_num, lang::get('SUBSCRIBE_MSG', 1));
            	else
            		return str_replace('%part%', $obj->error_part_num, lang::get('SUBSCRIBE_MSG', 2));

            } else if ($obj->part_count_awaiting > 0) {

                page::assign('part1', ($obj->part_count - $obj->part_count_awaiting));
                page::assign('part2', $obj->part_count);

            	return page::parse(lang::get('SUBSCRIBE_MSG', 3));

            } else if ($obj->last_subscribe == '0000-00-00 00:00:00') {

            	return lang::get('SUBSCRIBE_MSG', 4);

            } else return lang::get('SUBSCRIBE_MSG', 5).' '.date('d.m.Y '.lang::get('SUBSCRIBE_MSG', 6).' H:i', strtotime($obj->last_subscribe));
        }

        // Выводим список сообщений рассылки
	    $sel = new ormSelect('subscribe_msg');
        $sel->fields('name, last_subscribe, part_count, part_count_awaiting, error_part_num');
        $sel->where('parents', '=', system::url(2));
        $sel->orderBy('create_date', desc);


        $table = new uiTable($sel);
        $table->formatValues(true);
        $table->addColumn('name', lang::get('SUBSCRIBE_MSG_TT1'), 300);
        $table->addColumn('last_subscribe', lang::get('SUBSCRIBE_MSG_TT2'), 300, 0, 1, 'getState');

        $table->defaultRight('msg_upd');
        $table->addRight('msg_upd', 'edit', single);
        $table->addRight('msg_del', 'drop', multi);

        return $table->getHTML();
 	}

 	// форма добавления объекта
	public function add() {
		return $this->upd();
 	}

 	// форма редактирования объекта
	public function upd() {

        if (system::url(3) == 'view') {
            header('Content-Type: text/html; charset=utf-8');
            page::assign('hello', '');
            page::assign('user_name', user::get('name'));
       		echo page::parse(mailingProcess::getMailHTML(system::url(2)));
            system::stop();
        }

        // Указываем для какого объекта строить форму
        if (system::action() == "upd") {

			// Если это редактирование
			$obj = ormObjects::get(system::url(2));

			$parent_id = $obj->parent_id;
			$right = 'msg_proc_upd';


			if ($obj->error_part_num > 0) {

	        	$time = time() - strtotime($obj->last_subscribe);

	         	if ($time < 5) {
	         		ui::MessageBox(lang::get('SUBSCRIBE_MSG_MB_TITLE'), lang::get('SUBSCRIBE_MSG_MB_TEXT'));
	                system::redirect('/subscription/msg/'.$parent_id);
	         	}

	        }

		} else if (system::action() == "add") {

			// Если это добавление нового объекта
			$parent_id = system::url(2);
            $right = 'msg_proc_add';

			$obj = new ormObject();
            $obj->setClass('subscribe_msg');
            $obj->setParent($parent_id);

            $obj->publ_date = date('d.m.Y');
		}

        // Если произошли ошибки, перенаправляем на главную страницу модуля
		if ($obj->issetErrors())
			system::redirect('/subscription');

        // Устанавливаем кнопки для формы
        ui::setCancelButton('/subscription/msg/'.$parent_id);
		ui::newButton(lang::get('BTN_SAVE'), "javascript:sendForm('save');");
        ui::newButton(lang::get('BTN_APPLY'), "javascript:sendForm('apply');");

        // Создаем форму и выводим ее на страницу
        $form = new ormEditForm($obj, $right);

        // Формируем форму рассылки сообщений
        $send_form = '';
        if (system::action() == "upd" && user::issetRight('msg_send') && ($parent = ormObjects::get($parent_id))) {

            if (file_exists(MODUL_DIR.'/subscription/template/subscription.tpl')) {

                include(MODUL_DIR.'/subscription/template/subscription.tpl');

                $links = '';

                // Получаем количество частей в рассылке
		        $count = mailingProcess::getPartCount($parent->id);

		        if ($count > 0) {

                    $part_num = $obj->part_count - $obj->part_count_awaiting + 1;
                    if ($part_num > $obj->part_count) $part_num = 1;

			        page::assign('subject', $parent->subject);
			       // page::assign('count_part', ruNumbers::decl($count, lang::get('SUBSCRIBE_TS')));
			        page::assign('count_part', $count);
			        page::assign('count_part2', $count);
			        page::assign('part_num', $part_num);
		            page::assign('release_id', $obj->id);

		            if ($obj->error_part_num == 0) {
		            	page::assign('error_part_num', $part_num);
		            	page::fParse('sh1', 'block');
		            	page::fParse('sh2', 'none');
		       		} else {
		       			page::assign('error_part_num', $obj->error_part_num);
		       			ui::MessageBox(lang::get('SUBSCRIBE_TEXT_SEND', 9).$obj->error_part_num.lang::get('SUBSCRIBE_TEXT_SEND', 10), lang::get('SUBSCRIBE_TEXT_SEND', 11));
		       			page::fParse('sh2', 'block');
		            	page::fParse('sh1', 'none');
		    		}

			        $send_form = page::parse($TEMPLATE['frame']);

			        $links .= page::parse($TEMPLATE['send_link']);
		        }


                page::assignArray(lang::get('SUBSCRIBE_TEXT_SEND'));
                page::assign('url', system::au().'/subscription/msg_upd/'.$obj->id.'/view');
	            $links .= page::parse($TEMPLATE['view_link']);


                page::assign('list', $links);

                $form->addInBottomTabs('base', page::parse($TEMPLATE['frame_link']));
	        }
        }

        return $send_form.$form->getHTML();
 	}

 	// Обработчик рассылки сообщений
 	public function proc_send() {

        if (system::isAjax() && system::issetUrl(2)) {

        	if (system::url(3) == 'start') {

                echo mailingProcess::start(system::url(2), system::POST('subject'), system::POST('part'));

        	} else if (system::url(3) == 'stop') {

                mailingProcess::stop(system::url(2));

        	} else {

                mailingProcess::sendNextBlock(system::url(2), system::url(3));

        	}
        }

 		system::stop();
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

				$parent_id = $obj->parent_id;

			} else if (system::action() == "proc_add") {

	            // Говорим какой объект нужно создать
				$obj = new ormObject();
	            $obj->setClass('subscribe_msg');
                $obj->setParent(system::POST('obj_id'));

                $parent_id = system::POST('obj_id');
			}

	        // Если произошли ошибки, перенаправляем на главную страницу модуля
			if ($obj->issetErrors())
				system::redirect('/subscription');

	        // Присваиваем пришедшие значения полям в объекте
	        $obj->loadFromPost($mini_action);

	        // Сохраняем изменения
	        $obj_id = $obj->save();

	        // Если объект не сохранился, выводим пользователю текст ошибки.
	        if ($obj_id === false) {

	            system::savePostToSession();
		    	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), $obj->getErrorListText());
		    	ui::selectErrorFields($obj->getErrorFields());

	            $obj_id = (empty($_POST['obj_id'])) ? '' : $_POST['obj_id'];
		    	system::redirect('/subscription/msg_'.$mini_action.'/'.$obj_id);
		 	}

	        // Если данные изменились корректно перенаправляем на соответствующию страницу
			if ($_POST['parram'] == 'apply')
				system::redirect('/subscription/msg_upd/'.$obj_id);
			else
				system::redirect('/subscription/msg/'.$parent_id);


  	}

  	// удаление объекта
  	public function del() {

		if (system::issetUrl(2) && is_numeric(system::url(2))) {

            // Одиночное удаление
			if ($obj = ormObjects::get(system::url(2)))
				if ($obj->isInheritor('subscribe_msg'))
					$obj->toTrash();

			echo 'delete';

        } else if (isset($_POST['objects'])) {

        	// Множественное удаление
        	while(list($id, $val) = each($_POST['objects']))
                if ($obj = ormObjects::get($id))
                	if ($obj->isInheritor('subscribe_msg'))
                		$obj->toTrash();

            echo 'delete';
        }

        system::stop();

  	}

}

?>