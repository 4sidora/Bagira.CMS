<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Статический класс, коллекция методов по работе с правами доступа
	пользователей системы.
*/

class rights {

    /**
    * @return null
    * @param integer $domain_id - ID домена
    * @param integer $lang_id - ID языковой версии
    * @desc Создает список прав для модуля "Структура" для указанной языковой версии и домена.
    */
    public static function createForStructure($domain_id, $lang_id){

	    $d_id = db::q('INSERT INTO <<modules_rights>> SET mr_name = "'.$lang_id.' '.$domain_id.'", mr_mod_id = "3", mr_is_default = "0";');
        db::q('INSERT INTO <<modules_rights>> SET mr_name = "settings", mr_parent_id = "'.$d_id.'", mr_domain_id="'.$domain_id.'", mr_lang_id="'.$lang_id.'", mr_is_default = "0", mr_mod_id = "3";');
        db::q('INSERT INTO <<modules_rights>> SET mr_name = "minitext", mr_parent_id = "'.$d_id.'", mr_domain_id="'.$domain_id.'", mr_lang_id="'.$lang_id.'", mr_is_default = "0", mr_mod_id = "3";');

        $t_id = db::q('INSERT INTO <<modules_rights>> SET mr_name = "tree", mr_parent_id = "'.$d_id.'", mr_domain_id="'.$domain_id.'", mr_lang_id="'.$lang_id.'", mr_is_default = "1", mr_mod_id = "3";');
        db::q('INSERT INTO <<modules_rights>> SET mr_name = "list", mr_parent_id = "'.$t_id.'", mr_domain_id="'.$domain_id.'", mr_lang_id="'.$lang_id.'", mr_is_default = "0", mr_mod_id = "3";');
        db::q('INSERT INTO <<modules_rights>> SET mr_name = "page_upd", mr_parent_id = "'.$t_id.'", mr_domain_id="'.$domain_id.'", mr_lang_id="'.$lang_id.'", mr_is_default = "0", mr_mod_id = "3";');
        db::q('INSERT INTO <<modules_rights>> SET mr_name = "page_add", mr_parent_id = "'.$t_id.'", mr_domain_id="'.$domain_id.'", mr_lang_id="'.$lang_id.'", mr_is_default = "0", mr_mod_id = "3";');
        db::q('INSERT INTO <<modules_rights>> SET mr_name = "page_del", mr_parent_id = "'.$t_id.'", mr_domain_id="'.$domain_id.'", mr_lang_id="'.$lang_id.'", mr_is_default = "0", mr_mod_id = "3";');
        db::q('INSERT INTO <<modules_rights>> SET mr_name = "page_act", mr_parent_id = "'.$t_id.'", mr_domain_id="'.$domain_id.'", mr_lang_id="'.$lang_id.'", mr_is_default = "0", mr_mod_id = "3";');
        db::q('INSERT INTO <<modules_rights>> SET mr_name = "page_moveto", mr_parent_id = "'.$t_id.'", mr_domain_id="'.$domain_id.'", mr_lang_id="'.$lang_id.'", mr_is_default = "0", mr_mod_id = "3";');
        db::q('INSERT INTO <<modules_rights>> SET mr_name = "page_history", mr_parent_id = "'.$t_id.'", mr_domain_id="'.$domain_id.'", mr_lang_id="'.$lang_id.'", mr_is_default = "0", mr_mod_id = "3";');
        db::q('INSERT INTO <<modules_rights>> SET mr_name = "page_copy", mr_parent_id = "'.$t_id.'", mr_domain_id="'.$domain_id.'", mr_lang_id="'.$lang_id.'", mr_is_default = "0", mr_mod_id = "3";');
    }

    // Вспомогательный метод для метода getListForModuls()
    private static function getRightListSub($rights, $setRights, $rid, $modul, $modul_name, $template, $RIGHT){

		$str = '';
		reset($rights);
		while (list($key, $val) = each ($rights)) {

			if ($val['mr_parent_id'] == $rid && $val['mr_mod_id'] == $modul) {

			    $tmp = self::getRightListSub($rights, $setRights, $val['mr_id'], $modul, $modul_name, $template, $RIGHT);

		        if (!empty($setRights) && array_key_exists($val['mr_id'], $setRights)) {
			        $checked = 'checked';
		            $value = $setRights[$val['mr_id']];
		        } else {
		            $checked = '';
		            $value = 0;
		        }

		        page::assign("subright", $tmp);
		        page::assign("right", $val['mr_name']);
		        page::assign("right_id", $val['mr_id']);
		        page::assign("checked", $checked);
		        page::assign("value", $value);

                $pos = strpos($val['mr_name'], ' ');
		        if ($pos) {
		        	$lang_id = substr($val['mr_name'], 0, $pos);
		        	$domain_id = substr($val['mr_name'], $pos + 1, strlen($val['mr_name']) - $pos);

                    $domain = new domain($domain_id);
                    $lang = new language($lang_id);
		        	page::assign("right_name", $domain->getName().' ('.$lang->getName().')');

		        } else {
                    $right_name = (isset($RIGHT[$modul_name][$val['mr_name']])) ? $RIGHT[$modul_name][$val['mr_name']] : $val['mr_name'];
                	page::assign("right_name", $right_name);
                }

		        $str .= page::parse($template);
		    }
		}

		return $str;
	}

    /**
    * @return string HTML
    * @param integer $obj_id - ID группы или пользователя
    * @param boolean $obj_type - Тип объекта: 0 - группа, 1 - пользователь
    * @desc Генерирует страницу редактирования прав для группы или пользователя
    */
	public static function getListForModuls($obj_id, $obj_type) {

 		if (file_exists(MODUL_DIR.'/users/template/right_list.tpl'))
            include(MODUL_DIR.'/users/template/right_list.tpl');

        $setRights = array();
        if (!empty($obj_id)) {

             $rights = db::q("SELECT rgu_right_id, rgu_value FROM <<modules_rgu>>
                          	  WHERE rgu_obj_id = '$obj_id';", records);

             while (list($key, $val) = each ($rights))
                   $setRights[$val['rgu_right_id']] = $val['rgu_value'];
        }

        $modules = db::q("SELECT m_id, m_name FROM <<modules>> WHERE m_active=1 ORDER BY m_id;", records);

        $rights = db::q("SELECT * FROM <<modules_rights>> ORDER BY mr_id;", records);

        $str = '';
        while (list($key, $val) = each ($modules)) {

            if (file_exists(MODUL_DIR.'/'.$val['m_name'].'/lang-ru.php'))
	            include(MODUL_DIR.'/'.$val['m_name'].'/lang-ru.php');

        	$tmp = self::getRightListSub($rights, $setRights, 0, $val['m_id'], $val['m_name'], $TEMPLATE['right_'.$obj_type], $RIGHT);

        	if (!empty($tmp)) {
         		page::assign("modul_name", $MODNAME[$val['m_name']]);
              	page::assign("modul_id", $val['m_id']);
              	page::assign("modul_rights", $tmp);

              	$str .= page::parse($TEMPLATE['frame']);
           	}
        }

        return $str;
	}

	/**
    * @return string HTML
    * @param integer $obj_id - ID группы или пользователя
    * @param boolean $obj_type - Тип объекта: 0 - группа, 1 - пользователь
    * @desc Сохраняет настройки прав пришедшие через POST для группы или пользователя
    */
	public static function setListForModuls($obj_id, $obj_type) {

		if (!empty($obj_id)) {

            db::q("DELETE FROM <<modules_rgu>> WHERE rgu_obj_id = $obj_id");

			if (!empty($_POST['rights'])) {

		        if ($obj_type == 0) {

		            while (list($key, $right) = each ($_POST['rights'])) {

			            $right = system::checkVar($right, isInt);

		                db::q("INSERT INTO <<modules_rgu>>
			                    SET rgu_obj_id = $obj_id,
			                        rgu_value = 1,
			                        rgu_right_id = $right;");
		            }

		        } else {

		            while (list($right, $value) = each ($_POST['rights'])) {

		                $right = system::checkVar($right, isInt);
		                $value = system::checkVar($value, isInt);

		                if ($value != 0 && $right != false && $value != false)
		                  db::q("INSERT INTO <<modules_rgu>>
			                    SET rgu_obj_id = $obj_id,
			                        rgu_value = $value,
			                        rgu_right_id = $right;");
		            }
		        }
		    }
	    }
	}

    /**
    * @return string HTML
    * @param integer $obj - Экземпляр объекта (ormObject, ormPage)
    * @param string $action - Тип действия: "upd" - изменение, "add" - добавление
    * @desc Выводит форму изменения прав доступа для объекта
    */
	public static function getListForObject($obj, $action) {

 		if (file_exists(MODUL_DIR.'/users/template/right_list_object.tpl'))
            include(MODUL_DIR.'/users/template/right_list_object.tpl');

   		if (isset($_POST['query'])) {

            // Формируем список подсказок для пользователя
        	$sel = new ormSelect('user');
	        $sel->fields('login');
	        $sel->where('id', '<>', '29');
	        $sel->where('login', 'LIKE', '%'.$_POST['query'].'%');

            $users = '';
	        while($user = $sel->getObject()) {
	        	$zapi = ($sel->getObjectNum() != 0) ? ', ' : '';
	        	$users .= $zapi."'".$user->login."'";
	        }

        	echo "{ query:'".$_POST['query']."', suggestions:[".$users."], data:[] }";
        	system::stop();

        } else if (isset($_POST['user_name'])) {

            // Добавляем выбранного пользователя в список
            $sel = new ormSelect('user');
	        $sel->fields('login');
	        $sel->where('id', '<>', '29');
	        $sel->where('login', '=', $_POST['user_name']);
			$sel->limit(1);
            $user = $sel->getObject();

            if ($user instanceof ormObject) {
	        	page::assign("group.id", $user->id);
	         	page::assign("group.name", $user->login);
	         	page::assign("checked_edit", '');
           		page::assign("checked_view", 'checked');
	            echo page::parse($TEMPLATE['group']);
            }

        	system::stop();
        }

        // Выводим список групп
        $sel = new ormSelect('user_group');
	    $sel->where('id', '<>', '32');
		$sel->orderBy('name', asc);
		$groups = $sel->getData();

        $rights = array();
        if ($action == 'upd') {

			$users = db::q('SELECT o_id, login o_name
							FROM <<rights>>, <<objects>>, <<__user>>
							WHERE r_obj_id = "'.$obj->id.'" and
								  r_group_id = o_id and
								  obj_id = o_id and
								  o_class_id <> 33;', records);

            $groups = array_merge($groups, $users);

	        $tmp = db::q('SELECT r_state, r_group_id FROM <<rights>> WHERE r_obj_id = "'.$obj->id.'";', records);
	        while (list($key, $right) = each($tmp))
	        	$rights[$right['r_group_id']] = $right['r_state'];
            $select_all = (count($tmp) == 1 && empty($tmp[0]['r_group_id'])) ? $tmp[0]['r_state'] : false;

		} else $select_all = 2;


        $items = '';
        $num = $edit_num = $view_num = 0;
        while (list($key, $group) = each($groups)) {

            $state = (isset($rights[$group['o_id']])) ? $rights[$group['o_id']] : 0;

            if ($state == 2) $edit_num ++;
            $checked_edit = ($state == 2 || $select_all == 2) ? 'checked' : '';
            page::assign("checked_edit", $checked_edit);

            if ($state > 0) $view_num ++;
        	$checked_view = ($state > 0 || $select_all > 0) ? 'checked' : '';
            page::assign("checked_view", $checked_view);

        	page::assign("group.id", $group['o_id']);
         	page::assign("group.name", $group['o_name']);
            $items .= page::parse($TEMPLATE['group']);
            $num = $key + 1;
        }

        $checked_edit = ($select_all == 2 || $num == $edit_num) ? 'checked' : '';
        page::assign("checked_edit", $checked_edit);
        $checked_view = ($select_all > 0 || $num == $view_num) ? 'checked' : '';
        page::assign("checked_view", $checked_view);

        page::assign("text.all_user", lang::get('STRUCTURE_RIGHT_ALL_USER'));
        page::assign("text.title", lang::get('STRUCTURE_RIGHT_TITLE'));

        page::assign("groups", $items);
        return page::parse($TEMPLATE['frame']);;
	}

	/**
    * @return string HTML
    * @param integer $obj - Экземпляр объекта (ormObject, ormPage)
    * @desc Сохраняет настройки прав доступ для объекта, пришедшие через POST
    */
	public static function setListForObject($obj) {

		if ($obj instanceof ormObject) {

            if (isset($_POST['all_edit']) && isset($_POST['all_view'])) {

                // Если выбрано "все пользователи"
                $state = (isset($_POST['all_edit'])) ? 2 : 1;
            	$obj->setRightForAll($state);

            } else if (!empty($_POST['view_right'])) {

		    	// Если выбраны специфичные права
		        while (list($id, $val) = each ($_POST['view_right'])) {
	            	$state = (isset($_POST['edit_right'][$id])) ? 2 : 1;
					$obj->setRight($id, $state);
		        }

	        } else $obj->clearRight();
	    }
	}



}

?>