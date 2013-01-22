<?php

class __userlist {

    public function __construct() {
    	ui::checkClasses('user_group', 'user');
    }

	public function defAction() {

        // Определяем какие кнопки будут
		$types = ormClasses::get('user')->getAllInheritors();
		if (count($types) > 1){
	        $class_list = '';
		    while(list($id, $name) = each($types)) {
			    $bclass = ormClasses::get($id);
			    $class_list .= '<a href="'.system::au().'/users/user_add/'.system::url(2).'/'.$bclass->getSName().'" style="line-height:17px;">'.$bclass->getName().'</a><br />';
	        }
			ui::newButton(lang::get('BTN_NEW_USER'), "/users/user_add/".system::url(2), 'class_list', $class_list);
		} else ui::newButton(lang::get('BTN_NEW_USER'), "/users/user_add/".system::url(2));
		ui::setBackButton('/users');

		// Заголовок страницы
		if ($group = ormObjects::get(system::url(2))) {
	        ui::setNaviBar($group->name);
        	ui::setHeader(lang::right('userlist'));
        }

        // Выбираем пользователей
		$sel = new ormSelect('user');
		if (system::issetUrl(2) && system::url(2) != 0)
        	$sel->where('parents', '=', system::url(2));
		$sel->orderBy('create_date', desc);

        // Строим таблицу
		$table = new uiTable($sel);
		$table->formatValues(true);
		$table->showSearch(true);

        $table->addColumn('login', lang::get('USERS_TABLE_FIELD_1'), 0, true);
        $table->addColumn('surname name', lang::get('USERS_TABLE_FIELD_2'), 0, true);
		$table->addColumn('create_date', lang::get('USERS_TABLE_FIELD_7'), 0, true, true, '', 1);
		$table->addColumn('last_visit', lang::get('USERS_TABLE_FIELD_3'), 0, true, true, '', 1);
        $table->addColumn('parents', lang::get('USERS_TABLE_FIELD_4'), 0, false, true);

        $table->defaultRight('user_upd');
        $table->addRight('user_upd', 'edit', single);
        $table->addRight('user_act', 'active', multi);
        $table->addRight('user_del', 'drop', multi);

        $table->setDelMessage(lang::get('USERS_DEL_TITLE'), lang::get('USERS_DEL_TEXT'));
        $table->setMultiDelMessage(lang::get('USERS_DEL_TITLE_MULTI'), lang::get('USERS_DEL_TEXT_MULTI'));

        return $table->getHTML();
 	}



}

?>