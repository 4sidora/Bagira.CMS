<?php

class feedbackMacros {

    /**
	* @return HTML
	* @param string $templ_name - Шаблон оформления
	* @desc МАКРОС: Выводит форму обратной связи (добавления отзыва, вопрос-ответа и пр.)
	*/
    public function form($templ_name = 'default') {

        $templ_file = '/feedback/'.$templ_name.'.tpl';
        $TEMPLATE = page::getTemplate($templ_file);

	    if (!is_array($TEMPLATE))
	    	return page::errorNotFound('feedback.form', $templ_file);

        if (!user::isGuest()) {
            page::assign('feedback.name', user::get('surname') .' '. user::get('name'));
            page::assign('feedback.email', user::get('email'));
        }

     	page::assignSavingPost('feedback');

        // Парсим текст сообщения об ошибке
        page::parseError('feedback');

     	return page::parse($TEMPLATE['frame'], 1);
     }
    

    /**
	* @return HTML
    * @param int $form_id - ID формы обратной связи. Форма предварительно должна быть создана в настройках модуля "Обратная связь".
	* @param string $templ_name - Шаблон оформления
	* @desc МАКРОС: Автоватически генерирует форму обратной связи (добавления отзыва, вопрос-ответа и пр.)
	*/
    public function autoForm($form_id, $templ_name = 'default') {

    	if ($form_obj = ormObjects::get($form_id, 'feedback_form')) {

            $obj = new ormPage();
            $obj->setClass($form_obj->form_class);

            $form = new ormEditForm($obj, languages::pre().'/feedback/send_message');

            $form->tabuList('pseudo_url', 'h1', 'keywords', 'title', 'description',
                            'active', 'is_home_page', 'view_in_menu', 'view_submenu', 'in_search', 'answer',
                            'in_index', 'in_new_window', 'other_link', 'img_act', 'img_no_act', 'img_h1');

            page::assign('form_id', $form_obj->id);

            if ($form_obj->captcha)
                $form->showCaptcha();

            // Парсим текст сообщения
            page::parseError('feedback');

            return $form->getHTML('feedback/'.$templ_name);

   		}
    }

}

?>