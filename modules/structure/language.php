<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс для управления свойствами языковых версий сайтов.
*/

class language extends innerErrorList {

    private $values = array();
    private $id;
    private $old_prefix = '';

    // Конструктор класса
 	public function __construct($obj_id = 0) {

    	if (!empty($obj_id) && is_numeric($obj_id)) {

    		$this->values = db::q('SELECT * FROM <<langs>> WHERE l_id = "'.$obj_id.'";', record);
    		$this->id = $obj_id;

    	} else if (is_array($obj_id) && !empty($obj_id['id']) && !empty($obj_id['l_name'])) {

            $this->values = $obj_id;
            $this->id = $obj_id['id'];

    	}

        if (isset($this->values['l_prefix']))
    		$this->old_prefix = $this->values['l_prefix'];

 	}

    // Вернет ID языковой версии сайта
 	public function id() {
    	return $this->id;
    }

    // Вернет название языковой версии
    public function getName() {
        if (isset($this->values['l_name']))
    		return $this->values['l_name'];
    }

    /**
	* @return null
	* @param string $name - Название языка
	* @desc Устанавливает название языковой версии
	*/
    public function setName($name) {
        $name = system::checkVar($name, isString);

        if (empty($name))
            $this->newError(49, 'Неправильно указано название языка!');
        else
    		$this->values['l_name'] = $name;
    }

    // Вернет префикс языковой версии
    public function getPrefix() {
        if (isset($this->values['l_prefix']))
    		return $this->values['l_prefix'];
    }

    // Вернет префикс для формирования ссылок, исходя из текущей языковой версии
	public function pre(){
		
		if ($this->id != domains::curDomain()->getDefLang())
			return '/'.$this->getPrefix();
		else
			return '';
	}

    /**
	* @return null
	* @param string $name - префикс
	* @desc Устанавливает префикс языковой версии
	*/
    public function setPrefix($name) {
        $name = system::checkVar($name, isVarName, 4);
        if (empty($name))
            $this->newError(50, 'Префикс языка указан в неправильном формате!');
        else {

        	$isset = db::q('SELECT count(l_id) FROM <<langs>>
        					WHERE l_prefix = "'.$name.'" and
        						  l_id <> "'.$this->id.'";', value);

        	if (empty($isset))
    			$this->values['l_prefix'] = $name;
    		else
    			$this->newError(51, 'Невозможно добавить язык с префиксом "'.$name.'". Язык с таким префиксом уже зарегистрирован в системе!');
    	}

    }

	// Сохраняет текущие изменения в БД
    public function save() {

        if (!$this->issetErrors() && (empty($this->values['l_name']) || empty($this->values['l_prefix'])))
        	$this->newError(52, 'Поля "Название" и "Префикс" обязательны для заполнения!');

        if ($this->issetErrors()) {

			return false;

    	} else if (!empty($this->id)) {

            // Изменение языка
    		$sql = $this->getSql();
            if (!empty($sql))
	    		db::q('UPDATE <<langs>> SET '.$sql.' WHERE l_id = "'.$this->id.'";');

            system::log('Изменена языковая версия сайта "'.$this->values['l_name'].'" (id:'.$this->id.')', warning);

            // Переименовываем папки с шаблонами
            if ($this->old_prefix != $this->values['l_prefix']){
	            $domain = domains::getAll();
				while(list($key, $val) = each($domain)) {
				    if ($this->id != 1 || $val['d_id'] != 1){
			       		$old_name = '/__'.str_replace('.', '_', $val['d_name']).'_'.$this->old_prefix;
			       		$new_name = '/__'.str_replace('.', '_', $val['d_name']).'_'.$this->values['l_prefix'];
						@rename(TEMPL_DIR.$old_name, TEMPL_DIR.$new_name);
					}
	            }
            }

            return true;

    	} else {

            // Добавление языка
            $sql = $this->getSql();
            if (!empty($sql))
            	$this->id = db::q('INSERT INTO <<langs>> SET '.$sql.';');

            if (is_numeric($this->id)) {

				// Добавляем для каждого домена право и шаблон
	   			$domain = domains::getAll();
				while(list($key, $val) = each($domain)) {

                    rights::createForStructure($val['id'], $this->id);

					$templ = new template();
		            $templ->setName('default');
		            $templ->setFile('default');
		            $templ->setLangId($this->id);
		            $templ->setDomainId($val['id']);
		            $templ->save();

		            reg::setKey('/structure/'.$val['id'].'/'.$this->id.'/title_prefix', '%text% | '.$val['d_sitename']);
		            reg::setKey('/structure/'.$val['id'].'/'.$this->id.'/view_as_tree', 1);

		            // Создаем папки с шаблонами
                	$dname = '/__'.str_replace('.', '_', $val['d_name']).'_'.$this->values['l_prefix'];
					$this->copyDir(TEMPL_DIR, TEMPL_DIR.$dname);
				}


                system::log('Создана новая языковая версия сайта "'.$this->values['l_name'].'" (id:'.$this->id.')', warning);
				return true;

			} else {
				$this->newError(53, 'Произошла ошибка при добавление языка!');
				system::log('Произошла ошибка при добавление языка!', error);
				return false;
			}
    	}
    }

    // Хитрая функция для копирования шаблонов
    private function copyDir($from_path, $to_path) {
		 @mkdir($to_path, 0777);
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
            	if ($name != 'l_id' && $name != 'id') {
	            	$zpt = (empty($sql)) ? '' : ', ';
	            	$sql .= $zpt.$name.' = "'.$value.'"';
            	}
     	return $sql;
    }

    // Удаление языковой версии сайта
    public function delete() {

    	if (!empty($this->id) && $this->id != 1) {

            $is_ok = true;

            // Удаляем права, настройки и страницы связанные с данным языком
            $domain = domains::getAll();
			while(list($key, $val) = each($domain))  {

                // Удаление страниц
    			$tmp = ormPages::delAllFor($val['id'], $this->id);

    			if (!$tmp)
    				$is_ok = $tmp;
    			else {

	                // Удаление настроек
	    			reg::delKey('/structure/'.$val['id'].'/'.$this->id);

	                // Удаление прав
	    			db::q('DELETE FROM <<modules_rights>>
	    				   WHERE mr_name = "'.$this->id.' '.$val['id'].'"
	    				   		 and mr_mod_id = "3";');
    			}
    		}

            // Удаляем сам язык
            if ($is_ok) {

                // У все доменов приязанных к текущему языку, меняем "язык по умолчанию".
	            $domain = db::q('SELECT d_id FROM <<domains>> WHERE d_def_lang = "'.$this->id.'";', records);
	            while(list($key, $val) = each($domain))
		            if (!empty($val['d_id'])){
			            $domain = domains::get($val['d_id']);
			            if ($domain instanceof domain) {
				            $domain->setDefLang(1);
				            $domain->save();
			            }
		            }

    			$is_ok = db::q('DELETE FROM <<langs>> WHERE l_id = "'.$this->id.'";');

    			if ($is_ok !== false) {
    			    system::log('Удалена языковая версия сайта "'.$this->values['l_name'].'" (id:'.$this->id.')!', warning);
    				return true;
    			} else {
    				system::log('Произошла ошибка при удалении языка "'.$this->values['l_name'].'" (id:'.$this->id.')!', error);
    				$this->newError(54, 'Произошла ошибка при удалении языка "'.$this->values['l_name'].'"!');
    			}

            } else {
            	system::log('Пользователь пытался удалить языковую версию "'.$this->values['l_name'].'" (id:'.$this->id.'), но у него не хватило прав на удаление всех страниц!', error);
            	$this->newError(55, 'Вы не можете удалить язык сайта "'.$this->values['l_name'].'", т.к. не имеете прав на удаление некоторых страниц!');
            }
    	}

    	return false;
    }

}

?>