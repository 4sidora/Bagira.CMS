<?php

class __class {

	// форма добавления объекта
	public function add() {
		return $this->upd();
 	}

 	// формa редактирования объекта
	public function upd() {

        $is_page = false;

        if (file_exists(MODUL_DIR.'/constructor/template/class.tpl'))
        	include(MODUL_DIR.'/constructor/template/class.tpl');

		if (system::action() == "upd") {

			// форма обновления информации
			$class = new ormClass(system::url(2));

			if ($class->id() == '')
            	system::redirect('/constructor/tree');

			page::assign('obj.class_name', $class->getName());
			page::assign('obj.sname', $class->getSName());
			page::assign('obj.text', $class->getPadej());
			page::assign('obj.id', $class->id());

			$system = $class->isSystem();
			$is_list = $class->isList();

            $is_page = $class->isPage();
            $is_user = $class->isInheritor('user');
            $base_class = $class->getBaseClass();

			page::assign('right', 'class_proc_upd');

            // Выводим дерево для отображения структуры класса
            $tree = new ormFieldsTree();
			page::assign('fields', $tree->getHTML($class));


		} else if (system::action() == "add") {

			// форма добавления информации
            $system = $is_list = $uniqum = $base_class = 0;

            // Проверяем существует ли родитель?
            if (system::issetUrl(2)) {

				$parent = new ormClass(system::url(2));

				if ($parent->id() == '')
	            	system::redirect('/constructor/tree');

	            $is_list = $parent->isList();
	            $is_page = $parent->isPage();
	            $is_user = $parent->isInheritor('user');
            } else $is_user = false;

            page::assign('obj.id', system::url(2));
            page::assign('right', 'class_proc_add');
		}

        if ($is_page) {
			ui::SelectBox('class_list', ormClasses::getPagesClassList(), $base_class, 400, '&nbsp;');
			page::fParse('page_fields', $TEMPLATE['page_fields']);
		} else if ($is_user) {
			page::fParse('page_fields', $TEMPLATE['user_fields']);
		} else page::assign('page_fields', '');


		if (system::action() == "add" || !$class->isSystem()) {
        	ui::newButton(lang::get('BTN_SAVE'), "javascript:sendForm('save');");
        	ui::newButton(lang::get('BTN_APPLY'), "javascript:sendForm('apply');");
        	ui::setCancelButton('/constructor');
        } else ui::setBackButton('/constructor');

        page::assignArray(lang::get('CONSTR_FORM_FIELD'));
        page::assignSavingPost();

		ui::CheckBox('system', 1, $system, lang::get('CONSTR_FORM_FIELD', 3));
		ui::CheckBox('is_list', 1, $is_list, lang::get('CONSTR_FORM_FIELD', 4));

		return page::parse($TEMPLATE['frame'], 1);
 	}

 	// обработчик добавления объекта
  	public function proc_add() {
		$this->proc_upd();
  	}

 	// обработчик изменения объекта
  	public function proc_upd() {


        if (system::action() == "proc_upd") {

			$class = new ormClass($_POST['obj_id']);

		} else if (system::action() == "proc_add") {

			$class = new ormClass();
            $class->setParentId($_POST['obj_id']);

		}

	    $class->setName(system::POST('class_name'));
	    $class->setSName(system::POST('sname'));
	    $class->setSystem(system::POST('system'));
	    $class->setIsList(system::POST('is_list'));

	    if (isset($_POST['text']))
	    	$class->setPadej($_POST['text']);

        if (isset($_POST['class_list']))
	    	$class->setBaseClass($_POST['class_list']);

	    $class_id = $class->save();

	    if ($class_id === false) {

            $listError = '';
	    	$errors = $class->getErrorList();
	    	while(list($key, $text) = each($errors))            	$listError .= $text.'<br />';

            system::savePostToSession();
	    	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), $listError);

	    	system::redirect('/constructor/class_'.substr(system::action(), -3).'/'.$_POST['obj_id']);
	 	}

        if ($_POST['parram'] == 'apply')
			system::redirect('/constructor/class_upd/'.$class_id);
		else
			system::redirect('/constructor/tree');
  	}

  	// удаление объекта
  	public function del() {

        $class = new ormClass(system::url(2));

        if ($class->delete())
    		echo "ok";
    	else
            echo "error";

    	system::stop();

  	}

  	// перемещение объекта
  	public function moveto() {

    	system::redirect('/constructor/tree');
  	}


}

?>