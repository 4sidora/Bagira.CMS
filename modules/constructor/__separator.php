<?php

class __separator {

    // форма добавления объекта
	public function add() {
		return $this->upd();
 	}

 	// формa редактирования объекта
	public function upd() {

        if (file_exists(MODUL_DIR.'/constructor/template/separator.tpl'))
        	include(MODUL_DIR.'/constructor/template/separator.tpl');

		if (system::action() == "upd") {

			// форма обновления информации
			$obj = new ormField(system::url(2));

			if ($obj->id() == '')
            	system::stop();

			page::assign('obj.fname', $obj->getName());

            $max_size = ($obj->getMaxSize() != '') ? $obj->getMaxSize() : 0;
			page::assign('obj.max_size', $max_size);
			page::assign('obj.id', $obj->id());

			page::assign('right', 'separator_proc_upd');

		} else if (system::action() == "add") {

			// форма добавления информации
            if (system::issetUrl(2)) {
                // Проверяем существует ли родитель?
				$group = new ormFieldsGroup(system::url(2));
				if ($group->id() == '')
	            	system::stop();
            }

            page::assign('obj.id', system::url(2));
            page::assign('right', 'separator_proc_add');
			page::assign('obj.max_size', 0);
		}

        page::assignArray(lang::get('CONSTR_FORM_FIELD3'));
        page::assignSavingPost();

		echo page::parse($TEMPLATE['frame'], 1);
		system::stop();
 	}

    // обработчик добавления объекта
  	public function proc_add() {
		$this->proc_upd();
  	}

 	// обработчик изменения объекта
  	public function proc_upd() {

        if (system::issetUrl(2)) {

            $obj = new ormField();
  		    $obj->setGroupId(system::url(2));

        } else if (system::action() == "proc_upd") {

			$obj = new ormField($_POST['obj_id']);

		} else if (system::action() == "proc_add") {

			$obj = new ormField();
            $obj->setGroupId($_POST['obj_id']);
		}

        if (!empty($_POST['fname']))
            $obj->setName($_POST['fname']);

        if (!empty($_POST['max_size']))
            $obj->setMaxSize($_POST['max_size']);
        else
            $obj->setMaxSize(0);

          
	    $obj->setType(0);
	    $obj->setInherit(1);
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

}

?>