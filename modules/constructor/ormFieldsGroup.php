<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс для работы с группами полей ORM-классов
*/

class ormFieldsGroup extends innerErrorList {

    private $id, $class_id, $name, $sname, $view, $system, $position, $is_clone;
    private $old_system, $old_pos, $old_sname, $inher_flag = false;
    private $fields = Array();

    public function __construct($id = '') {

        $this->is_clone = 0;
        if (!empty($id))
            $this->Load($id);

    }

    private function Load($id){

        $id = system::checkVar($id, isInt);

        if ($id !== false) {

            $row = db::q('SELECT * FROM <<fgroup>> WHERE fg_id = "'.$id.'";', values);

            if (!empty($row)) {

                $this->id = $id;
                $this->old_sname = $this->sname;

                $this->class_id = $row[1];
                $this->position = $row[2];
                $this->name = $row[3];
                $this->sname = $row[4];
                $this->view = $row[5];
                $this->system = $row[6];
                $this->old_system = $row[6];
                $this->is_clone = $row[7];
            }
        }
    }

    // Поддержка наследования групп полей.
    public function __clone(){

        $this->id = 0;
        $this->class_id = 0;
        $this->system = 0;
        $this->is_clone = 1;
        //echo " this->id = ".$this->id."<br />";
    }


    // Вернет ID группы
    public function id(){
        return $this->id;
    }

    // Вернет ID класса в который входит группа полей
    public function getClassId(){
        return $this->class_id;
    }

    // Вернет экземпляр класса в который входит группа полей
    public function getClass(){
        return ormClasses::get($this->class_id);
    }

    // Присваивает ID родительского класса (только для режима добавления группы).
    public function setClassId($value){

        if (empty($this->id)) {
            $this->class_id = system::checkVar($value, isInt);
            if ($this->class_id === false)
                $this->newError(13, 'Неправильно указан ID ORM-класса!');
        }
    }

    // Устанавливаем позицию для группы
    public function setPosition($val){

        $this->old_pos = $this->position;
        $this->position = system::checkVar($val, isInt);
        if ($this->position === false)
            $this->newError(14, 'Неправильное значение позиции группы в списке!');
    }

    // Человеческое название группы
    public function getName(){
        return $this->name;
    }

    public function setName($name){

        $this->name = db::specQuote(system::checkVar($name, isString));
        if ($this->name === false)
            $this->newError(15, 'Неправильно указано название группы!');

        $this->inher_flag = true;
    }

    // Системное название группы
    public function getSName(){
        return $this->sname;
    }

    public function setSName($name){

        if (!empty($this->id) && empty($this->old_sname))
            $this->old_sname = $this->sname;

        $this->sname = system::checkVar($name, isVarName, 50);
        if ($this->sname === false)
            $this->newError(16, 'Неправильно указано системное имя группы!');

        $this->inher_flag = true;
    }

    // Видимость группы
    public function getView(){
        return $this->view;
    }

    public function setView($val){
        $this->view = system::checkVar($val, isBool);
        $this->inher_flag = true;
    }

    // Свойство "системный"
    public function getSystem(){
        return $this->system;
    }

    public function setSystem($val){
        $this->system = system::checkVar($val, isBool);
    }

    // Вернет TRUE, если группа является наследником другой группы
    public function isClone(){
        return $this->is_clone;
    }


    // Добавление или изменение группы полей
    public function save(){

        if (!empty($this->id) && $this->old_system && $this->inher_flag)
            $this->newError(17, 'Группа является системной, вам запрещено ее редактирование!');


        if ($this->issetErrors()) {

            // Указанные характеристики группы не соответствуют требованиям
            return false;

        } else if (!empty($this->id)) {

            // Изменяем свойства группы
            return $this->changeGroup();

        } else {

            // Добавляем новую группу
            return $this->createGroup();

        }

    }

    // Изменение группы полей         -		-		-		-		-		-		-		-
    private function changeGroup(){

        // Проверяем на уникальность системное имя
        if (!empty($this->tmp_sname) && $this->tmp_sname != $this->sname){

            $sql =  'SELECT count(fg_id)
	  					 FROM <<fgroup>>
	        			 WHERE fg_sname = "'.$this->sname.'" and
	        			 	   fg_id <> "'.$this->id.'" and
	        			 	   fg_class_id = "'.$this->class_id.'";';

            if (db::q($sql, value) === false) {

                $this->newError(18, 'Группа полей с указанным системным именем уже существует в данном классе!');
                return false;
            }
        }

        // Вычисляяем позицию группы
        if (!empty($this->position)) {

            if (!empty($this->old_pos)) {
                if ($this->position < $this->old_pos) {

                    db::q('UPDATE <<fgroup>> SET fg_position = fg_position + 1
	                           WHERE fg_position >= "'.$this->position.'" and
	                                 fg_position < "'.$this->old_pos.'" and
	                                 fg_class_id = "'.$this->class_id.'";');

                } else {

                    db::q('UPDATE <<fgroup>> SET fg_position = fg_position - 1
	                            WHERE fg_position <= "'.$this->position.'" and
	                                  fg_position > "'.$this->old_pos.'" and
	                                  fg_class_id = "'.$this->class_id.'";');
                }
            }

            $sql_pos = ', fg_position = "'.$this->position.'"';
            $sql_pos2 = 'fg_position = "'.$this->position.'"';

        } else $sql_pos = $sql_pos2 = '';


        // Изменяем данные
        if ($this->old_system && !empty($sql_pos2))

            $sql = 'UPDATE <<fgroup>> SET '.$sql_pos2.'
						WHERE fg_id = "'.$this->id.'";';

        else if (!$this->old_system && $this->inher_flag)

            $sql = 'UPDATE <<fgroup>>
						SET fg_name = "'.$this->name.'",
							fg_sname = "'.$this->sname.'",
							fg_view = "'.$this->view.'",
							fg_system = "'.$this->system.'" '.$sql_pos.'
						WHERE fg_id = "'.$this->id.'";';

        else if (!$this->old_system && !$this->inher_flag)

            $sql = 'UPDATE <<fgroup>>
						SET fg_system = "'.$this->system.'" '.$sql_pos.'
						WHERE fg_id = "'.$this->id.'";';



        if(!empty($sql) && db::q($sql) !== false) {

            // Обновляем данные у классов наследников
            if ($this->inher_flag){

                while($obj = $this->getClass()->getInheritor()) {

                    $sname = (!empty($this->old_sname)) ? $this->old_sname : $this->sname;
                    $group = $obj->getGroupBySName($sname);

                    if ($group->id() != '') {
                        $group->setName($this->name);
                        $group->setSName($this->sname);
                        $group->setView($this->view);
                        $group->save();
                    }
                }
            }

            // Присваиваем атрибут системный для всех полей группы
            if ($this->system && !$this->old_system) {

                $fields = $this->getAllFields();
                if (!empty($fields))
                    while(list($key, $val) = each($fields)) {
                        $obj = new ormField($val['f_id']);
                        $obj->setSystem(1);
                        $obj->save();
                    }
            }

            return $this->id;

        } else {
            $this->newError(12, 'Ошибка в SQL запросе!');
            return false;
        }


    }

    // Создание группы полей        -		-		-		-		-		-		-		-
    private function createGroup(){

        $sql =  'SELECT count(fg_id)
  					 FROM <<fgroup>>
        			 WHERE fg_sname = "'.$this->sname.'" and
	        			   fg_class_id = "'.$this->class_id.'";';

        if (db::q($sql, value) == 0) {

            $nul = ($this->class_id == NULL) ? '' : 'fg_class_id = "'.$this->class_id.'",';

            $sql =  'SELECT MAX(fg_position) FROM <<fgroup>>
	        			 WHERE fg_class_id = "'.$this->class_id.'";';

            $pos = db::q($sql, value) + 1;

            $sql = 'INSERT INTO <<fgroup>>
						SET fg_name = "'.$this->name.'",
							fg_sname = "'.$this->sname.'",
							fg_position = "'.$pos.'",
							'.$nul.'
							fg_view = "'.$this->view.'",
							fg_is_clone = "'.$this->is_clone.'",
							fg_system = "'.$this->system.'";';

            $this->id = db::q($sql);

            if ($this->id == false) {

                $this->newError(12, 'Ошибка в SQL запросе!');
                return false;

            } else {

                // Копируем группы в классы наследники
                //  echo $this->getClass()->id();
                while($obj = ormClasses::getInheritor($this->class_id)) {
                    $group = clone $this;
                    $group->setClassId($obj->id());
                    $group->save();
                }

                return $this->id;
            }

        } else {

            $this->newError(18, 'Группа полей с указанным системным именем уже существует в данном классе!');
            return false;

        }

    }

    // Удаление группы полей        -		-		-		-		-		-		-		-
    public function delete(){

        if ($this->old_system) {

            $this->newError(17, 'Группа является системной, вам запрещено ее редактирование!');
            return false;

        } else if (!empty($this->id)) {

            // Удаляем аналогичные группы у наследников
            while($obj = $this->getClass()->getInheritor()) {
                $group = $obj->getGroupBySName($this->sname);
                $group->delete();
            }

            // Удаляем вместе с полями группы SQL-поля привязанной таблицы
            $fields = db::q('SELECT f_id FROM <<fields>> WHERE f_group_id = "'.$this->id.'";', records);
            while(list($key, $field) = each($fields)) {
                $obj = new ormField($field['f_id']);
                $obj->delete();
            }

            $res = db::q('DELETE FROM <<fgroup>> WHERE fg_id = "'.$this->id.'";');

            if ($res == false) {
                $this->newError(12, 'Ошибка в SQL запросе!');
                return false;
            } else return true;

        } else return false;

    }

    // Получить все поля группы. Нужно избавиться от этого метода...
    public function getAllFields($inherit = 0){

        if (!empty($this->id)) {

            $inherit = (!empty($inherit)) ? ' and f_inherit = 1' : '';

            $sql =  'SELECT f_id FROM <<fields>>
		        		 WHERE f_group_id = "'.$this->id.'" '.$inherit.'
		        		 ORDER BY f_position;';

            return db::q($sql, records);

        } return false;
    }


}

?>