<?php

class __page {

    public function __construct() {
    	ui::checkClasses('section', 'page');

    	ui::setNewLink(lang::get('STRUCTURE_SETTINGS'), 'settings', 'tabs-main');
        ui::setNewLink(lang::get('STRUCTURE_FORM_FIELD', 11), 'settings', 'tabs-page_tpl');
        ui::setNewLink(lang::get('STRUCTURE_FORM_FIELD', 14), 'settings', 'tabs-obj_tpl');
        ui::setNewLink(lang::get('STRUCTURE_FORM_FIELD', 15), 'settings', 'tabs-view');
    	ui::setNewLink(lang::get('STRUCTURE_MINITEXT'), 'minitext');
    }

	// форма добавления объекта  /mpanel/structure/page_add
	public function add() {
		return $this->upd();
 	}

 	// форма редактирования объекта  /mpanel/structure/page_upd
	public function upd() {

        // Проверяем наличие шаблонов
        $templs = templates::getByDestination(0, true);
        if (empty($templs)) {
        	ui::MessageBox(lang::get('STRUCTURE_TEMPL_NOT_FOUND'), lang::get('STRUCTURE_TEMPL_NOT_FOUND2'));
        	system::redirect('/structure/settings#tabs-page_tpl');
        }

        // Указываем для какого объекта строить форму
        if (system::action() == "upd") {

            // Изменение страницы

			if (system::url(2) == 0)
				system::redirect('/structure/settings');

            $obj = ormPages::get(system::url(2));

			if ($obj instanceof ormPage) {
				$parent_id = $obj->getParentId();
				ui::setHeader($obj->name);
	            ui::setNaviBar(lang::get('TEXT_EDIT').$obj->getClass()->getPadej(1));
            }

            $class_list = '';
			$right = 'page_proc_upd';

		} else if (system::action() == "add") {

            // Добавление страницы

            $class_name = (system::issetUrl(3)) ? system::url(3) : ormPages::getPopularClass(system::url(2));
            if (!$class = ormClasses::get($class_name))
                system::redirect('/structure/tree');

			if (in_array($class->id(), reg::getList(ormPages::getPrefix().'/no_edit')))
            	system::redirect('/structure/tree');

            if (system::issetUrl(2) && system::url(2) != 0) {
	            $parent = ormPages::get(system::url(2));
	            ui::setNaviBar($parent->name, '/structure/list/'.$parent->id);
            }
            ui::setHeader(lang::get('TEXT_ADD').$class->getPadej(1));


			// Если это добавление нового объекта
			$obj = new ormPage();
			$obj->setParent(system::url(2));
            $obj->setClass($class_name);

            $obj->view_in_menu = 1;
            $obj->view_submenu = 1;
            $obj->active = 1;
            $obj->in_search = 1;
            $obj->in_index = 1;
            $obj->publ_date = date('d.m.Y H:i:s');

            // Наследуем параметры от родителя

            // Шаблоны
            $parent_id = ($obj->issetParents()) ? $obj->getParentId() : 0;
            $templ = templates::getPopularForSection($parent_id);
			
			$def_templ_1 = $obj->getClass()->getDefTemplate(0);
			$def_templ_2 = $obj->getClass()->getDefTemplate(1);

			$obj->template_id = $def_templ_1 != 0 ? $def_templ_1 : $templ[0];
			$obj->template2_id = $def_templ_2 != 0 ? $def_templ_2 : $templ[1];

            $parent_id = system::url(2);


            // Количество элементов на странице
            $this->getPopularValue($obj, 'number_of_items', 10);

            // Способ сортировки
            $this->getPopularValue($obj, 'order_by', '');

            // Формируем список классов для быстрого изменения класса объекта

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
				    	<a href="'.system::au().'/structure/page_add/'.$parent_id.'/'.$bc->getSName().'">'.$bc->getName().'</a></li>';
	                else
				    	$elem_list .= '
				    	<li ><img src="'.$pach.$ico.'">
				    	<a href="'.system::au().'/structure/page_add/'.$parent_id.'/'.$bc->getSName().'">'.$bc->getName().'</a></li>';
		    	}
	        }
	        $class_list = '<ul>'.$cat_list.'</ul><ul>'.$elem_list.'</ul>';

            $right = 'page_proc_add';
		}

		page::assign('right', $right);

        // Если произошли ошибки, перенаправляем на главную страницу модуля
		if (!($obj instanceof ormPage) || $obj->issetErrors())
			system::redirect('/structure/tree');


        // Устанавливаем кнопки для формы
        if (isset($_SESSION['STRUCTURE_LIST_FLAG']) && $_SESSION['STRUCTURE_LIST_FLAG'])
        	ui::setCancelButton('/structure/list/'.$parent_id);
        else
        	ui::setCancelButton('/structure/tree');

        if ($obj->isEditable()){
			ui::newButton(lang::get('BTN_SAVE'), "javascript:sendForm('save');");
	        ui::newButton(lang::get('BTN_APPLY'), "javascript:sendForm('apply');");
        }

        // Создаем форму и выводим ее на страницу
        $form = new ormEditForm($obj, $right);
        $form->setORMList($class_list);
        $form->addPadding('param', 8, 1);


        // Выбор шаблона оформления страниц
        $form->addField('param', 9, '', lang::get('STRUCTURE_TEMPLATE'), $this->getTemplateBox('template_id', $templs, $obj->template_id));

        // Выбор шаблона оформления объектов
        $form->addField('param', 10, '', lang::get('STRUCTURE_TEMPLATE2'), $this->getTemplateBox('template2_id', templates::getByDestination(1, true), $obj->template2_id, 1));

        // Выводим ID и URL страницы
        $this->getPageInfo($obj, $form);

        $form->newTabs(lang::get('STRUCTURE_TABS_RIGHT'), rights::getListForObject($obj, system::action()));
        //$form->newTabs(lang::get('STRUCTURE_TABS_RESTORE'), '&nbsp;');

        return $form->getHTML();
 	}

    private function getPageInfo($obj, $form) {
        if ($obj->id != 0) {
            $domain = (system::isLocalhost()) ? domains::curDomain()->getUrl() : '';
            $form->addInBottomTabs('param', '
                <div class="clear"></div><div class="otstup"></div>
                <div class="fieldBox"><label><b></b>'.lang::get('STRUCTURE_PAGE_URL').'</label> <a href="'.$domain . $obj->_url.'">'.$obj->_url.'</a></div>
                <div class="fieldBox"><label><b></b>'.lang::get('STRUCTURE_PAGE_URL').'</label> '.$obj->id.'</div>
            ');
        }
    }


 	private function getTemplateBox($field_name, $templs, $value, $fla = 0) {

         $empty = ($field_name == 'template2_id') ? lang::get('STRUCTURE_NO_CHANGE') : '';
        return ui::SelectBox($field_name, $templs, $value, 400, $empty, '', 'selectbox_template').'
                 <div id="'.$field_name.'_edit_block" class="input_min_2" style="display:none;">
                    <input value="" id="'.$field_name.'_new_val" name="'.$field_name.'_new_val" class="" />
                    <input value="" id="'.$field_name.'_new_val2" name="'.$field_name.'_new_val2" class="" />
                 </div>
                 <span class="add_value" onclick="return AddNewTemplate(this, '.$fla.');"></span>
                 <small>'.ui::CheckBox($field_name.'_all', 1, 0, 'Применить для всех подразделов', 0, 0, 0).'</small>';
 	}

 	private function getPopularValue($obj, $field_name, $def_value) {

 		if ($obj->getClass()->issetField($field_name)) {

	 		if ($obj->issetParents()) {

	          	$parent = $obj->getParent();

	          	if ($parent->issetChildren()) {

		            $max_val = array();
		            $parent->resetChild();
	                while($ch = $parent->getChild()) {
	                	if (isset($max_val[md5($ch->__get($field_name))]))
			        		$max_val[md5($ch->__get($field_name))]['count'] ++;
			        	else
			        		$max_val[md5($ch->__get($field_name))] = array('value' => $ch->__get($field_name), 'count' => 1);
	                }

	                $max_count = 0;
			        while(list($num, $val) = each($max_val)){
			        	if ($val['count'] > $max_count) {
			        		$max_count = $val['count'];
			        		$value = $val['value'];
			        	}
	        		}
	          	}

                if (empty($value))
                    if ($parent->getClass()->issetField($field_name))
                        $value = $parent->__get($field_name);
                    else
                        $value = $def_value;

	   		} else $value = $def_value;

	   		$obj->__set($field_name, $value);

             
   		}
 	}

 	// обработчик добавления объекта  /mpanel/structure/page_proc_add
  	public function proc_add() {
		$this->proc_upd();
  	}

 	// обработчик изменения объекта  /mpanel/structure/page_proc_upd
  	public function proc_upd() {

        $mini_action = substr(system::action(), -3);

        $this->createTemplate('template_id');
        $this->createTemplate('template2_id');

        if (system::action() == "proc_upd") {

            // Говорим какой объект нужно изменить
			$obj = new ormPage(system::POST('obj_id'));

		} else if (system::action() == "proc_add") {

            // Говорим какой объект нужно создать
			$obj = new ormPage();
            $obj->setClass(system::POST('class_id'));
            $obj->setParent(system::POST('obj_id'));

		}

        // Если произошли ошибки, перенаправляем на главную страницу модуля
		if ($obj->issetErrors())
			system::redirect('/structure/tree');

        // Присваиваем пришедшие значения полям в объекте
        $obj->loadFromPost($mini_action);
        rights::setListForObject($obj);

        // Сохраняем изменения
        $obj_id = $obj->save();

        // Если объект не сохранился, выводим пользователю текст ошибки.
        if ($obj_id === false) {

            system::savePostToSession();
	    	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), $obj->getErrorListText());
	    	ui::selectErrorFields($obj->getErrorFields());

            $class = ($mini_action == 'add') ? '/'.system::POST('class_id') : '';
	    	system::redirect('/structure/page_'.$mini_action.'/'.$_POST['obj_id'].$class);

	 	}  else {

	        // Присваиваем выбранные шаблоны для всех вложенных объектов, если выбрано.
	        if (system::POST('template_id_all', isBool))
	        	$this->inheritTemplate($_POST['template_id'], false, $obj_id);

	        if (system::POST('template2_id_all', isBool))
	        	$this->inheritTemplate($_POST['template2_id'], true, $obj_id);

	 	}

        // Если данные изменились корректно перенаправляем на соответствующию страницу
		if ($_POST['parram'] == 'apply')
			system::redirect('/structure/page_upd/'.$obj_id);
		else if (isset($_SESSION['STRUCTURE_LIST_FLAG']) && $_SESSION['STRUCTURE_LIST_FLAG'])
        	system::redirect('/structure/list/'.$obj->getParentId());
        else
        	system::redirect('/structure/tree');

  	}

  	private function inheritTemplate($templ_id, $templ_type, $obj_id) {

        if ($obj = ormPages::get($obj_id)) {

	        $obj->resetChild();

	        while ($page = $obj->getChild()) {

				if ($templ_type)
				    $page->template2_id = $templ_id;
				else
				    $page->template_id = $templ_id;
				$page->save();

	        	$this->inheritTemplate($templ_id, $templ_type, $page->id);
			}
		}
  	}


  	// При необходимости создает новый шаблон для страницы
  	private function createTemplate($field_name) {

  		$id = 0;
  		$type = ($field_name == 'template_id') ? 0 : 1;

  		if (!empty($_POST[$field_name.'_new_val']) && !empty($_POST[$field_name.'_new_val2'])) {
  			$templ = new template($id);
            $templ->setName($_POST[$field_name.'_new_val']);
            $templ->setFile($_POST[$field_name.'_new_val2']);
            $templ->setDestination($type);
            $templ->setLangId(languages::curId());
            $templ->setDomainId(domains::curId());
            $id = $templ->save();
     	}

     	if (!empty($id))
     		$_POST[$field_name] = $id;
  	}

  	// изменение активности объекта  /mpanel/structure/page_proc_act
  	public function proc_act() {

    	if (system::issetUrl(2) && is_numeric(system::url(2))) {

	    	$obj = ormPages::get(system::url(2));

	    	if ($obj instanceof ormPage){
				$obj->active = ($obj->active) ? false : true;
				$obj->save();

				if (!$obj->issetErrors())
					echo ($obj->active) ? 'active' : 'no_active';
	        }

        } else if (isset($_POST['objects'])) {

        	// Множественное изменение
        	$invert = true;

        	while(list($id, $val) = each($_POST['objects'])) {

        		if (is_numeric($id)) {

        			$obj = ormPages::get($id);

			    	if ($obj instanceof ormPage){
						$obj->active = ($obj->active) ? false : true;
						$obj->save();

						if ($obj->issetErrors())
							$invert = false;
			        }
				}
        	}

        	if ($invert) echo 'invert';
        }

        system::stop();

  	}

  	// удаление объекта              /mpanel/structure/page_proc_del
  	public function del() {

        set_time_limit(600);

        if (system::issetUrl(2) && is_numeric(system::url(2))) {

            // Одиночное удаление
	    	if ($obj = ormPages::get(system::url(2))){
				$obj->toTrash();
				echo 'delete';
            }

        } else if (isset($_POST['objects'])) {

        	// Множественное удаление
        	while(list($id, $val) = each($_POST['objects'])) {

        		if (is_numeric($id)) {

        			if ($obj = ormPages::get($id))
						$obj->toTrash();
				}
        	}
            echo 'delete';
        }

        system::stop();


  	}

  	// перемещение объекта          /mpanel/structure/page_proc_moveto
  	public function proc_moveto() {

        if ($obj = ormPages::get(system::url(3))) {

	        $obj->delParent(system::url(5));

	    	if (system::url(4) == 'inside'){

	        	// Перемещаем в другой раздел, в конец списка
	        	$obj->setParent(system::url(2));

	     	} else {

	            // Устанавливаем позицию до\после указанного объекта
	            // Получаем соседа
	            $neighbor = ormPages::get(system::url(2));

	            $position = $neighbor->getPosition(system::url(6));
	            if (system::url(4) == 'after')
	            	$position = $position + 1;

	            $obj->setParent(system::url(6), $position);

	      	}

	        $obj->save();

	        if (!$obj->issetErrors())
				echo 'ok';
        }
    	system::stop();
  	}


  	// Просмотр истории изменения объекта          /mpanel/structure/page_history
  	public function history() {

        if (system::url(2) == 0)
			system::redirect('/structure');

		if ($obj = ormPages::get(system::url(2))) {

			if (isset($_SESSION['STRUCTURE_LIST_FLAG']) && $_SESSION['STRUCTURE_LIST_FLAG'])
	        	ui::setBackButton('/structure/list/'.$obj->getParentId());
	        else
	        	ui::setBackButton('/structure/tree');

	        ui::setNaviBar(lang::right('page_history').' '.$obj->getClass()->getPadej(1));
			ui::setHeader($obj->name);

	        return ui::getHistoryTable($obj->id);
    	}

	    system::redirect('/structure');

    }


    // Создание копирование страницы          /mpanel/structure/page_copy
  	public function copy() {

        if (system::url(2) == 0)
			system::redirect('/structure');

		if ($obj = ormPages::get(system::url(2))) {

            $obj->copy();

			if (isset($_SESSION['STRUCTURE_LIST_FLAG']) && $_SESSION['STRUCTURE_LIST_FLAG'])
	        	system::redirect('/structure/list/'.$obj->getParentId());
	        else
	        	system::redirect('/structure/tree');
    	}

    }

}

?>