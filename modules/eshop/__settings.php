<?php

class __settings {

	public function defAction() {

        if (file_exists(MODUL_DIR.'/eshop/template/settings.tpl'))
        	include(MODUL_DIR.'/eshop/template/settings.tpl');

        ui::newButton(lang::get('BTN_SAVE_ONLY'), "javascript:sendForm('save');");

        page::assignArray(lang::get('ESHOP_TEXT_SETTINGS'));


        ui::SelectBox('fisrt_state', ormObjects::getObjectsByClass('eshop_status'), reg::getKey('/eshop/fisrt_state'));
        ui::CheckBox('only_reg', 1, reg::getKey('/eshop/only_reg'), lang::get('ESHOP_TEXT_SETTINGS', 1));
        ui::CheckBox('check_count', 1, reg::getKey('/eshop/check_count'), lang::get('ESHOP_TEXT_SETTINGS', 2));
        ui::CheckBox('dubl_to_email', 1, reg::getKey('/eshop/dubl_to_email'), lang::get('ESHOP_TEXT_SETTINGS', 3));

        //page::assign('nds', reg::getKey('/eshop/nds'));

        page::assign('min_count', reg::getKey('/eshop/min_count'));


		return page::parse($TEMPLATE['frame']);
 	}

    public function proc() {

        reg::setKey('/eshop/only_reg', system::POST('only_reg', isBool));
        reg::setKey('/eshop/check_count', system::POST('check_count', isBool));
        reg::setKey('/eshop/dubl_to_email', system::POST('dubl_to_email', isBool));
        reg::setKey('/eshop/fisrt_state', system::POST('fisrt_state', isInt));


        $min_count = system::POST('min_count', isInt);
        if (empty($min_count)) $min_count = '0';
        reg::setKey('/eshop/min_count', $min_count);

        reg::clearCache();

        ui::MessageBox(lang::get('CONFIG_SAVE_OK'), lang::get('CONFIG_SAVE_OK_MSG'));
		system::redirect('/eshop/settings');
    }


}

?>