<?php

class __message {

    public function __construct() {
    	ui::checkClasses('feedback', 'feedback_form');
    }

	public function upd() {

 		if (!$obj = ormPages::get(system::url(2)))
        	system::redirect('/feedback');

        ui::setCancelButton('/feedback');
		ui::newButton(lang::get('BTN_SAVE'), "javascript:sendForm('save');");
	    ui::newButton(lang::get('BTN_APPLY'), "javascript:sendForm('apply');");

        // Создаем форму и выводим ее на страницу
        $form = new ormEditForm($obj, 'message_proc_upd');
        $form->withoutSH();
        $form->withoutTabs();
        $form->tabuList('pseudo_url', 'h1', 'keywords', 'title', 'description',
	            'active', 'is_home_page', 'view_in_menu', 'view_submenu', 'in_search',
	            'in_index', 'in_new_window', 'other_link', 'img_act', 'img_no_act', 'img_h1');

        if (!$obj->active)
        	$form->addField('base', 10, 'publ', '', ui::CheckBox('publ', 1, 0, 'Опубликовать на сайте'));

        if (!$obj->send_answer_to_user)
        	$form->addField('base', 11, 'send_to_email', '', ui::CheckBox('send_to_email', 1, 0, 'Отправить ответ на почту'));

        return $form->getHTML();
 	}

  	public function proc_upd() {

		$obj = ormPages::get(system::POST('obj_id'));

		$obj->tabuList('pseudo_url', 'h1', 'keywords', 'title', 'description',
	            'active', 'is_home_page', 'view_in_menu', 'view_submenu', 'in_search',
	            'in_index', 'in_new_window', 'other_link', 'img_act', 'img_no_act', 'img_h1');

        $obj->loadFromPost();

        // Публикация на сайте
        if (system::POST('publ', isBool))
        	if ($obj->isInheritor('faq') && $obj->newVal('answer') == '') {
            	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), lang::get('FEEDBACK_MSG_3'));
	            ui::selectErrorFields(array('select' => '', 'focus' => 'answer'));
        	} else $obj->active = 1;

        $obj_id = $obj->save();

        // Если объект не сохранился, выводим пользователю текст ошибки.
        if ($obj_id === false) {
            system::savePostToSession();
	    	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), $obj->getErrorListText());
	    	ui::selectErrorFields($obj->getErrorFields());
	    	system::redirect('/feedback/message_upd/'.$_POST['obj_id']);
	 	}

	 	if (system::POST('send_to_email', isBool) && !$obj->send_answer_to_user && $form_obj = ormObjects::get($obj->form_id)) {

            if ($form_obj->send_answer) {

               	if ($obj->answer != '') {

	               	$fields = $obj->getClass()->loadFields();
			        while(list($num, $field) = each($fields))
			            if (!empty($field['f_sname']))
			            page::assign($field['f_sname'], $obj->__get($field['f_sname']));

                    page::assign('site_name', domains::curDomain()->getSiteName());
        			page::assign('base_email', domains::curDomain()->getEmail());

		            $mail = new phpmailer();
		            $mail->From = $this->parse($form_obj->answer_sender_address);
		            $mail->FromName = $this->parse($form_obj->answer_sender_name);

		            $mail->AddAddress($obj->email);
		            $mail->WordWrap = 50;
		            $mail->IsHTML(true);

		            $mail->Subject = $this->parse($form_obj->answer_subject);
		            $mail->Body = $this->parse($form_obj->answer_template);

		            $mail->Send();

		            // Помечаем, что ответ отправлен
                    $obj->send_answer_to_user = 1;
   					$obj->save();

		            ui::MessageBox(lang::get('FEEDBACK_MSG_1'), '');

	            } else {

	            	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), lang::get('FEEDBACK_MSG_2'));
	            	ui::selectErrorFields(array('select' => '', 'focus' => 'answer'));
	          	}
            }
	 	}

        // Если данные изменились корректно перенаправляем на соответствующию страницу
		if ($_POST['parram'] == 'apply')
			system::redirect('/feedback/message_upd/'.$obj_id);
        else
        	system::redirect('/feedback');

  	}

  	private function parse($val) {
 		return page::parse(str_replace(array('{', '}'), '%', $val));
 	}

  	public function act() {

        if (system::issetUrl(2) && is_numeric(system::url(2))) {

	    	$obj = ormPages::get(system::url(2));

	    	if ($obj instanceof ormPage){
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

        			$obj = ormPages::get($id);

			    	if ($obj instanceof ormPage){
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

  	public function del() {

        if (system::issetUrl(2) && is_numeric(system::url(2))) {

            // Одиночное удаление
	    	if ($obj = ormPages::get(system::url(2))){
				$obj->toTrash();
				echo 'delete';
            }

        } else if (isset($_POST['objects'])) {

        	// Множественное удаление
        	while(list($id, $val) = each($_POST['objects'])) {
        		if ($obj = ormPages::get($id))
					$obj->toTrash();
        	}
            echo 'delete';
        }

        system::stop();
  	}

}

?>