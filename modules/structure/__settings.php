<?php

class __settings {

    public function __construct() {
    	ui::checkClasses('section', 'page');

    	ui::setNewLink(lang::get('STRUCTURE_SETTINGS'), 'settings', 'tabs-main');
        ui::setNewLink(lang::get('STRUCTURE_FORM_FIELD', 11), 'settings', 'tabs-page_tpl');
        ui::setNewLink(lang::get('STRUCTURE_FORM_FIELD', 14), 'settings', 'tabs-obj_tpl');
        ui::setNewLink(lang::get('STRUCTURE_FORM_FIELD', 15), 'settings', 'tabs-view');
    	ui::setNewLink(lang::get('STRUCTURE_MINITEXT'), 'minitext');
    }

	public function defAction() {

        if (file_exists(MODUL_DIR.'/structure/template/settings.tpl'))
        	include(MODUL_DIR.'/structure/template/settings.tpl');

      //  ui::newButton(lang::get('BTN_SAVE_ONLY'), "javascript:sendForm('save');");
        ui::newButton(lang::get('BTN_SAVE'), "javascript:sendForm('save');");
	    ui::newButton(lang::get('BTN_APPLY'), "javascript:sendForm('apply');");

        if (isset($_SESSION['STRUCTURE_LIST_FLAG']) && $_SESSION['STRUCTURE_LIST_FLAG'])
        	ui::setCancelButton('/structure/list');
        else
        	ui::setCancelButton('/structure/tree');

		// Основные настройки
        page::assignArray(lang::get('STRUCTURE_FORM_FIELD'));

        page::assign('title_prefix', reg::getKey(ormPages::getPrefix().'/title_prefix'));
        page::assign('keywords', reg::getKey(ormPages::getPrefix().'/keywords'));
        page::assign('description', reg::getKey(ormPages::getPrefix().'/description'));

        ui::CheckBox('cur_date', 1, reg::getKey('/structure/'.domains::curId().'/cur_date'), lang::get('STRUCTURE_FORM_FIELD', 4));
        ui::CheckBox('no_view_no_edit', 1, reg::getKey(ormPages::getPrefix().'/no_view_no_edit'), lang::get('STRUCTURE_FORM_FIELD', 7));
        ui::CheckBox('view_as_tree', 1, reg::getKey(ormPages::getPrefix().'/view_as_tree'), lang::get('STRUCTURE_FORM_FIELD', 18));
        ui::CheckBox('auto_index', 1, reg::getKey(ormPages::getPrefix('search').'/auto_index'), lang::get('STRUCTURE_FORM_FIELD', 19));

        $file = ROOT_DIR.'/robots_part_'.domains::curDomain()->getName().'.txt';
        $robots = (file_exists($file)) ? file_get_contents($file) : '';
        page::assign('robots', $robots);



        // Форма редактирования шаблонов страниц
        $form = new uiMultiForm('page_tpl');
        $form->insideForm();
        $form->setData(templates::getByDestination());
     	$form->addColumn('t_name', lang::get('STRUCTURE_TABLE_FIELD_5'), 300);
        $form->addColumn('t_file', lang::get('STRUCTURE_TABLE_FIELD_6'), 300, lang::get('STRUCTURE_TABLE_FIELD_7'));
        page::assign('page_tpl', $form->getHTML());


        // Форма редактирования шаблонов объектов
        $form = new uiMultiForm('obj_tpl');
        $form->insideForm();
        $form->setData(templates::getByDestination(1));
     	$form->addColumn('t_name', lang::get('STRUCTURE_TABLE_FIELD_5'), 300);
        $form->addColumn('t_file', lang::get('STRUCTURE_TABLE_FIELD_6'), 300, lang::get('STRUCTURE_TABLE_FIELD_7'));
        page::assign('obj_tpl', $form->getHTML());



        // Управление отображением
        $classes = ormClasses::getPagesClassList();
        ui::SelectBox('no_view_classes', $classes, reg::getList(ormPages::getPrefix().'/no_view'), 600, '&nbsp;', '', 'selectbox_multi_big');
        ui::SelectBox('no_edit_classes', $classes, reg::getList(ormPages::getPrefix().'/no_edit'), 600, '&nbsp;', '', 'selectbox_multi_big');

		return page::parse($TEMPLATE['frame']);
 	}



  	public function proc() {

        // ******************	Основные настройки	***************************

        reg::setKey(ormPages::getPrefix().'/title_prefix', system::POST('title_prefix'));
        reg::setKey(ormPages::getPrefix().'/keywords', system::POST('keywords'));
        reg::setKey(ormPages::getPrefix().'/description', system::POST('description'));
        reg::setKey('/structure/'.domains::curId().'/cur_date', system::POST('cur_date', isBool));
        reg::setKey(ormPages::getPrefix().'/no_view_no_edit', system::POST('no_view_no_edit', isBool));
        reg::setKey(ormPages::getPrefix().'/view_as_tree', system::POST('view_as_tree', isBool));
        reg::setKey(ormPages::getPrefix('search').'/auto_index', system::POST('auto_index', isBool));

        // Сохраняем информацию о части файла Robots.txt
        if (isset($_POST['robots'])) {

            $file = ROOT_DIR.'/robots_part_'.domains::curDomain()->getName().'.txt';
        	if (empty($_POST['robots']) && file_exists($file)) {

        		unlink($file);

        	} else if (!empty($_POST['robots'])) {
        		$f = fopen ($file, "w");
				fwrite($f, $_POST['robots']);
				fclose($f);
        	}
        }

        // Управление отображением

        reg::delKey(ormPages::getPrefix().'/no_view');
        if (isset($_POST['no_view_classes']))
	        while(list($num, $val) = each($_POST['no_view_classes']))
            	if (!empty($val)) reg::setKey(ormPages::getPrefix().'/no_view/'.$num, $val);

        reg::delKey(ormPages::getPrefix().'/no_edit');
	    if (isset($_POST['no_edit_classes']))
	        while(list($num, $val) = each($_POST['no_edit_classes']))
	        	if (!empty($val)) reg::setKey(ormPages::getPrefix().'/no_edit/'.$num, $val);


        // ******************	Сохранение шаблонов	***************************
        function changeTempl($id, $obj, $form_name){

            $type = ($form_name == 'page_tpl') ? 0 : 1;

            $templ = new template($id);
            $templ->setName($obj['t_name']);
            $templ->setFile($obj['t_file']);
            $templ->setDestination($type);
            $templ->setLangId(languages::curId());
            $templ->setDomainId(domains::curId());
            $templ->save();

			return true;
        }

        function delTempl($id){
            $templ = new template($id);
            $templ->delete();
        }

        $form = new uiMultiForm('page_tpl');
        $form->process('changeTempl', 'delTempl');

        $form = new uiMultiForm('obj_tpl');
        $form->process('changeTempl', 'delTempl');

        ormPages::clearCache();

        if ($_POST['parram'] == 'apply')
			system::redirect('/structure/settings');
		else if ($_SESSION['STRUCTURE_LIST_FLAG'])
        	system::redirect('/structure/list');
        else
        	system::redirect('/structure/tree');
  	}




}

?>