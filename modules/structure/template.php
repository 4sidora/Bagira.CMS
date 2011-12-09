<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс для управления свойствами шаблонов сайта.
*/

class template extends innerErrorList {

    private $values = array();
    private $id;

 	public function __construct($obj_id = 0) {

    	if (!empty($obj_id) && is_numeric($obj_id)) {

    		$this->values = db::q('SELECT * FROM <<template>> WHERE t_id = "'.$obj_id.'";', record);
    		$this->id = $obj_id;

    	} else if (is_array($obj_id) && !empty($obj_id['id']) && !empty($obj_id['t_name'])) {

            $this->values = $obj_id;
            $this->id = $obj_id['id'];
    	}

 	}

    // Вернет ID шаблона
 	public function id() {
    	return $this->id;
    }

    // Имя шаблона
    public function getName() {
        if (isset($this->values['t_name']))
    		return $this->values['t_name'];
    }

    public function setName($name) {
        $name = system::checkVar($name, isString);

        if (empty($name))
            $this->newError(64, 'Неправильно указано название шаблона!');
        else
    		$this->values['t_name'] = $name;
    }

    // Имя файла шаблона
    public function getFile() {
        if (isset($this->values['t_file']))
    		return $this->values['t_file'];
    }

    public function setFile($name) {
        $name = system::checkVar($name, isVarName);
        if (empty($name))
            $this->newError(65, 'Имя файла шаблона указано в неправильном формате!');
        else
    		$this->values['t_file'] = $name;
    }

    // Тип (назначение) шаблона
    public function getDestination() {
        if (isset($this->values['t_type']))
    		return $this->values['t_type'];
    }

    /**
	* @return null
	* @param boolean $value - Назначение шаблона:
			0	-	шаблон для страниц
			1	-	шаблон для объектов
	* @desc Устанавливает тип назначения шаблона
	*/
    public function setDestination($value) {
        $this->values['t_type'] = system::checkVar($value, isBool);
    }

    // Языковая версия к которой привязан шаблон
    public function getLangId() {
        if (isset($this->values['t_lang_id']))
    		return $this->values['t_lang_id'];
    }

    /**
	* @return null
	* @param integer $id - ID языковой версии
	* @desc Привязывает шаблон к указанной языковой версии сайта
	*/
    public function setLangId($id) {
        $name = system::checkVar($id, isInt);
        if (empty($name))
            $this->newError(66, 'Не указана языковая версия для шаблона!');
        else
    		$this->values['t_lang_id'] = $name;
    }

    // Домен к которому привязан шаблон
    public function getDomainId() {
        if (isset($this->values['t_domain_id']))
    		return $this->values['t_domain_id'];
    }

    /**
	* @return null
	* @param integer $id - ID домена
	* @desc Привязывает шаблон к указанному домену
	*/
    public function setDomainId($id) {
        $name = system::checkVar($id, isInt);
        if (empty($name))
            $this->newError(67, 'Не указан домен для шаблона!');
        else
    		$this->values['t_domain_id'] = $name;
    }

    // Сохранить \ добавить шаблон
    public function save() {

        if (empty($this->values['t_name']) || empty($this->values['t_file']))
        	$this->newError(68, 'Поля "Название шаблона" и "Файл шаблона" обязательны для заполнения!');

        if (empty($this->values['t_domain_id']) || empty($this->values['t_lang_id']))
        	$this->newError(69, 'Для шаблона не были определены языковая версия или домен!');

        if ($this->issetErrors()) {

			return false;

    	} else if (!empty($this->id)) {

            // Изменение языка
    		$sql = $this->getSql();
            if (!empty($sql))
	    		db::q('UPDATE <<template>> SET '.$sql.' WHERE t_id = "'.$this->id.'";');

       		return $this->id;

    	} else {

            // Добавление языка
            $sql = $this->getSql();
            if (!empty($sql))
            	$this->id = db::q('INSERT INTO <<template>> SET '.$sql.';');

            if (is_numeric($this->id)) {

				return $this->id;

			} else {
				$this->newError(70, 'Произошла ошибка при добавлении шаблона!');
				return false;
			}
    	}
    }

    private function getSql(){
    	$sql = '';
    	if (!empty($this->values))
            while(list($name, $value) = each($this->values))
            	if ($name != 't_id' && $name != 'id') {
	            	$zpt = (empty($sql)) ? '' : ', ';
	            	$sql .= $zpt.$name.' = "'.$value.'"';
            	}
     	return $sql;
    }

    // Удалить шаблон
    public function delete() {

    	if (!empty($this->id) && $this->values['t_file'] != 'default') {

            // Убираем связи страниц с удалаяемым шаблоном

            $field_name = ($this->values['t_type'] == 1) ? 'template2_id' : 'template_id';
            $field_val = ($this->values['t_type'] == 1) ? 'Null' : '1';

            $list = db::q('SELECT p_obj_id id FROM <<pages>> WHERE '.$field_name.' = "'.$this->id.'";', records);

            while(list($num, $val) = each($list))
                db::q('UPDATE <<pages>> SET '.$field_name.' = '.$field_val.' WHERE p_obj_id = "'.$val['id'].'";');


    		db::q('DELETE FROM <<template>> WHERE t_id = "'.$this->id.'";');
    	}
    }
}

?>