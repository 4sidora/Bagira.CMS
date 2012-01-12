<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Статический класс для работы с текущим пользователем.
    Основные возможности:
    	- Авторизация (вход/выход)
    	- Определение группы, статуса пользователя
    	- Получение всей информации о пользователе
    	- Проверка прав доступа
*/

class user {

    private static $right = array();
    private static $defModul = '';

    private static $isGuest = true;
    private static $isAdmin = false;
    private static $guestGroup = 48;

    private static $obj;

    // Иницилизация класса
    static function init() {

     	if (isset($_SESSION['curUser']['name']) && $_SESSION['curUser']['name'] != 'none'){

      		self::$isGuest = false;
        	self::$isAdmin = $_SESSION['curUser']['isAdmin'];


            $key = 'user'.$_SESSION['curUser']['id'];

            if (!(self::$obj = cache::get($key))) {
              
                self::$obj = ormObjects::get($_SESSION['curUser']['id']);

                // Записываем в кэш
                cache::set($key, self::$obj);
            }

            /*
            self::$obj->last_visit = date('Y-m-d H:i:s');
            self::$obj->last_ip = $_SERVER['REMOTE_ADDR'];

            self::$obj->save();    */
      	}

       if (!isset($_SESSION['curUser']['name']))
       		self::guestCreate();

    }

    

    // Создает пользователя гостя
    private static function guestCreate() {

	   self::$isGuest = true;
       self::$isAdmin = false;

       self::updateSession(0, '', 0, 'none', 'none');
    }

    // Обновляет данные текущей сессии
    private static function updateSession($id, $login, $name, $email) {

       $_SESSION['curUser']['id'] = $id;
       $_SESSION['curUser']['login'] = $login;
       $_SESSION['curUser']['name'] = $name;
       $_SESSION['curUser']['email'] = $email;
       $_SESSION['curUser']['isAdmin'] = self::$isAdmin;
   
    }

    // Выход пользователя
    static function logout($redirect = true) {

    	system::log(lang::get('EXIT_USER'), info);
     	session_unset();

     	self::guestCreate();

        if ($redirect)
     	    system::redirect('/');
    }

    // Автоматическая авторизация указанного пользователя
    static function authHim(ormObject $user) {

        if ($user->isInheritor('user')) {

            self::$obj = $user;

            self::$obj->last_visit = date('Y-m-d H:i:s');
            self::$obj->last_ip = $_SERVER['REMOTE_ADDR'];
            self::$obj->error_passw = 0;
            self::$obj->save();

            // Загружаем данные и обновляем сессию
            self::getRights();
            self::$isAdmin = (count(self::$right) == 0) ? false : true;
            self::$isGuest = false;

            self::updateSession(self::$obj->id,
                                self::$obj->login,
                                self::$obj->name,
                                self::$obj->email);

            system::log(lang::get('ENTER_USER'), info);

            return true;
        }

        return false;
    }

    // Авторизация
    static function auth($login, $password) {

    	$ret = false;
     	$login = system::checkVar($login, isString);

		$sel = new ormSelect('user');
	    $sel->where(
	        $sel->val('active', '=', 1),
			$sel->val('login', '=', trim($login)),
			$sel->containedIn('user_group',
				$sel->val('active', '=', 1)
			)
		);
        $sel->limit(1);

  		if(self::$obj = $sel->getObject()) {

    		if (self::$obj->password == system::checkVar($password, isPassword)) {

                $ret = self::authHim(self::$obj);

      		} else {

        		$max_error = reg::getKey('/users/errorCountBlock');

          		//Смотрим, если у юзера уже N неправильных паролей, то блокируем его
            	if ((self::$obj->error_passw + 1) >= $max_error && $max_error > 0) {
             		self::$obj->active = 0;
             		self::sendMailBlock(self::$obj);
                }

                self::$obj->error_passw++;
                self::$obj->save();

                if (!self::$obj->active) {

                	//записываем что пользователь заблокирован по своей дурости из-за не знания пароля
                 	system::log(str_replace('%user%', $login, str_replace('%count%', $max_error, lang::get('BLOCKED_USER'))), error);

                } else {

                	//Записываем в журнал о неправильном вводе пароля
                 	system::log(str_replace('%user%', $login, lang::get('ERROR_PASSWORD')), error);
                }
            }
        }

        return $ret;
    }

    // Отправка сообщения о блокировки пользователя
    private static function sendMailBlock($user) {

        page::assign('domain', 'http://'.domains::curDomain()->getName().languages::pre());
      	page::assign('login', $user->login);
        page::assign('name', $user->name);
       	system::sendMail('/users/mails/block.tpl', $user->email);
  	}

    // Проверяет вхождение пользователя в указанную группу
    static function inGroup($group_id) {

        if (self::$isGuest) {
            return ($group_id == self::$guestGroup) ? true : false;
    	} else if (self::$obj instanceof ormObject) {
    		return (array_key_exists($group_id, self::$obj->getParents())) ? true : false;
    	} else return false;

    }

    // Вернет массив, список групп в которые входит пользователь
    static function getGroups() {
           // print_r(self::$obj);
        if (self::$obj instanceof ormObject)  {
    		return self::$obj->getParents();
    	}else{
    		return array(self::$guestGroup => self::$guestGroup);
    	}
    }

    // Вернет любую информацию о текущем пользователе
    static function get($name) {
        if (self::$obj instanceof ormObject)
    		return self::$obj->__get($name);
    	else
    		return '';
    }

    // Вернет true, если пользователь имеет права доступа в панель администрирования
    static function isAdmin() {
		return self::$isAdmin;
    }

    // Вернет true, если пользователь гость (не авторизован)
    static function isGuest() {
		return self::$isGuest;
    }

    // Вернет экземпляр ORM-объекта для изменение данных пользвателя
    static function getObject() {
		if (self::$obj instanceof ormObject)
			return self::$obj;
    }



    // +++	Работа с правами +++

    /**
	* @return boolean
	* @param string $right - Имя права в панели администрирования
	* @param string $module - Системное имя модуля. Если не указанно, имя определяется исходя из текущего URL`a
	* @desc Проверяет существование указанного права для текущего модуля
	*/
    static function issetRight($right, $module = 0) {

    	if (!self::$isGuest) {

            if (empty($module))
	            $module = system::url(0);

    		self::getRights();

    		$right = str_replace('_proc_', '_', $right);

            if ($module == 'structure' && !strpos($right, ' ')) {
            	$sitever = languages::curId().' '.domains::curId();
            	return (isset(self::$right[$module]['rights'][$sitever][$right])) ? true : false;
            } else
    			return (isset(self::$right[$module]['rights'][$right])) ? true : false;

    	} else
    		return false;

    }

    // Проверяем имеет ли пользователь права на указанный модуль
    static function issetModule($module) {

    	if (self::$isAdmin) {

    		self::getRights();
    		return (isset(self::$right[$module]['rights'])) ? true : false;

    	} else
    		return false;

    }

    // Возвращает право по умолчанию для текущего модуля
    static function getDefaultRight($module) {

    	if (self::$isAdmin && system::$isAdmin) {

    	    self::getRights();

    		if (isset(self::$right[$module]['def_right']))
	    		return self::$right[$module]['def_right'];
	    	else
	    		return false;

		} else return false;
    }

    // Формирует массив с правами для текущего пользователя
 	static function getRights() {

        if (count(self::$right) == 0){

            // Формируем список групп в которые входит пользователь
            $groups = self::$obj->getParents();
            $objs = '';
            while (list($key, $val) = each ($groups))
            	$objs .= ' or rgu_obj_id = "'.$key.'" ';

            // Получаем все права текущего пользователя
            self::$right = self::getRightsFor(self::$obj->id, $objs);
        }

   		return self::$right;
 	}

 	// Формирует массив с правами для указанного объекта: группы или пользователя
 	static function getRightsForObject($obj) {

        if (is_numeric($obj)) {

        	return self::getRightsFor($obj);

        } else if ($obj instanceof ormObject) {

	        if ($obj->isInheritor('user_group')) {

	   			return self::getRightsFor($obj->id);

	   		} else if ($obj->isInheritor('user')) {

		  		// Формируем список групп в которые входит пользователь
		        $groups = $obj->getParents();
		        $groups_ids = '';
		        while (list($key, $val) = each ($groups))
		            $groups_ids .= ' or rgu_obj_id = "'.$key.'" ';

		   		return self::getRightsFor($obj->id, $groups_ids);
	   		}
        }

 	}

    /**
	* @return array
	* @param int $obj - ID объекта или ORM-объект
	* @param boolean $ru_names - Если true, в массиве используются русские имена
	* @desc Формирует список доступных модулей для указанного объекта: группы или пользователя
	*/
 	static function getModulesForObject($obj, $ru_names = true) {

    	$rights = self::getRightsForObject($obj);

		$modules = array();

		if (count($rights)) {
	        while (list($key, $val) = each($rights)) {

	            if ($ru_names){
	            	$name = lang::module($key);
	            	if (empty($name)) $name = $key;
	            } else $name = $key;

	        	$modules[] = array($val['id'], $name);
	        }
        }

        return $modules;
 	}

    /**
	* @return array
	* @param integer $obj_id - ID объекта
	* @param string $groups_ids - Дополнительные уловия в SQL запрос
	* @desc Вспомогательная функция для получения прав доступа объекта
	*/
 	private static function getRightsFor($obj_id, $groups_ids = '') {

		// Получаем список разрещенных прав
        $sql = 'SELECT m_id, m_name, mr_name, mr_is_default, mr_parent_id, mr_lang_id, mr_domain_id
        		FROM <<modules_rights>>, <<modules_rgu>>, <<modules>>
        		WHERE m_id = mr_mod_id and
        			  m_active = 1 and
        			  rgu_right_id = mr_id and
        			  rgu_value = 1 and
        			  (rgu_obj_id = "'.$obj_id.'" '.$groups_ids.')
        		ORDER BY m_sort ASC;';

 		$rights = db::q($sql, records);
        $old_mod = '';
 		$right = array();
   		while (list($key, $val) = each ($rights)) {

   			// В случае, если нет права по умолчанию, ставим первое попавшееся
            if ($old_mod != $val['m_name']) {
            	if (!empty($old_mod) && empty($right[$old_mod]['def_right'])) {
            		$right[$old_mod]['id'] = $tmp_id;
            		$right[$old_mod]['def_right'] = $tmp_def_right;
            	}
            	$old_mod = $val['m_name'];
            	$tmp_id = $tmp_def_right = '';
            }

   			// Добавляем права в список допустимых
   			if ($val['m_name'] == 'structure' && !strpos($val['mr_name'], ' ')) {

				// Добавление прав для модуля Структура (поддержка мультидоменности)
   				$sitever = $val['mr_lang_id'].' '.$val['mr_domain_id'];
   				if (isset($right[$val['m_name']]['rights'][$sitever]) && is_array($right[$val['m_name']]['rights'][$sitever]))
                    $right[$val['m_name']]['rights'][$sitever][$val['mr_name']] = 1;
       			else
          			$right[$val['m_name']]['rights'][$sitever] = array($val['mr_name'] => 1);

   			} else if (!isset($right[$val['m_name']]['rights'][$val['mr_name']]))
      			$right[$val['m_name']]['rights'][$val['mr_name']] = 1;

            //Дополнительна информ. о модуле
            if ($val['mr_is_default']) {
            	$right[$val['m_name']]['id'] = $val['m_id'];
            	$right[$val['m_name']]['def_right'] = $val['mr_name'];
            } else if ($val['mr_parent_id'] == 0) {
                $tmp_id = $val['m_id'];
            	$tmp_def_right = $val['mr_name'];
            }


            // Определяем имя модуля по умолчанию
            $def_mod = (self::$obj->def_modul == 0) ? 1 : self::$obj->def_modul;
            if (self::$obj->id == $obj_id && $val['m_id'] == $def_mod)
            	self::$defModul = $val['m_name'];
   		}

   	    // В случае, если нет права по умолчанию, ставим первое попашееся
     	if (!empty($old_mod) && empty($right[$old_mod]['def_right'])) {
            $right[$old_mod]['id'] = $tmp_id;
            $right[$old_mod]['def_right'] = $tmp_def_right;
      	}

   		// Если это пользователь, проверяем наличие запрещающих прав
   		if (!empty($groups_ids)) {

	   		$sql = 'SELECT mr_name, m_name
	     			FROM <<modules_rights>>, <<modules_rgu>>, <<modules>>
	                WHERE m_id = mr_mod_id and
	        			  m_active = 1 and
	        			  rgu_right_id = mr_id and
	        			  rgu_value = "-1" and
	        			  rgu_obj_id = "'.$obj_id.'"
	                GROUP BY mr_id;';

	        $ban_rights = db::q($sql, records);

	        // Удаляем из основного списка запрещенные права
	        while (list($key, $val) = each ($ban_rights))
	           	if (isset($right[$val['m_name']]['rights'][$val['mr_name']])) {
	           		unset($right[$val['m_name']]['rights'][$val['mr_name']]);
	             	if (count($right[$val['m_name']]['rights']) == 0)
	                   	unset($right[$val['m_name']]);
	            }
        }
           // print_r($right);
        return $right;
    }

    // Вернет имя модуля по умолчанию
    static function getDefModul() {

    	if (self::$isAdmin) {

	    	self::getRights();
	    	return self::$defModul;

    	} else return '';
    }


}

?>