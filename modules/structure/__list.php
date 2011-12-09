<?php

class __list {

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

        $_SESSION['STRUCTURE_LIST_FLAG'] = 1;

        // Если страниц нет, предлагаем добавить новую
        if (ormPages::getCountOfSection(0) == 0) {
        	ui::MessageBox(lang::get('STRUCTURE_PAGE_NOT_FOUND'), lang::get('STRUCTURE_PAGE_NOT_FOUND2'));
        	system::redirect('/structure/page_add');
        }

        $obj_id = (system::issetUrl(2)) ? system::url(2) : 0;


        function getPageNotice($page) {
            $notice = '';

            if ($page->is_home_page)
        		$notice = lang::get('STRUCTURE_TREE_TEXT12');

        	if ($page->other_link != '')
        		$notice = lang::get('STRUCTURE_TREE_TEXT32');

        	if (!$page->view_in_menu) {
        		if (!empty($notice)) $notice .= ', ';
        		$notice .= lang::get('STRUCTURE_TREE_TEXT22');
        	}

        	if (!empty($notice))
        		return '('.$notice.')';
        	else
        		return '';
        }

        // Вывод дерева объектов
		$tree = new ormTree(328, 57);
        $tree->setClass('ormPage');
	  	$tree->setRoot(0, reg::getKey(ormPages::getPrefix().'/title_prefix'), 'list/0');
	  	$tree->setNotice('getPageNotice');

	  	$tree->setRightEdit('list');
	  	$tree->setRightActive('page_proc_act');
	  	$tree->setRightRemove('page_proc_moveto');
	  	$tree->setRightAjaxLoad('list');

	  	$tree->setDelMessage(lang::get('STRUCTURE_DEL_TITLE'), lang::get('STRUCTURE_DEL_TEXT'));

        $tree->addRight('page_upd', lang::right('page_upd'), 'compose_image');
	  	$tree->addRight('page_add', lang::right('page_add'), 'add_image'); //, 'class_list', $class_list

        ui::setLeftPanel($tree->getHTML());

        // Заголовок страницы
        ui::setHeader(lang::right('tree'));
        if (!empty($obj_id) && $obj = ormPages::get($obj_id)) {
			ui::setNaviBar(lang::right('list'));
        	ui::setHeader($obj->name);
			ui::setBackButton('/structure/list/'.$obj->getParentId());
        } else $obj_id = 0;

        // Формируем список классов для быстрого добавления
        $class_name = ormPages::getPopularClass($obj_id);
        $class = ormClasses::get($class_name);



        $types = ormClasses::get('section')->getAllInheritors();
	    $no_edit = reg::getList(ormPages::getPrefix().'/no_edit');
        $elem_list = $cat_list = '';
	    while(list($id, $name) = each($types)) {
            if (!in_array($id, $no_edit)) {
		    	$bc = ormClasses::get($id);

		    	$pach = '/css_mpanel/tree/images/';
                $ico = 'classes/'.$bc->getSName().'.png';
	            if (!file_exists(ROOT_DIR.$pach.$ico))
	            	$ico = 'file1.gif';

                if ($bc->isInheritor('category') || $bc->getSName() == 'section')
			    	$cat_list .= '
			    	<li><img src="'.$pach.$ico.'">
			    	<a href="'.system::au().'/structure/page_add/'.$obj_id.'/'.$bc->getSName().'">'.$bc->getName().'</a></li>';
                else
			    	$elem_list .= '
			    	<li ><img src="'.$pach.$ico.'">
			    	<a href="'.system::au().'/structure/page_add/'.$obj_id.'/'.$bc->getSName().'">'.$bc->getName().'</a></li>';
	    	}
        }
        $class_list = '<ul>'.$cat_list.'</ul><ul>'.$elem_list.'</ul>';

        ui::newButton(lang::get('PANEL_BTN_ADD_PART').$class->getPadej(0), "/structure/page_add/".$obj_id, 'class_list', $class_list);


        // Формируем выборку страниц для таблицы
        $sel = new ormSelect();
        $sel->findInPages();
		$sel->where('parents', '=', $obj_id);

		// Определяем способ сортировки списка
		$order_by = (!empty($obj_id)) ? $obj->order_by : '';
		if (!empty($order_by)) {
            $pos = strpos($order_by, ' ');
            if ($pos) {
            	$parram = substr($order_by, $pos + 1);
            	$order_by = substr($order_by, 0, $pos);
            } else $parram = '';
        	$sel->orderBy($order_by, $parram);
        } else $sel->orderBy(position, asc);

        // Вывод таблицы
        $table = new uiTable($sel);
		$table->formatValues(true);
		$table->showSearch(true);
        $table->hideEmptyColumns(true);

        $table->addColumn('name', lang::get('STRUCTURE_TABLE_FIELD_1'), 500, true);
        $table->addColumn('image', lang::get('STRUCTURE_TABLE_FIELD_2'), 400, false, false);
        $table->addColumn('price', lang::get('STRUCTURE_TABLE_FIELD_3'), 400);
        $table->addColumn('publ_date', lang::get('STRUCTURE_TABLE_FIELD_4'), 400);

        $table->defaultRight('page_upd');
        $table->addRight('page_upd', 'edit', single);
        $table->addRight('list', 'list', single);
        $table->addRight('page_history', 'history', single);
        $table->addRight('page_proc_act', 'active', multi);
        $table->addRight('page_del', 'drop', multi);

        $table->setDelMessage(lang::get('STRUCTURE_DEL_TITLE'), lang::get('STRUCTURE_DEL_TITLE'));
        $table->setMultiDelMessage(lang::get('STRUCTURE_DEL_TITLE_MULTI'), lang::get('STRUCTURE_DEL_TEXT_MULTI'));

        return $table->getHTML();


 	}


}

?>