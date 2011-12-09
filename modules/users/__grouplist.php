<?php

class __grouplist {

	public function __construct() {
    	ui::checkClasses('user_group', 'user');
    }

    // вывод списка
	public function defAction() {

	    // Формируем список классов для быстрого добавления
	    $types = ormClasses::get('user')->getAllInheritors();
        if (count($types) > 1){
	        $class_list = '';
		    while(list($id, $name) = each($types)) {
			    $bclass = ormClasses::get($id);
			    $class_list .= '<li><a href="'.system::au().'/users/user_add/0/'.$bclass->getSName().'" >'.$bclass->getName().'</a></li>';
	        }
			$java = '<script> $("#usel").parent().css("width", "150px"); </script>';
			ui::newButton(lang::get('BTN_NEW_USER'), "/users/user_add", 'class_list', '<ul id="usel">'.$class_list.'</ul>'.$java);
		} else ui::newButton(lang::get('BTN_NEW_USER'), "/users/user_add");

        ui::newButton(lang::get('BTN_NEW_UGROUP'), "/users/group_add");


        $sel = new ormSelect('user_group');
		$sel->orderBy('name', asc);

		$table = new uiTable($sel);
		$table->showSearch(true);

        $table->addColumn('name', lang::get('USERS_TABLE_FIELD_5'), 0, true);
        $table->addColumn('children', lang::get('USERS_TABLE_FIELD_6'), 0, true, true, 'count');

        $table->defaultRight('userlist');
      	$table->addRight('userlist', 'users', single);
        $table->addRight('group_upd', 'edit', single);
        $table->addRight('group_act', 'active', multi);
        $table->addRight('group_del', 'drop', multi);

        $table->setDelMessage(lang::get('USERS_DEL_TITLE2'), lang::get('USERS_DEL_TEXT2'));
        $table->setMultiDelMessage(lang::get('USERS_DEL_TITLE_MULTI2'), lang::get('USERS_DEL_TEXT_MULTI2'));

        return $table->getHTML();
 	}

}

?>