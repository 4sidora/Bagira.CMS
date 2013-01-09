<?php

class __fgroup {

	// форма добавления объекта
	public function add() {
		return $this->upd();
 	}

 	// формa редактирования объекта
	public function upd() {

        if (file_exists(MODUL_DIR.'/constructor/template/fgroup.tpl'))
        	include(MODUL_DIR.'/constructor/template/fgroup.tpl');

		if (system::action() == "upd") {

			// форма обновления информации
			$group = new ormFieldsGroup(system::url(2));

			if ($group->id() == '')
            	system::stop();

			page::assign('obj.group_name', $group->getName());
			page::assign('obj.group_sname', $group->getSName());
			page::assign('obj.id', $group->id());

			$view = $group->getView();
			$system = $group->getSystem();
			page::assign('right', 'fgroup_proc_upd');

		} else if (system::action() == "add") {

			// форма добавления информации
            if (system::issetUrl(2)) {
                // Проверяем существует ли родитель?
				$class = new ormClass(system::url(2));
				if ($class->id() == '')
	            	system::stop();
            }

            $view = 1;
            $system = 0;

            page::assign('obj.id', system::url(2));
            page::assign('right', 'fgroup_proc_add');

		}

        page::assignArray(lang::get('CONSTR_FORM_FIELD2'));
        page::assignSavingPost();

		ui::CheckBox('group_view', 1, $view, lang::get('CONSTR_FORM_FIELD2', 3));
        ui::CheckBox('group_system', 1, $system, lang::get('CONSTR_FORM_FIELD2', 4));

		echo page::parse($TEMPLATE['frame'], 1);
		system::stop();
 	}

 	// обработчик добавления объекта
  	public function proc_add() {
		$this->proc_upd();
  	}

 	// обработчик изменения объекта
  	public function proc_upd() {


        if (system::action() == "proc_upd") {

			$obj = new ormFieldsGroup($_POST['obj_id']);

		} else if (system::action() == "proc_add") {

			$obj = new ormFieldsGroup();
            $obj->setClassId($_POST['obj_id']);

		}

	    $obj->setName(system::POST('group_name'));
	    $obj->setSName(system::POST('group_sname'));
	    $obj->setView(system::POST('group_view'));
        $obj->setSystem(system::POST('group_system'));

	    $obj_id = $obj->save();

	    if ($obj_id === false) {

            echo json_encode(array('error' => 1, 'data' => $obj->getErrorListText(' ')));

	 	} else {

            $tree = new ormFieldsTree();
            $forUpd = (system::action() == "proc_add") ? 0 : 1;
            
            echo json_encode(array('error' => 0, 'data' => $tree->getGroupHTML($obj, $forUpd)));
		}


		system::stop();
  	}

  	// удаление объекта
  	public function del() {

        $obj = new ormFieldsGroup(system::url(2));

        if ($obj->delete())
    		echo "ok";
    	else
            echo "error";

    	system::stop();

  	}

  	// перемещение объекта
  	public function moveto() {

    	$obj = new ormFieldsGroup(system::url(2));
        $obj->setPosition(system::url(3));

        if ($obj->save())
    		echo "ok";
    	else
            echo "error";

    	system::stop();
  	}


}

?>