<?php

class __comment {

    function upd(){

        // Устанавливаем кнопки для формы
        ui::setCancelButton('/comments/list');
        ui::newButton(lang::get('BTN_SAVE'), "javascript:sendForm('save');");
	    ui::newButton(lang::get('BTN_APPLY'), "javascript:sendForm('apply');");

        if (file_exists(MODUL_DIR.'/comments/template/comment.tpl'))
        	include(MODUL_DIR.'/comments/template/comment.tpl');

        if (!$obj = comments::get(system::url(2)))
            system::redirect('/comments/list');



        if ($obj->getUserId() != 0 && user::issetRight('user_upd', 'users')) {
            $url = system::au().'/users/user_upd/'.$obj->getUserId();
            page::assign('user_link', $url);
            page::fParse('user_name', $TEMPLATE['user_link']);
        } else page::fParse('user_name', $TEMPLATE['user_name']);

        page::assign('obj.id', $obj->id());
        page::assign('obj.username', $obj->getUserName());
        page::assign('obj.email', $obj->getEmail());
        page::assign('obj.text', $obj->getText());
        page::assign('obj.date', date('d.m.Y H:i', strtotime($obj->getPublDate())));
        page::assign('obj.parram', $obj->getParram());

        ui::CheckBox('active', 1, $obj->isActive(), 'Проверен');

        // Информация о странице
        if ($page = ormPages::get($obj->getObjId())) {
            page::assign('page.id', $page->id);
            page::assign('page.url', $page->url);
            page::assign('page.name', $page->name);
        }


        return page::parse($TEMPLATE['frame']);
    }

    function proc_upd(){

        if (!$obj = comments::get(system::POST('obj_id')))
            system::redirect('/comments/list');

        $obj->setActive(system::POST('active'));
        $obj->setText(system::POST('c_text'));
        $obj->setParram(system::POST('c_parram'));

        $obj_id = $obj->save();

        // Если объект не сохранился, выводим пользователю текст ошибки.
        if ($obj_id === false) {
	    	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), $obj->getErrorListText());
	    	ui::selectErrorFields($obj->getErrorFields());
            system::redirect('/comments/comment_upd/'.system::POST('obj_id'));
	 	}

        // Если данные изменились корректно перенаправляем на соответствующию страницу
		if ($_POST['parram'] == 'apply')
			system::redirect('/comments/comment_upd/'.$obj_id);
        else
        	system::redirect('/comments/list');

    }

    // удаление объекта
  	public function del() {

        if (system::issetUrl(2) && is_numeric(system::url(2))) {

            // Одиночное удаление
            if ($obj = comments::get(system::url(2))){
				$obj->delete();
				echo 'delete';
            }

        } else if (isset($_POST['objects'])) {

        	// Множественное удаление
        	while(list($id, $val) = each($_POST['objects'])) {

        		if ($obj = comments::get($val))
					$obj->delete();
		
        	}
            echo 'delete';
        }

        system::stop();
  	}

    // активность объекта
  	public function act() {

        if (system::issetUrl(2) && is_numeric(system::url(2))) {

            // Одиночное удаление
            if ($obj = comments::get(system::url(2))){
				$obj->setActive(!$obj->isActive());
                $obj->save();
				echo (!$obj->isActive()) ? 'active' : 'no_active';
            }

        } else if (isset($_POST['objects'])) {

        	// Множественное удаление
        	while(list($id, $val) = each($_POST['objects'])) {

        		if ($obj = comments::get($val)) {
					$obj->setActive(!$obj->isActive());
                    $obj->save();
                }
        	}
            echo 'invert';
        }

        system::stop();
  	}


}

?>