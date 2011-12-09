<?php

class __change {

	public function defAction() {

        if (file_exists(MODUL_DIR.'/core/template/change.tpl'))
        	include(MODUL_DIR.'/core/template/change.tpl');

        // Выводим (если нужно) форму изменения свойств домена (AJAX)
        $this->changeDomainInfo($TEMPLATE);

        ui::newButton(lang::get('BTN_SAVE_ONLY'), "javascript:saveConfig();");

        page::assignArray(lang::get('CONFIG_FORM_FIELD'));

        page::assign('errorCountBlock', reg::getKey('/users/errorCountBlock'));
       // page::assign('errorCountCapcha', reg::getKey('/users/errorCountCapcha'));
		ui::CheckBox('gzip', 1, reg::getKey('/core/gzip'), lang::get('CONFIG_FORM_FIELD', 9));
		ui::CheckBox('reg', 1, reg::getKey('/users/reg'), lang::get('CONFIG_FORM_FIELD', 11));
		ui::CheckBox('activation', 1, reg::getKey('/users/activation'), lang::get('CONFIG_FORM_FIELD', 28));
        ui::CheckBox('confirm', 1, reg::getKey('/users/confirm'), lang::get('CONFIG_FORM_FIELD', 35));
               
        ui::CheckBox('delToTrash', 1, reg::getKey('/core/delToTrash'), lang::get('CONFIG_FORM_FIELD', 12));
        ui::CheckBox('noIE6', 1, reg::getKey('/core/noIE6'), lang::get('CONFIG_FORM_FIELD', 34));

        ui::loadFile('watermark', reg::getKey('/core/watermark'));

        ui::CheckBox('scaleBigJpeg', 1, reg::getKey('/core/scaleBigJpeg'), lang::get('CONFIG_FORM_FIELD', 33));
        page::assign('sizeBigJpeg', reg::getKey('/core/sizeBigJpeg'));

         // Форма редактирования языков
        $form = new uiMultiForm('langs');
        $form->insideForm();
        $form->setData(languages::getAll());
     	$form->addColumn('l_name', lang::get('CONFIG_FORM_FIELD', 13), 255);
        $form->addColumn('l_prefix', lang::get('CONFIG_FORM_FIELD', 14), 120, lang::get('CONFIG_FORM_FIELD', 15));
        page::assign('langs', $form->getHTML());



        // Форма редактирования доменов
        $form = new uiMultiForm('domains');
        $form->insideForm();
        $form->setData(domains::getAll());
        $form->addColumn('d_name', lang::get('CONFIG_FORM_FIELD', 18), 150, lang::get('CONFIG_FORM_FIELD', 23));
        $form->addColumn('d_def_lang', lang::get('CONFIG_FORM_FIELD', 19), 140, lang::get('CONFIG_FORM_FIELD', 24), 0, 'getLangList');
        $form->addColumn('d_online', lang::get('CONFIG_FORM_FIELD', 20), 40, lang::get('CONFIG_FORM_FIELD', 25), 0, 'getCheckBox');
        $form->addColumn('d_sitename', lang::get('CONFIG_FORM_FIELD', 4), 250, lang::get('CONFIG_FORM_FIELD', 16));
     	$form->addColumn('d_email', lang::get('CONFIG_FORM_FIELD', 5), 250, lang::get('CONFIG_FORM_FIELD', 17));
     	$form->addColumn('d_id', '&nbsp;', 25, '', 0, 'getEditButt');

        function getLangList($val, $obj) {
        	return ui::SelectBox('objdomains['.$obj['id'].'][d_def_lang]', languages::getAll(), $val, 130);
        }

        function getCheckBox($val, $obj) {
        	return '<div align="center">'.ui::CheckBox('objdomains['.$obj['id'].'][d_online]', 1, $val).'</div>';
        }

        function getEditButt($val, $obj) {
        	if (empty($val))
        		return '';
        	else
        		return '<div name="'.$val.'" class="header_tree" style="float:left;margin-left:15px;">
        			<font class="compose_image"></font></div>';
        }

        page::assign('domains', $form->getHTML());


		return page::parse($TEMPLATE['frame']);
 	}

  	public function proc() {

        reg::setKey('/core/gzip', system::POST('gzip', isBool));
        reg::setKey('/core/delToTrash', system::POST('delToTrash', isBool));
        reg::setKey('/core/scaleBigJpeg', system::POST('scaleBigJpeg', isBool));
        reg::setKey('/core/sizeBigJpeg', system::POST('sizeBigJpeg', isInt));
        reg::setKey('/core/noIE6', system::POST('noIE6', isBool));

        //reg::setKey('/users/errorCountCapcha', system::POST('errorCountCapcha'));
        reg::setKey('/users/errorCountBlock', system::POST('errorCountBlock'));
        reg::setKey('/users/reg', system::POST('reg', isBool));
        reg::setKey('/users/activation', system::POST('activation', isBool));
        reg::setKey('/users/confirm', system::POST('confirm', isBool));


        // Загрузка ватермарка
        if (isset($_FILES['file_watermark']) && !empty($_FILES['file_watermark']['name']))
        	if (system::fileExtIs($_FILES['file_watermark']['name'], array('png')))
	        	$watermark = system::copyFile(
	        		$_FILES['file_watermark']['tmp_name'],
	        		$_FILES['file_watermark']['name'],
	        		'/upload/image'
	        	);
	      	else
	      		ui::MessageBox(lang::get('CONFIG_SAVE_ERROR'), lang::get('CONFIG_WATERMARK_ERROR'));

        if (!isset($watermark) && isset($_POST['watermark']))
        	if (system::fileExtIs($_POST['watermark'], array('png')) || empty($_POST['watermark']))
        		$watermark = system::checkVar($_POST['watermark'], isString);
        	else
                ui::MessageBox(lang::get('CONFIG_SAVE_ERROR'), lang::get('CONFIG_WATERMARK_ERROR').'123');

        if (isset($watermark) && $watermark != reg::getKey('/core/watermark')) {
        	reg::setKey('/core/watermark', $watermark);
            $this->deleteCacheWatermark($watermark);
        }



        // ******************	Сохранение доменов 	***************************
        function changeDomain($id, $data){

            $online = (isset($data['d_online'])) ? $data['d_online'] : 0;
            $isAdd = (empty($id)) ? true : false;

            $obj = new domain($id);
            $obj->setName($data['d_name']);
            $obj->setOnline($online);
            $obj->setDefLang($data['d_def_lang']);
            $obj->setEmail($data['d_email']);
            $obj->setSiteName($data['d_sitename']);
            $id = $obj->save();

            if ($id === false)
		    	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), $obj->getErrorListText());
		    else if ($isAdd)
            	ui::MessageBox(lang::get('CONFIG_DOMAIN_ADD'), lang::get('CONFIG_DOMAIN_TEXT'));


			return true;
        }

        function delDomain($id){
            $obj = new domain($id);

            if ($obj->delete() === false)
		    	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), $obj->getErrorListText());
        }

        $form = new uiMultiForm('domains');
        $form->process('changeDomain', 'delDomain');


        // ******************	Сохранение языков 	***************************
        function changeLang($id, $data){

            $isAdd = (empty($id)) ? true : false;

            $obj = new language($id);
            $obj->setName($data['l_name']);
            $obj->setPrefix($data['l_prefix']);
            $id = $obj->save();

            if ($id === false)
		    	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), $obj->getErrorListText());
            else if ($isAdd)
            	ui::MessageBox(lang::get('CONFIG_LANG_ADD'), lang::get('CONFIG_LANG_TEXT'));

			return true;
        }

        function delLang($id){
        	$obj = new language($id);

            if ($obj->delete() === false)
		    	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), $obj->getErrorListText());
        }

        $form = new uiMultiForm('langs');
        $form->process('changeLang', 'delLang');

        // Если в системе только один язык, сохраняем его данные в реестр для быстрой подгрузки
        if (count(languages::getAll(true)) == 1){
        	reg::setKey('/core/cur_lang/id', languages::get(1, true)->id());
        	reg::setKey('/core/cur_lang/prefix', languages::get(1, true)->getPrefix());
        	reg::setKey('/core/cur_lang/name', languages::get(1, true)->getName());
        } else reg::delKey('/core/cur_lang');

        // Если в системе только один домен, сохраняем его данные в реестр для быстрой подгрузки
        if (count(domains::getAll(true)) == 1) {
        	reg::setKey('/core/cur_domain/id', domains::get(1, true)->id());
        	reg::setKey('/core/cur_domain/name', domains::get(1, true)->getName());
        	reg::setKey('/core/cur_domain/def_lang', domains::get(1, true)->getDefLang());
        	reg::setKey('/core/cur_domain/sitename', domains::get(1, true)->getSiteName());
        	reg::setKey('/core/cur_domain/email', domains::get(1, true)->getEmail());
        	reg::setKey('/core/cur_domain/online', domains::get(1, true)->online());
        	reg::setKey('/core/cur_domain/offline_msg', domains::get(1, true)->getOfflineMsg());
        	reg::setKey('/core/cur_domain/error_msg', domains::get(1, true)->getErrorMsg());
        } else reg::delKey('/core/cur_domain');

        ui::MessageBox(lang::get('CONFIG_SAVE_OK'), lang::get('CONFIG_SAVE_OK_MSG'));


        reg::clearCache();

        system::log(lang::get('CONFIG_LOG_SAVE'), warning);
		system::redirect('/core/change');
  	}


    //
  	private function deleteCacheWatermark() {

        $from_path = '/cache/img/';
		chdir(ROOT_DIR.$from_path);
		$handle = opendir('.');

		while (($file = readdir($handle)) !== false) {
			if ($file != "." && $file != ".." && is_dir(ROOT_DIR.$from_path.$file)) {
    			$num = substr($file, strlen($file) - 1, strlen($file));
        		if (is_numeric($num) && $num > 0)
					system::deleteDir(ROOT_DIR.$from_path.$file);
			}
		}

		closedir($handle);
	}


    // Выводим форму изменения свойств домена (AJAX)
  	private function changeDomainInfo($TEMPLATE) {

        if (system::issetUrl(2) && system::url(2) == 'edit'){

            if (isset($_POST['error_msg']) && isset($_POST['offline_msg'])) {

                // Обработчик изменения свойств домена
            	$domain = new domain(system::url(3));
            	$domain->setErrorMsg($_POST['error_msg']);
            	$domain->setOfflineMsg($_POST['offline_msg']);
                $domain->save();

                $form = new uiMultiForm('mirrors');

            	function changeMirror($id, $data, $form_name, $domain){
					return $domain->changeMirror($data['d_name'], $id);
		        }

		        function delMirror($id, $form_name, $domain){
		            $domain->delMirror($id);
		        }

		        $form->process('changeMirror', 'delMirror', $domain);

            } else {

                // Форма редактирования домена
	            $domain = new domain(system::url(3));

	            page::assign('error_msg', $domain->getErrorMsg());
	            page::assign('offline_msg', $domain->getOfflineMsg());

	            $form = new uiMultiForm('mirrors');
		        $form->insideForm();
		        $form->setData($domain->getMirrors());
		     	$form->addColumn('d_name', lang::get('CONFIG_FORM_FIELD', 31), 260);
		        page::assign('mirrors_list', $form->getHTML());

	            echo page::parse($TEMPLATE['domain_frame']);
            }

        	die;
        }
  	}


}

?>