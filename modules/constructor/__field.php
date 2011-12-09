<?php

class __field {

	// форма добавления объекта
	public function add() {
		return $this->upd();
 	}

 	// формa редактирования объекта
	public function upd() {

        if (file_exists(MODUL_DIR.'/constructor/template/field.tpl'))
        	include(MODUL_DIR.'/constructor/template/field.tpl');

		if (system::action() == "upd") {

			// форма обновления информации
			$obj = new ormField(system::url(2));

			if ($obj->id() == '')
            	system::stop();

			page::assign('obj.fname', $obj->getName());
			page::assign('obj.fsname', $obj->getSName());
			page::assign('obj.hint', $obj->getHint());
			page::assign('obj.max_size', $obj->getMaxSize());
			page::assign('obj.id', $obj->id());

			$view = $obj->getView();
			$inherit = $obj->getInherit();
            $search = $obj->getSearch();
            $filter = $obj->getFilter();
            $required = $obj->getRequired();
            $system = $obj->getSystem();
            $type = $obj->getType();
            $list_id = $obj->getListId();
            $uniqum = $obj->getUniqum();
            $quick_add = $obj->getQuickAdd();
            $relation = $obj->getRelType();
            $spec = $obj->getSpec();

            $fr = ($obj->getSName() == 'name') ? '_name' : '';

			page::assign('right', 'field_proc_upd');

		} else if (system::action() == "add") {

			// форма добавления информации
            if (system::issetUrl(2)) {
                // Проверяем существует ли родитель?
				$group = new ormFieldsGroup(system::url(2));
				if ($group->id() == '')
	            	system::stop();
            }

            $view = $inherit = 1;
            $search = $filter = $required = $system = $type = $list_id = $uniqum = $spec = $quick_add = $relation = 0;

            $fr = '';

            page::assign('obj.id', system::url(2));
            page::assign('right', 'field_proc_add');

		}


        page::assignArray(lang::get('CONSTR_FORM_FIELD3'));
        page::assignSavingPost();

		ui::CheckBox('view', 1, $view, lang::get('CONSTR_FORM_FIELD3', 6));
		ui::CheckBox('inherit', 1, $inherit, lang::get('CONSTR_FORM_FIELD3', 7));
		ui::CheckBox('search', 1, $search, lang::get('CONSTR_FORM_FIELD3', 8));
		ui::CheckBox('filter', 1, $filter, lang::get('CONSTR_FORM_FIELD3', 9));
		ui::CheckBox('required', 1, $required, lang::get('CONSTR_FORM_FIELD3', 10));
        ui::CheckBox('system', 1, $system, lang::get('CONSTR_FORM_FIELD3', 11));
        ui::CheckBox('uniqum', 1, $uniqum, lang::get('CONSTR_FORM_FIELD3', 13));
        ui::CheckBox('quick_add', 1, $quick_add, lang::get('CONSTR_FORM_FIELD3', 14));
        ui::CheckBox('spec', 1, $spec, lang::get('CONSTR_FORM_FIELD3', 16));


        ui::SelectBox('reltype', lang::get('CONSTR_RELTYPE'), $relation, 300);
		ui::SelectBox('type', lang::get('CONSTR_TYPE_LIST'.$fr), $type, 300);

		$list = ormClasses::getHandbooks();
        ui::SelectBox('list_id', $list, $list_id, 300, '&nbsp;');
        page::assign('sh', ((!empty($list_id)) ? '' : 'style="display:none;"'));

        page::assign('sh2', (($type > 69 && $type < 86) ? '' : 'style="display:none;"'));
        page::assign('sh3', (($type == 90 || $type == 95 || $type == 100) ? '' : 'style="display:none;"'));
        page::assign('sh4', (($type == 55 || $type == 60) ? '' : 'style="display:none;"'));

		echo page::parse($TEMPLATE['frame'.$fr], 1);
		system::stop();
 	}

 	// обработчик добавления объекта
  	public function proc_add() {
		$this->proc_upd();
  	}

 	// обработчик изменения объекта
  	public function proc_upd() {


        if (system::action() == "proc_upd") {

			$obj = new ormField($_POST['obj_id']);

		} else if (system::action() == "proc_add") {

			$obj = new ormField();
            $obj->setGroupId($_POST['obj_id']);

		}

	    $obj->setName(system::POST('fname'));
	    $obj->setSName(system::POST('fsname'));
	    $obj->setHint(system::POST('hint'));
	    $obj->setType(system::POST('type'));
	    $obj->setView(system::POST('view'));
	    $obj->setSearch(system::POST('search'));
	    $obj->setInherit(system::POST('inherit'));
	    $obj->setFilter(system::POST('filter'));
	    $obj->setRequired(system::POST('required'));
	    $obj->setSystem(system::POST('system'));
	    $obj->setUniqum(system::POST('uniqum'));
	    $obj->setMaxSize(system::POST('max_size'));
	    $obj->setQuickAdd(system::POST('quick_add'));
        $obj->setRelType(system::POST('reltype'));
        $obj->setSpec(system::POST('spec'));

        // Работа с привязанным справочником
	    if (system::POST('type') == 95 || system::POST('type') == 90 || system::POST('type') == 97) {

            $list_id = system::POST('list_id');

            // Если не был указан справочник, то автоматически создаем новый
	        if (empty($list_id)) {
                
                $class_name = 'list_'.system::POST('fsname', isVarName);

                if ($class = ormClasses::get($class_name))

                    $list_id = $class->id();

                else {

                    $class = new ormClass();
                    $class->setParentId(29);
                    $class->setName('Для поля "'.system::POST('fname', isString).'"');
                    $class->setSName($class_name);
                    $class->setSystem(0);
                    $class->setIsList(1);
                    $list_id = $class->save();

                    if ($list_id === false)
                        $list_id = 0;
                }
	        }

	    	$obj->setListId($list_id);

	    } else $obj->setListId(0);

	    $obj_id = $obj->save();

	    if ($obj_id === false) {

            echo json_encode(array('error' => 1, 'data' => $obj->getErrorListText(' ')));

	 	} else {

            $tree = new ormFieldsTree();
            $forUpd = (system::action() == "proc_add") ? 0 : 1;
            echo json_encode(array('error' => 0, 'data' => $tree->getFieldHTML($obj, $forUpd)));
		}


		system::stop();
  	}

  	// удаление объекта
  	public function del() {

        $obj = new ormField(system::url(2));

        if ($obj->delete())
    		echo "ok";
    	else
            echo "error";

    	system::stop();

  	}

  	// перемещение объекта
  	public function moveto() {

    	$obj = new ormField(system::url(2));
        $obj->setPosition(system::url(3));
        $obj->setGroupId(system::url(4));

        if ($obj->save())
    		echo "ok";
    	else {
    	    print_r($obj->getErrorList());
    	    echo 'error';
        }
    	system::stop();
  	}


}

?>