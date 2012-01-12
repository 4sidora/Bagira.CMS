<?php

class __profile {

 	// форма редактирования объекта
	public function defAction() {

		$obj = user::getObject();

        // Если произошли ошибки, перенаправляем на главную страницу админки
		if (!($obj instanceof ormObject))
			system::redirect('/');

        // Устанавливаем кнопки для формы
		ui::newButton(lang::get('BTN_SAVE_CHANGE'), "javascript:sendForm('save');");

        // Создаем форму и выводим ее на страницу
        $form = new ormEditForm($obj, 'profile_proc');

        $modules = user::getModulesForObject($obj);
        $form->replaceField('def_modul', ui::SelectBox('def_modul', $modules, $obj->def_modul, 400));

        // Зануляем не нужные поля
        $form->replaceField('active', '');
        $form->replaceField('groups', '');
        $form->replaceField('login', '');

        return $form->getHTML();
 	}

 	// обработчик изменения объекта
  	public function proc() {

        $obj = user::getObject();

        // Если произошли ошибки, перенаправляем на главную страницу админки
		if (!($obj instanceof ormObject))
			system::redirect('/');

        // Присваиваем пришедшие значения полям в объекте
        $obj->tabuList('active', 'groups', 'login');
        $obj->loadFromPost();

        // Сохраняем изменения
        $obj_id = $obj->save();

        // Если объект не сохранился, выводим пользователю текст ошибки.
        if ($obj_id === false) {
            system::savePostToSession();
	    	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), $obj->getErrorListText());
	    	ui::selectErrorFields($obj->getErrorFields());
	 	}

        cache::delete('user'.$obj_id);

        system::redirect('/core/profile');

  	}

}

?>