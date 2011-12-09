<?php

class __form {

    public function __construct() {
    	ui::checkClasses('feedback', 'feedback_form');
    }

	public function add() {
		return $this->upd();
 	}

	public function upd() {

        // Указываем для какого объекта строить форму
        if (system::action() == "upd") {

			if (system::url(2) == 0)
				system::redirect('/structure/settings');

            $obj = ormObjects::get(system::url(2));

            $class_list = '';
			$right = 'form_proc_upd';

		} else if (system::action() == "add") {

			$obj = new ormObject();
            $obj->setClass('feedback_form');

            $obj->admin_sender_name = '{name}';
            $obj->admin_sender_address = '{email}';
            $obj->admin_subject = 'Сообщение с сайта {site_name}';
            $obj->admin_template = '
<p>
	 Сегодня прекрасный день!
</p>
<p>
	 Посетитель сайта {site_name}, воспользовавшись формой обратной связи, отправил следующее сообщение:
</p>
<p>
	 <i>{content}</i>
</p>
<p>
	 ФИО посетителя: {name} <br>
	 E-mail для связи: {email}
</p>
<p>
	 С уважением, Bagira.CMS<br>
</p>
            ';


            $obj->notice_sender_name = '{site_name}';
            $obj->notice_sender_address = '{base_email}';
            $obj->notice_subject = 'Уведомление с сайта {site_name}';
            $obj->notice_template = '
<p>
	 Добрый день, {name}.
</p>
<p>
	 Это письмо - автоматическое уведомление о том, что отправленное вами сообщение принято
	 к рассмотрению. Мы постараемся ответить вам в кратчайшие сроки.
</p>
<p>
	 Благодарим за обращение,<br>
	 Администрация сайта {site_name}.<br>
</p>

            ';


            $obj->answer_sender_name = '{site_name}';
            $obj->answer_sender_address = '{base_email}';
            $obj->answer_subject = 'Ответ на ваше сообщение с сайта {site_name}';

            $obj->answer_template = '
<p>
	 Добрый день, {name}.
</p>
<p>
	 Не так давно, воспользовавшись формой обратной связи, вы отправили нам следующее сообщение:
</p>
<p>
	 <i>{content}</i>
</p>
<p>
	 Наш ответ:
</p>
<p>
	 {answer}
</p>
<p>
	 С уважением,<br>
	 Администрация сайта {site_name}.<br>
</p>
            ';


            $right = 'form_proc_add';
		}

		page::assign('right', $right);

		if (!is_a($obj, 'ormObject') || $obj->issetErrors())
			system::redirect('/feedback/settings');




        ui::setCancelButton('/feedback/settings');
		ui::newButton(lang::get('BTN_SAVE'), "javascript:sendForm('save');");
	    ui::newButton(lang::get('BTN_APPLY'), "javascript:sendForm('apply');");


        // Создаем форму и выводим ее на страницу
        $form = new ormEditForm($obj, $right);


        $list = array();
		if ($class = ormClasses::get('feedback')) {
	        $mas = $class->getAllInheritors();
	        while(list($id, $sname) = each($mas)){
		    	$h = ormClasses::get($sname);
		        $list[] = array(
		        	'id' => $id,
		        	'name' => $h->getName().' ('.$sname.')'
		        );
		    }
        }
        $form->replaceField('form_class', ui::SelectBox('form_class', $list, $obj->form_class, 400));

        return $form->getHTML();
 	}

  	public function proc_add() {
		$this->proc_upd();
  	}

  	public function proc_upd() {

        $mini_action = substr(system::action(), -3);

        if (system::action() == "proc_upd") {

            // Говорим какой объект нужно изменить
			$obj = new ormObject(system::POST('obj_id'));

		} else if (system::action() == "proc_add") {

            // Говорим какой объект нужно создать
			$obj = new ormObject();
            $obj->setClass('feedback_form');

		}

		if ($obj->issetErrors())
			system::redirect('/feedback/settings');

        $obj->loadFromPost($mini_action);
        $obj_id = $obj->save();

        // Если объект не сохранился, выводим пользователю текст ошибки.
        if ($obj_id === false) {

            system::savePostToSession();
	    	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), $obj->getErrorListText());
	    	ui::selectErrorFields($obj->getErrorFields());

            $class = ($mini_action == 'add') ? '/'.system::POST('class_id') : '';
	    	system::redirect('/feedback/form_'.$mini_action.'/'.$_POST['obj_id']);
	 	}

        // Если данные изменились корректно перенаправляем на соответствующию страницу
		if ($_POST['parram'] == 'apply')
			system::redirect('/feedback/form_upd/'.$obj_id);
        else
        	system::redirect('/feedback/settings');

  	}

  	public function del() {

        if (system::issetUrl(2) && is_numeric(system::url(2))) {

            // Одиночное удаление
	    	if ($obj = ormObjects::get(system::url(2))){
				$obj->toTrash();
				echo 'delete';
            }

        } else if (isset($_POST['objects'])) {

        	// Множественное удаление
        	while(list($id, $val) = each($_POST['objects'])) {
        		if ($obj = ormObjects::get($id))
					$obj->toTrash();
        	}
            echo 'delete';
        }

        system::stop();
  	}
}

?>