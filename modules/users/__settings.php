<?php

class __settings {

	public function defAction() {

        if (file_exists(MODUL_DIR.'/users/template/settings.tpl'))
        	include(MODUL_DIR.'/users/template/settings.tpl');

        ui::newButton(lang::get('BTN_SAVE_ONLY'), "javascript:sendForm('save');");

        page::assignArray(lang::get('USERS_FORM_FIELD'));

        page::assign('errorCountBlock', reg::getKey('/users/errorCountBlock'));
       // page::assign('errorCountCapcha', reg::getKey('/users/errorCountCapcha'));
		ui::CheckBox('gzip', 1, reg::getKey('/core/gzip'), lang::get('USERS_FORM_FIELD', 9));
		ui::CheckBox('reg', 1, reg::getKey('/users/reg'), lang::get('USERS_FORM_FIELD', 11));
		ui::CheckBox('activation', 1, reg::getKey('/users/activation'), lang::get('USERS_FORM_FIELD', 28));
        ui::CheckBox('confirm', 1, reg::getKey('/users/confirm'), lang::get('USERS_FORM_FIELD', 35));

        ui::CheckBox('ask_email', 1, reg::getKey('/users/ask_email'), lang::get('USERS_FORM_FIELD', 9));

        //авторизация чере соц. сети
        ui::CheckBox('twitter_bool', 1, reg::getKey('/users/twitter_bool'), lang::get('USERS_FORM_FIELD', 40));
        page::assign('twitter_id', reg::getKey('/users/twitter_id'));
        page::assign('twitter_secret', reg::getKey('/users/twitter_secret'));

        ui::CheckBox('vk_bool', 1, reg::getKey('/users/vk_bool'), lang::get('USERS_FORM_FIELD', 42));
		page::assign('vk_id', reg::getKey('/users/vk_id'));
        page::assign('vk_secret', reg::getKey('/users/vk_secret'));

        ui::CheckBox('facebook_bool', 1, reg::getKey('/users/facebook_bool'), lang::get('USERS_FORM_FIELD', 41));
		page::assign('facebook_id', reg::getKey('/users/facebook_id'));
        page::assign('facebook_secret', reg::getKey('/users/facebook_secret'));

		ui::CheckBox('yandex_bool', 1, reg::getKey('/users/yandex_bool'), lang::get('USERS_FORM_FIELD', 43));
		ui::CheckBox('google_bool', 1, reg::getKey('/users/google_bool'), lang::get('USERS_FORM_FIELD', 44));

		return page::parse($TEMPLATE['frame']);
 	}

  	public function proc() {

        //reg::setKey('/users/errorCountCapcha', system::POST('errorCountCapcha'));
        reg::setKey('/users/errorCountBlock', system::POST('errorCountBlock'));
        reg::setKey('/users/reg', system::POST('reg', isBool));
        reg::setKey('/users/activation', system::POST('activation', isBool));
        reg::setKey('/users/confirm', system::POST('confirm', isBool));
        reg::setKey('/users/ask_email', system::POST('ask_email', isBool));


        //авторизация чере соц. сети
        reg::setKey('/users/twitter_bool', system::POST('twitter_bool'), isBool);
        reg::setKey('/users/twitter_id', system::POST('twitter_id'), isString);
        reg::setKey('/users/twitter_secret', system::POST('twitter_secret'), isString);

        reg::setKey('/users/vk_bool', system::POST('vk_bool'), isBool);
		reg::setKey('/users/vk_id', system::POST('vk_id'), isString);
        reg::setKey('/users/vk_secret', system::POST('vk_secret'), isString);

        reg::setKey('/users/facebook_bool', system::POST('facebook_bool'), isBool);
		reg::setKey('/users/facebook_id', system::POST('facebook_id'), isString);
        reg::setKey('/users/facebook_secret', system::POST('facebook_secret'), isString);

		reg::setKey('/users/yandex_bool', system::POST('yandex_bool'), isBool);
		reg::setKey('/users/google_bool', system::POST('google_bool'), isBool);


        ui::MessageBox(lang::get('CONFIG_SAVE_OK'), lang::get('CONFIG_SAVE_OK_MSG'));

        reg::clearCache();
        system::log(lang::get('CONFIG_LOG_SAVE'), warning);
		system::redirect('/users/settings');
  	}
}

?>