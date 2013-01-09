<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс для управления свойствами домена. В данном контексте домен == сайт.
*/

class domain extends innerErrorList {

    private $values = array();
    private $id;
    private $old_name = '';

	// Конструктор класса
 	public function __construct($obj_id = 0) {

    	if (!empty($obj_id) && is_numeric($obj_id)) {

    		$this->values = db::q('SELECT * FROM <<domains>> WHERE d_id = "'.$obj_id.'";', record);
    		$this->id = $obj_id;

    	} else if (is_array($obj_id) && !empty($obj_id['id']) && !empty($obj_id['d_name'])) {

            $this->values = $obj_id;
            $this->id = $obj_id['id'];
    	}

        if (isset($this->values['d_name']))
    		$this->old_name = $this->values['d_name'];

 	}

	// Вернет ID текущего домена
 	public function id() {
    	return $this->id;
    }

    // Вернет доменое имя
    public function getName() {
        if (isset($this->values['d_name']))
    		return $this->values['d_name'];
    }

    // Вернет ссылку на доммен
    public function getUrl() {
        if (isset($this->values['d_name']))
    		return 'http://'.$this->values['d_name'];
    }

	/**
	* @return null
	* @param string $name - Доменное имя сайта
	* @desc Устанавливает доменное имя
	*/
    public function setName($name) {
        $name = system::checkVar($name, isRuDomain);

        if (empty($name))
            $this->newError(56, 'Неправильно указано доменное имя сайта!');
        else {

        	$isset = db::q('SELECT count(d_id) FROM <<domains>>
        					WHERE d_name="'.$name.'" and
        						  d_domain_id is NULL and
        						  d_id <> "'.$this->id.'";', value);

        	if (empty($isset))
    			$this->values['d_name'] = $name;
    		else
    			$this->newError(57, 'Невозможно добавить домен "'.$name.'". Домен с таким именем уже зарегистрирован в системе!');
    	}
    }

    // Вернет true, если сайт "включен"
    public function online() {
        if (isset($this->values['d_online']))
    		return $this->values['d_online'];
    }

    /**
	* @return null
	* @param boolean $val - Состояние: true - включен, false - выключен.
	* @desc Устанавливает для сайта режим Вкл\Выкл
	*/
    public function setOnline($val) {
        $this->values['d_online'] = system::checkVar($val, isBool);
    }

    // Вернет сообщение при отключенном сайте
    public function getOfflineMsg() {
        if (isset($this->values['d_offline_msg']))
    		return $this->values['d_offline_msg'];
    }

    /**
	* @return null
	* @param string $val - Текст сообщения
	* @desc Устанавливает сообщение при отключенном сайте
	*/
    public function setOfflineMsg($val) {
        $this->values['d_offline_msg'] = system::checkVar($val, isText);
    }

    // Сообщение при ошибке
    public function getErrorMsg() {
        if (isset($this->values['d_error_msg']))
    		return $this->values['d_error_msg'];
    }

    /**
	* @return null
	* @param string $name - Текст сообщения
	* @desc Устанавливает сообщение при ошибке
	*/
    public function setErrorMsg($val) {
        $this->values['d_error_msg'] = system::checkVar($val, isText);
    }

    // Вернет название сайта
    public function getSiteName() {
        if (isset($this->values['d_sitename']))
    		return $this->values['d_sitename'];
    }

    /**
	* @return null
	* @param string $name - Название сайта
	* @desc Устанавливает название сайта
	*/
    public function setSiteName($name) {
        $name = system::checkVar($name, isString);

        if (empty($name))
            $this->newError(58, 'Неправильно указано название сайта!');
        else
    		$this->values['d_sitename'] = $name;
    }

    // Е-mail главного администратора
    public function getEmail() {
        if (isset($this->values['d_email']))
    		return $this->values['d_email'];
    }

    /**
	* @return null
	* @param string $name - E-mail
	* @desc Устанавливает e-mail главного администратора
	*/
    public function setEmail($name) {
        $name = system::checkVar($name, isEmail);

        if (empty($name))
            $this->newError(59, 'Неправильно указан основной E-mail!');
        else
    		$this->values['d_email'] = $name;
    }

    // Вернет ID языка по умолчанию
    public function getDefLang() {
        if (isset($this->values['d_def_lang']))
    		return $this->values['d_def_lang'];
    }

    /**
	* @return null
	* @param integer $val - ID языковой версии сайта
	* @desc Устанавливает текущий язык по умолчанию
	*/
    public function setDefLang($val) {
        $val = system::checkVar($val, isInt);
        if (empty($val))
            $this->newError(60, 'Неправильно указан язык по умолчанию!');
        else
        	$this->values['d_def_lang'] =  $val;

    }

    // Вернет в виде массива список зеркал для текущего доммена
    public function getMirrors() {
    	if (empty($this->values['d_id']))
    		return array();
    	else
        	return db::q('SELECT *, d_id id FROM <<domains>> WHERE d_domain_id = "'.$this->values['d_id'].'";', records);
    }

    /**
	* @return null
	* @param string $name - Доменное имя
	* @param integer $id - ID зеркала
	* @desc Добавляет или изменяет зеркало домена.
	*/
    public function changeMirror($name, $id = '') {

        $id = system::checkVar($id, isInt);
	    $name = system::checkVar($name, isRuDomain);

        if (!empty($this->id) && !empty($this->values['d_def_lang']) && !empty($name)) {

	    	if (!empty($id)) {

	    		db::q('UPDATE <<domains>> SET d_name = "'.$name.'" WHERE d_id = "'.$id.'";');
	    		system::log('Изменено зеркало "'.$name.'" (id:'.$id.') домена "'.$this->values['d_name'].'" (id:'.$this->id.')', warning);

	    	} else {

	    		db::q('INSERT INTO <<domains>>
	    			   SET d_name = "'.$name.'",
	    				   d_domain_id = "'.$this->id.'",
	    				   d_def_lang = NULL,
	    				   d_online = 1;');

	    		system::log('Добавлено зеркало "'.$name.'" (id:'.$id.') к домена "'.$this->values['d_name'].'" (id:'.$this->id.')', warning);
	    	}

	    	return true;
    	}
    }

    /**
	* @return null
	* @param integer $id - ID зеркала
	* @desc Удаляет указанное зеркало у текущего домена
	*/
    public function delMirror($id) {

    	if (!empty($this->id) && is_numeric($id)) {

    		db::q('DELETE FROM <<domains>> WHERE d_id = "'.$id.'" and d_domain_id = "'.$this->id.'";');
    		system::log('Удалено зеркало (id:'.$id.') у домена "'.$this->values['d_name'].'" (id:'.$this->id.')', warning);

    	}
    }

    // Записывает текущие изменения в БД, в случае успеха вернет true
    public function save() {

        if (!$this->issetErrors() && (empty($this->values['d_name']) || empty($this->values['d_def_lang'])||
        	empty($this->values['d_email']) || empty($this->values['d_sitename'])))
        	$this->newError(61, 'Не все обязательные поля были заполнены!');

        if ($this->issetErrors()) {

			return false;

    	} else if (!empty($this->id)) {

            // Изменение домена
            $sql = $this->getSql();
            if (!empty($sql))
	    		db::q('UPDATE <<domains>> SET '.$sql.' WHERE d_id = "'.$this->id.'";');

            system::log('Изменен домен "'.$this->values['d_name'].'" (id:'.$this->id.')', warning);

            // Переименовываем папки с шаблонами
       		if ($this->old_name != $this->values['d_name']){
	            $lang = languages::getAll();
				while(list($key, $val) = each($lang)) {
				    if ($this->id != 1 || $val['l_id'] != 1){
			       		$old_name = '/__'.str_replace('.', '_', $this->old_name).'_'.$val['l_prefix'];
			       		$new_name = '/__'.str_replace('.', '_', $this->values['d_name']).'_'.$val['l_prefix'];
						@rename(TEMPL_DIR.$old_name, TEMPL_DIR.$new_name);
					}
	            }
            }

            return true;

    	} else {

            // Добавление домена
            $sql = $this->getSql();
            if (!empty($sql))
            	$this->id = db::q('INSERT INTO <<domains>> SET '.$sql.';');

            if (!empty($this->id)) {


				$lang = languages::getAll();
				while(list($key, $val) = each($lang)) {


                    rights::createForStructure($this->id, $val['id']);

                    $templ = new template();
		            $templ->setName('default');
		            $templ->setFile('default');
		            $templ->setLangId($val['id']);
		            $templ->setDomainId($this->id);
		            $templ->save();

                    reg::setKey('/structure/'.$this->id.'/'.$val['id'].'/title_prefix', '%text% | '.$this->values['d_sitename']);
                    reg::setKey('/structure/'.$this->id.'/'.$val['id'].'/view_as_tree', 1);

					$dname = '/__'.str_replace('.', '_', $this->values['d_name']).'_'.$val['l_prefix'];
					$this->copyDir(TEMPL_DIR, TEMPL_DIR.$dname);
				}

                system::log('Создан домен "'.$this->values['d_name'].'" (id:'.$this->id.')', warning);
				return true;

			} else {
				$this->newError(62, 'Произошла ошибка при добавлении домена!');
				system::log('Произошла ошибка при добавлении домена!', error);
				return false;
			}
    	}
    }


    // Копируем папку особым образом
    private function copyDir($from_path, $to_path) {
		 mkdir($to_path, 0777);
		 $this_path = getcwd();
		 if (is_dir($from_path)) {
			  chdir($from_path);
			  $handle = opendir('.');
			  while (($file = readdir($handle))!==false) {
				   if ($file != "." && $file != ".." && substr($file, 0, 2) != '__') {
					    if (is_dir($file)) {
						     $this->copyDir($from_path.'/'.$file, $to_path.'/'.$file);
						     chdir($from_path);
					    }
				    	if (is_file($file))
				    		copy($from_path.'/'.$file, $to_path.'/'.$file);
				   }
		  	  }
		 	 closedir($handle);
		 }
	}

    private function getSql(){
    	$sql = '';
    	if (!empty($this->values))
            while(list($name, $value) = each($this->values))
            	if ($name != 'd_id' && $name != 'd_domain_id' && $name != 'id') {
	            	$zpt = (empty($sql)) ? '' : ', ';
	            	$sql .= $zpt.$name.' = "'.$value.'"';
            	}
     	return $sql;
    }

    // Удаление домена
    public function delete() {

    	if (!empty($this->id) && $this->id != 1) {

            $is_ok = true;

    		$list = languages::getAll();
			while(list($key, $val) = each($list))  {

    			// Удаление страниц
    			$tmp = ormPages::delAllFor($this->id, $val['id']);

    			if (!$tmp)
    				$is_ok = $tmp;
    			else
	    			// Удаление прав на домен
	    			db::q('DELETE FROM <<modules_rights>>
	    				   WHERE mr_name = "'.$val['id'].' '.$this->id.'"
	    				   		 and mr_mod_id = "3";');
    		}

    		// Удаление домена
            if ($is_ok) {

	    		$is_ok = db::q('DELETE FROM <<domains>> WHERE d_id = "'.$this->id.'";');

				if ($is_ok !== false) {

					// Удаление настроек
	    			reg::delKey('/structure/'.$this->id);

	    			system::log('Удален домен "'.$this->values['d_name'].'" (id:'.$this->id.')', warning);

    				return true;

    			} else {
    			    system::log('Произошла ошибка при удалении домена "'.$this->values['d_name'].'" (id:'.$this->id.')', error);
    				$this->newError(12, 'Произошла ошибка при удалении домена "'.$this->values['d_name'].'"!');
                }

            } else {
                system::log('Пользователь пытался удалить домен "'.$this->values['d_name'].'" (id:'.$this->id.'), но ему не хватило прав на удаление всех страниц!', error);
            	$this->newError(63, 'Вы не можете удалить домен "'.$this->values['d_name'].'", т.к. не имеете прав на удаление некоторых страниц!');
            }
    	}

    	return false;
    }
}

?>