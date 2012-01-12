<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс позволяет управлять свойствами ORM-классов.
*/

class ormClass extends innerErrorList {

    private $id, $parent_id, $name, $sname, $system, $is_list, $is_page, $padej, $base_class;
    private $old_sname, $old_system;
    private $groups = Array();

    private $fields = Array();
    private $fields_name = Array();
    private $fields_obj = Array();

    // Конструктор
    public function __construct($class_id = 0) {

        if (!empty($class_id))
            $this->Load($class_id);

    }

    // Загружаем данные указанного класса
    private function Load($id){

        if (!empty($id) && !is_array($id)) {

            $id = system::checkVar($id, isVarName);

            $sql = (is_numeric($id)) ? 'c_id = "'.$id.'"' : 'c_sname = "'.$id.'"';

            $row = db::q('SELECT * FROM <<classes>> WHERE '.$sql.';', values);

            if (!empty($row)) {

                $this->id = $row[0];
                $this->parent_id = $row[1];

                $this->name = $row[2];
                $this->sname = $row[3];

                $this->padej = $row[4];
                $this->base_class = $row[5];

                $this->system = $row[6];
                $this->old_system = $row[6];
                $this->is_list = $row[7];
                $this->is_page = $row[8];
                //$this->old_system = rand(100, 999);
                return;
            }

        } else if (is_array($id) && isset($id['c_id']) && isset($id['c_sname'])) {

            /*
            Загрузка данных в объект из другого источника (ormObject).
            Используется для уменьшения дублирующих запросов.
            */
            $row = $id;

            $this->id = $row['c_id'];
            $this->parent_id = $row['c_parent_id'];

            $this->name = $row['c_name'];
            $this->sname = $row['c_sname'];

            $this->system = $row['c_system'];
            $this->old_system = $row['c_system'];
            $this->is_list = $row['c_is_list'];
            $this->is_page = $row['c_is_page'];

            $this->base_class = $row['c_base_class'];
            $this->padej = $row['c_text'];

            //$this->old_system = rand(100, 999);
            return;

        }

        $this->newError(19, 'Невозможно загрузить данные класса!');

    }


    // Вернет ID ORM-класса
    public function id(){
        return $this->id;
    }

    // Возвращает ID родительского класса.
    public function getParentId(){
        return $this->parent_id;
    }

    // Возвращает экземпляр родительского ORM-класса.
    public function getParent(){
        return ormClasses::get($this->parent_id);
    }

    // Присвоить ID родительского класса (только при создании нового класса).
    public function setParentId($value){

        if (empty($this->id)) {
            $this->parent_id = system::checkVar($value, isInt);
            if ($this->parent_id === false)
                $this->newError(20, 'Неправильно указан ID родителя ORM-класса!');
        }
    }

    // Проверяет является ли данный ORM класс наследником указанного класса
    public function isInheritor($class_name) {

        if (!empty($this->id)) {

            if ($this->sname == $class_name || $this->id == $class_name)
                return true;
            else {

                $class = $this->getParent();
                if ($class instanceof ormClass)
                    return $class->isInheritor($class_name);
                else
                    return false;
            }

        } else return false;
    }

    /*
        Вернет список прямых наследников текущего класса
    */
    public function getInheritors() {
        return ormClasses::getInheritors($this->id);
    }

    // Скидывает индекс перебора списка наследников методом getInheritor()
    public function reset() {
        return ormClasses::resetFor($this->id);
    }

    /*
        Вернет следующий по порядку экземпляр наследника.
        Используется для перебора в цикле. Если наследников нет или
        они все перебранны - вернет FALSE.
    */
    public function getInheritor() {
        return ormClasses::getInheritor($this->id);
    }

    /**
     * @return array Список классов наследников
     * @param array $classes - Используется для организации рекурсии. Список классов наследников.
     * @desc Вернет список всех (прямых и косвенных) наследников текущего класса.
     */
    public function getAllInheritors($classes = array()) {

        if (!empty($this->id)) {
            $classes[$this->id] = $this->sname;
            $this->reset();
            while ($class = $this->getInheritor())
                $classes = $class->getAllInheritors($classes);
        } else $classes = array();

        return $classes;
    }

    // Возвращает Имя класса
    public function getName(){
        return $this->name;
    }

    public function setName($name){
        $this->name = db::specQuote(system::checkVar($name, isString));
        if ($this->name === false)
            $this->newError(21, 'Неправильно указано название класса!');
    }

    // Возвращает системное имя класса
    public function getSName(){
        return $this->sname;
    }

    public function setSName($name){

        if (!empty($this->id) && empty($this->old_sname))
            $this->old_sname = $this->sname;

        $this->sname = system::checkVar($name, isVarName, 50);
        if ($this->sname === false)
            $this->newError(22, 'Неправильно указано системное название класса!');
    }

    // Вернет 1, если класс является системным
    public function isSystem(){
        return $this->system;
    }

    public function setSystem($value){
        $this->system = system::checkVar($value, isBool);
    }

    // Вернет 1, если класс можно использовать как справочник
    public function isList(){
        return $this->is_list;
    }

    public function setIsList($value){
        $this->is_list = system::checkVar($value, isBool);
    }

    // Вернет ID класса по умолчанию для подразделов объектов данного класса
    public function getBaseClass(){
        return $this->base_class;
    }

    public function setBaseClass($value){
        $this->base_class = system::checkVar($value, isInt);
    }

    /**
     * @return string Название объекта
     * @param integer $num - Номер словоформы. Если не указывать вернет все склонения сразу.
    Если указать (от 0 до 6) вернет одно из слонений названия.
     * @desc Возвращает склонение названия объекта класса
     */
    public function getPadej($num = -1){
        if ($num >= 0){
            $padej = explode(',', str_replace('', '', $this->padej));
            if (isset($padej[$num]))
                return $padej[$num];
            else
                return lang::get('CONSTR_ADDTEXT_DEF');
        } else return $this->padej;
    }

    /**
     * @return null
     * @param string $value - Строка названий, каждое название отделено запятой.
     * @desc Присваивает строку склонений названий объектов класса
     */
    public function setPadej($value){
        $this->padej = db::specQuote(system::checkVar($value, isString));
        if ($this->padej === false)
            $this->newError(23, 'Неправильно указаны склонения названий класса!');
    }


    /*
             Вернет TRUE, если класс наследник класса "section"
             Этот метод работает быстрее чем метод isInheritor('section')
         */
    public function isPage(){
        return $this->is_page;
    }

    // Добавление или изменение класса данных
    public function save(){

        if (!empty($this->id) && $this->old_system)
            $this->newError(24, 'Класс является системным, вам запрещено его редактирование!');

        if ($this->issetErrors()) {

            // Указанные характеристики класса не соответствуют требованиям
            return false;

        } else if (!empty($this->id)) {

            // Изменяем свойства класса
            return $this->changeClass();

        } else {

            // Добавляем новый класс
            return $this->createClass();

        }

    }

    // Изменение класса
    private function changeClass(){

        if (!empty($this->old_sname) && $this->old_sname != $this->sname){

            // Проверяем на уникальность системное имя
            $sql =  'SELECT count(c_id)
	  					 FROM <<classes>>
	        			 WHERE c_sname = "'.$this->sname.'" and
	        			 		c_id <> "'.$this->id.'";';

            if (db::q($sql, value) == 0) {

                $tmp = db::q('ALTER TABLE <<__'.$this->old_sname.'>> RENAME <<__'.$this->sname.'>>;');

                if ($tmp === false)  {

                    $this->newError(25, 'Невозможно переименовать таблицу данных класса!');
                    return false;

                }

            } else {

                $this->newError(26, 'Класс с указанным системным именем уже существует в системе!');
                return false;

            }
        }

        $sql = (!empty($this->base_class)) ? ', c_base_class = "'.$this->base_class.'"' : ', c_base_class = NULL';


        // Изменяем данные о классе     -		-		-		-		-		-		-		-
        $sql = 'UPDATE <<classes>>
					SET c_name = "'.$this->name.'",
						c_sname = "'.$this->sname.'",
						c_system = "'.$this->system.'",
						c_is_list = "'.$this->is_list.'",
						c_text = "'.$this->padej.'"'.$sql.'
					WHERE c_id = "'.$this->id.'";';

        if(db::q($sql) !== false) {

            // Обновляем данные у классов наследников
            while($obj = ormClasses::getInheritor($this->id)) {
                $obj->setIsList($this->is_list);
                $obj->save();
            }


            // Присваиваем атрибут системный для всех групп и полей класса
            if ($this->system && !$this->old_system) {
                $groups = $this->getAllGroups();
                if (!empty($groups))
                    while(list($key, $val) = each($groups)) {
                        $obj = new ormFieldsGroup($val['fg_id']);
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

    // Создание нового класса    -		-		-		-		-		-		-		-
    private function createClass(){

        $sql =  'SELECT count(c_id)
  					 FROM <<classes>>
        			 WHERE c_sname = "'.$this->sname.'";';

        if (db::q($sql, value) == 0) {

            $nul = ($this->base_class == NULL) ? '' : 'c_base_class = "'.$this->base_class.'",';
            $nul .= ($this->parent_id == NULL) ? '' : 'c_parent_id = "'.$this->parent_id.'",';

            $parent = $this->getParent();
            $this->is_page = ($parent instanceof ormClass) ? $parent->isPage() : 0;

            $sql = 'INSERT INTO <<classes>>
						SET c_name = "'.$this->name.'",
							c_sname = "'.$this->sname.'",
							'.$nul.'
							c_text = "'.$this->padej.'",
							c_system = "'.$this->system.'",
							c_is_list = "'.$this->is_list.'",
							c_is_page = "'.$this->is_page.'";';

            $this->id = db::q($sql);

            if ($this->id !== false){

                $sql = 'CREATE TABLE <<__'.$this->sname.'>> (
							`obj_id` INT( 11 ) NOT NULL
							) ENGINE=InnoDB;';

                if(db::q($sql) !== false) {

                    db::q('ALTER TABLE <<__'.$this->sname.'>>
                         		ADD CONSTRAINT <<__'.$this->sname.'_fk_obj_id>>
                         		FOREIGN KEY (`obj_id`)
                         		REFERENCES <<objects>> (`o_id`)
                         		ON DELETE CASCADE ON UPDATE CASCADE');

                    // Регистрация класса в коллекции
                    ormClasses::registration($this);

                    // Наследует все поля родительского класса
                    if ($parent instanceof ormClass) {

                        $groups = $parent->getAllGroups();

                        if (!empty($groups))
                            while(list($key, $gval) = each($groups)) {

                                $parentGroup = new ormFieldsGroup($gval['fg_id']);

                                $group = clone $parentGroup;
                                $group->setClassId($this->id);
                                $gid = $group->save();

                                if ($gid !== false) {

                                    $fields = $parentGroup->getAllFields(1);
                                    while(list($key, $fval) = each($fields)) {

                                        $field = clone new ormField($fval['f_id']);
                                        $field->setGroupId($gid);
                                        $field->save();
                                    }
                                }
                            }

                    } else {

                        // Класс сам по себе, создаем группу и поле по умолчанию

                        $group = new ormFieldsGroup();
                        $group->setName('Основное');
                        $group->setSName('base');
                        $group->setView(1);
                        $group->setSystem(1);
                        $group->setClassId($this->id);
                        $gid = $group->save();

                        if ($gid !== false) {
                            $field = new ormField();
                            $field->setName('Название');
                            $field->setSName('name');
                            $field->setType(10);
                            $field->setView(1);
                            $field->setSearch(0);
                            $field->setInherit(1);
                            $field->setFilter(0);
                            $field->setRequired(1);
                            $field->setSystem(0);
                            $field->setUniqum(0);
                            $field->setGroupId($gid);
                            $field->save();
                        }
                    }


                    return $this->id;

                } else {
                    $this->newError(27, 'Невозможно создать таблицу данных класса!');
                    return false;
                }

            } else {
                $this->newError(12, 'Ошибка в SQL запросе!');
                return false;
            }


        } else {

            $this->newError(26, 'Класс с указанным системным именем уже существует в системе!');
            return false;

        }

    }

    // Удаление класса данных       -		-		-		-		-		-		-		-
    public function delete(){

        if ($this->old_system) {

            $this->newError(24, 'Класс является системным, вам запрещено его редактирование!');
            return false;

        } else if (!empty($this->id)) {

            // Удаляем все объекты созданные на основе этого класса
            $sel = new ormSelect($this->sname);
            $sel->fields('id');
            //$sel->where('parents', '=', 0);
            while($obj = $sel->getObject())
                $obj->delete();

            // Удаляем SQL-таблицы всех наследников класса
            while($class = $this->getInheritor())
                $class->delete();

            // Убираем связь удаляемого класса с другими классами
            db::q('UPDATE <<classes>> SET c_base_class = NULL WHERE c_base_class = "'.$this->id.'";');

            $ret = db::q('DELETE FROM <<classes>> WHERE c_id = "'.$this->id.'";');

            if($ret !== false) {

                db::q('DROP TABLE IF EXISTS <<__'.$this->sname.'>>;');
                return true;

            } else {
                $this->newError(12, 'Ошибка в SQL запросе!');
                return false;
            }

        } else return false;

    }

    // *****************************************



    // Вернет массив полей для текущего класса
    public function loadFields() {

        if (!empty($this->id)) {

            if (empty($this->fields)) {

                /*f_id, f_name, f_sname, f_hint, f_type, f_list_id, f_view, f_search,
                                    f_filter, f_required, f_quick_add, f_uniqum, f_max_size, fg_id, fg_name, fg_sname, fg_view*/
                $sql =  '/* Подгрузка полей */
	                		SELECT *
			  				 FROM <<fgroup>>, <<fields>>
			        		 WHERE fg_class_id = "'.$this->id.'" and
			        		 	   fg_id = f_group_id
			        		 ORDER BY fg_position, f_position;';

                $ret = db::q($sql, records, 0);

                if ($ret !== false) {

                    $this->fields = array();
                    while (list($key, $value) = each ($ret)) {

                        if (!empty($value['f_type']))
                            $this->fields[$value['f_sname']] = $value;
                        else
                            $this->fields[$value['f_id']] = $value;

                        $this->fields_name[$value['f_id']] = $value['f_sname'];
                    }

                    // Типа флага, что бы не делать по несколько запросов к БД, если у класса нет полей.
                    if (empty($this->fields))
                        $this->fields['id'] = 1;

                } else return false;

            }

            return $this->fields;

        } else return false;
    }

    // Вернет массив Специальных полей
    public function loadSpecFields() {

        if (!empty($this->id)) {

            if (empty($this->spec_fields)) {

                $this->loadFields();
                $this->spec_fields = array();
                while (list($key, $value) = each ($this->fields)) {
                    if ($value['f_spec'] == 1)
                        if (!empty($value['f_type']))
                            $this->spec_fields[$value['f_sname']] = $value;
                        else
                            $this->spec_fields[$value['f_id']] = $value;
                }
            }

            return $this->spec_fields;

        } else return false;
    }

    // Вернет TRUE, если указанное поле существует
    function issetField($name){
        $this->loadFields();
        return (isset($this->fields[$name]));
    }

    // Вернет объект ormField по системному имени поля
    function getField($sname){

        $field = false;

        if (!empty($sname) && !is_array($sname)) {

            $this->loadFields();

            // Проверяем переданные данные, определяем ID
            if (is_numeric($sname)) {
                $id = system::checkVar($sname, isInt);
                if (array_key_exists($id, $this->fields_name))
                    $sname = $this->fields_name[$id];
            } else if (!is_array($sname)) {
                $sname = system::checkVar($sname, isVarName);
            }

            // Загрузка поля
            if (isset($this->fields_obj[$sname]))
                $field = $this->fields_obj[$sname];
            else if (isset($this->fields[$sname])) {
                $field = new ormField($this->fields[$sname]);
                if (!$field->issetErrors())
                    $this->fields_obj[$sname] = $field;
                else $field = false;
            }
        }

        if ($field)
            return $field;
    }



    // Вспомогательные функции для работы с ПОЛЯМИ    --------------------------

    // Вернет тип поля, в том числе для виртуальных полей
    function getFieldType($name){
        if ($name == 'create_date' || $name == 'change_date')
            return 32;
        else {
            $this->loadFields();
            if (isset($this->fields[$name]))
                return $this->fields[$name]['f_type'];
        }
    }

    // Вернет имя поля, в том числе для виртуальных полей
    function getFieldName($name){
        if ($name == 'id')
            return 'Номер объекта';
        else if ($name == 'change_date')
            return 'Дата изменения';
        else if ($name == 'create_date')
            return 'Дата создания';
        else if ($name == 'name')
            return 'Имя';
        else {
            $this->loadFields();
            if (isset($this->fields[$name]))
                return $this->fields[$name]['f_name'];
        }
    }


    // Вернет все поля класса. Оставлен для совместимости, пока не можем отказаться от этого метода.
    function getAllFields($inherit = 0){

        if (!empty($this->id)) {

            $inherit = (!empty($inherit)) ? ' and f_inherit = 1' : '';

            $sql =  '/* Загрузка всех полей */
		        		SELECT f_id, f_group_id, f_name, f_sname, f_type, f_view, f_required, f_system, f_is_clone
		  				 FROM <<fgroup>>, <<fields>>
		        		 WHERE fg_class_id = "'.$this->id.'" and
		        		 		fg_id = f_group_id '.$inherit.'
		        		 ORDER BY fg_position, f_position;';

            return db::q($sql, records);

        } return false;
    }






    // Вспомогательные функции для работы с ГРУППАМИ    --------------------------

    // Вернет список групп класса
    function getAllGroups(){

        if (!empty($this->id)) {

            $sql =  'SELECT fg_id, fg_name, fg_sname, fg_view, fg_system, fg_is_clone
		  				 FROM <<fgroup>>
		        		 WHERE fg_class_id = "'.$this->id.'"
		        		 ORDER BY fg_position;';

            return db::q($sql, records);

        } return false;
    }

    // Вернет объект ormFieldsGroup или его ID по системному названию группы
    function getGroupBySName($name, $only_id = false){
        $sname = system::checkVar($name, isVarName);

        if ($sname !== false) {
            $sql =  'SELECT fg_id FROM <<fgroup>>
		        		 WHERE fg_class_id = "'.$this->id.'" and fg_sname="'.$sname.'";';

            $id = db::q($sql, value);
            if ($id !== false){
                if (!$only_id)
                    return new ormFieldsGroup($id);
                else
                    return $id;
            } else return false;
        }

    }


}

?>