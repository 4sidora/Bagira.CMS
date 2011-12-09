<?php

class __settings {

	public function defAction() {

        if (file_exists(MODUL_DIR.'/subscription/template/settings.tpl'))
        	include(MODUL_DIR.'/subscription/template/settings.tpl');

        ui::newButton(lang::get('BTN_SAVE_ONLY'), "javascript:sendForm('save');");

        page::assignArray(lang::get('SUBSCRIBE_TEXT_SETTINGS'));

        page::assign('count_mails_day', reg::getKey('/subscription/count_mails_day'));
        page::assign('count_mails', reg::getKey('/subscription/count_mails'));


		return page::parse($TEMPLATE['frame']);
 	}

    public function proc() {

        reg::setKey('/subscription/count_mails', system::POST('count_mails', isInt));
        reg::setKey('/subscription/count_mails_day', system::POST('count_mails_day', isInt));

        reg::clearCache();

        ui::MessageBox(lang::get('CONFIG_SAVE_OK'), lang::get('CONFIG_SAVE_OK_MSG'));
		system::redirect('/subscription/settings');
    }


}

?>