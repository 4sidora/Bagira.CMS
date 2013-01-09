<?php

class __settings {

	public function defAction() {

        if (file_exists(MODUL_DIR.'/comments/template/settings.tpl'))
        	include(MODUL_DIR.'/comments/template/settings.tpl');

        ui::newButton(lang::get('BTN_SAVE_ONLY'), "javascript:sendForm('save');");

        page::assignArray(lang::get('ESHOP_TEXT_SETTINGS'));

        ui::CheckBox('only_reg', 1, reg::getKey('/comments/only_reg'), lang::get('COMMENT_ONLY_REG'));
        ui::CheckBox('com_moderation', 1, reg::getKey('/comments/com_moderation'), lang::get('COMMENT_COM_MODERATION'));

        page::assign('text_length', reg::getKey('/comments/text_length'));
        
		return page::parse($TEMPLATE['frame']);
 	}

    public function proc() {

        reg::setKey('/comments/only_reg', system::POST('only_reg', isBool));
        reg::setKey('/comments/com_moderation', system::POST('com_moderation', isBool));
        reg::setKey('/comments/text_length', system::POST('text_length', isInt));
   

        ui::MessageBox(lang::get('CONFIG_SAVE_OK'), lang::get('CONFIG_SAVE_OK_MSG'));
		system::redirect('/comments/settings');
    }


}

?>