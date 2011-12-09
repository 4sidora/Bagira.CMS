<?php

class __tree {

    public function __construct() {
    	ui::checkClasses('section', 'page');

    	ui::setNewLink(lang::get('STRUCTURE_SETTINGS'), 'settings', 'tabs-main');
        ui::setNewLink(lang::get('STRUCTURE_FORM_FIELD', 11), 'settings', 'tabs-page_tpl');
        ui::setNewLink(lang::get('STRUCTURE_FORM_FIELD', 14), 'settings', 'tabs-obj_tpl');
        ui::setNewLink(lang::get('STRUCTURE_FORM_FIELD', 15), 'settings', 'tabs-view');
    	ui::setNewLink(lang::get('STRUCTURE_MINITEXT'), 'minitext');
    }

	// вывод списка  /mpanel/structure/tree
	public function defAction() {



        if (!reg::getKey(ormPages::getPrefix().'/view_as_tree'))
        	system::redirect('/structure/list');

        $_SESSION['STRUCTURE_LIST_FLAG'] = 0;

        // Если страниц нет, предлагаем добавить новую
        if (ormPages::getCountOfSection(0) == 0) {
        	ui::MessageBox(lang::get('STRUCTURE_PAGE_NOT_FOUND'), lang::get('STRUCTURE_PAGE_NOT_FOUND2'));
        	system::redirect('/structure/page_add');
        }

	    $types = ormClasses::get('section')->getAllInheritors();
	    $no_edit = reg::getList(ormPages::getPrefix().'/no_edit');
        $elem_list = $cat_list = '';
	    while(list($id, $name) = each($types)) {
            if (!in_array($id, $no_edit)) {
		    	$obj = ormClasses::get($id);

		    	$pach = '/css_mpanel/tree/images/';
                $ico = 'classes/'.$obj->getSName().'.png';
	            if (!file_exists(ROOT_DIR.$pach.$ico))
	            	$ico = 'file1.gif';

                if ($obj->isInheritor('category') || $obj->getSName() == 'section')
			    	$cat_list .= '
			    	<li><img src="'.$pach.$ico.'">
			    	<a name="'.system::au().'/structure/page_add/%obj_id%/'.$obj->getSName().'" href="">'.$obj->getName().'</a></li>';
                else
			    	$elem_list .= '
			    	<li ><img src="'.$pach.$ico.'">
			    	<a name="'.system::au().'/structure/page_add/%obj_id%/'.$obj->getSName().'" href="">'.$obj->getName().'</a></li>';
	    	}
        }
        $class_list = '<ul>'.$cat_list.'</ul><ul>'.$elem_list.'</ul>';


        function getPageNotice($page) {
            $notice = '';

            if ($page->is_home_page)
        		$notice = lang::get('STRUCTURE_TREE_TEXT1');

        	if ($page->other_link != '')
        		$notice = lang::get('STRUCTURE_TREE_TEXT3');

        	if (!$page->view_in_menu) {
        		if (!empty($notice)) $notice .= ', ';
        		$notice .= lang::get('STRUCTURE_TREE_TEXT2');
        	}

        	if (!empty($notice))
        		return '('.$notice.')';
        	else
        		return '';
        }

		$tree = new ormTree(975, 265);

        $tree->setClass('ormPage');
	  	$tree->setRoot(0, reg::getKey(ormPages::getPrefix().'/title_prefix'), 'settings');

	  	$tree->setRightEdit('page_upd');
	  	$tree->setRightActive('page_proc_act');
	  	$tree->setRightRemove('page_proc_moveto');
	  	$tree->setRightAjaxLoad('tree');

	  	$tree->setDelMessage(lang::get('STRUCTURE_DEL_TITLE'), lang::get('STRUCTURE_DEL_TEXT'));

        $tree->addRight('getUrl()', lang::get('STRUCTURE_PAGE_URL'), 'view_image');
	  	$tree->addEmptyRight();
        $tree->addEmptyRight();
	  	$tree->addRight('page_add', lang::right('page_add'), 'add_image', 0, 1, 'class_list', $class_list);
	  	$tree->addEmptyRight();
	  	$tree->addRight('list', lang::right('list'), 'list_block_image');
	  	$tree->addEmptyRight();
	  	$tree->addRight('page_upd', lang::right('page_upd'), 'compose_image');
    	$tree->addRight('page_copy', lang::right('page_copy'), 'copy_image', 0, 0);
	  	$tree->addRight('page_history', lang::right('page_history'), 'history_image', 0, 0);
	  	$tree->addRight('page_del', lang::right('page_del'), 'drop_image', 1, 0);

	  	$tree->setNotice('getPageNotice');

	  	return $tree->getHTML();
 	}


}

?>