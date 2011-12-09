<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс для работы с полям ORM-классов
*/

class ormField extends innerErrorList {

    private $id, $group_id, $copy_to, $class_id, $is_clone, $position;
    private $name, $sname, $hint, $type, $list_id, $view, $search, $inherit, $filter, $required, $system, $uniqum, $quick_add, $max_size, $relation, $spec;

    private $old_pos, $old_type, $old_sname, $old_group_id, $old_system, $inher_flag = false;

    // Список слов, которые запрещенно использовать в качестве системного названия поля
    private $swear_words = array('id', 'obj_id', 'class_id', 'create_date', 'change_date', 'parent', 'parents');
    // Список названий полей, специфичных для наследников ORM-классa "section". Эти поля только для страниц.
    private $page_fields = array('active', 'is_home_page', 'view_in_menu', 'view_submenu', 'in_search', 'in_index', 'title', 'h1',
                                 'pseudo_url', 'keywords', 'description', 'img_h1', 'img_act', 'img_no_act', 'in_new_window', 'other_link', 'order_by', 'number_of_items');


    // Создаем поле
    public function __construct($id = 0) {

        $this->is_clone = 0;
        if (!empty($id))
            $this->Load($id);
    }

    private function Load($id){

        if (is_array($id) && isset($id['f_id']) && isset($id['f_name']) && isset($id['f_sname'])) {

            $row = $id;

            $this->id = $row['f_id'];

            $this->group_id = $row['f_group_id'];
            $this->position = $row['f_position'];

            $this->name = $row['f_name'];
            $this->sname = $row['f_sname'];
            $this->hint = $row['f_hint'];

            $this->type = $row['f_type'];
            $this->list_id = $row['f_list_id'];
            $this->max_size = $row['f_max_size'];

            $this->view = $row['f_view'];
            $this->search = $row['f_search'];
            $this->inherit = $row['f_inherit'];
            $this->filter = $row['f_filter'];
            $this->required = $row['f_required'];
            $this->system = $row['f_system'];
            $this->old_system = $row['f_system'];
            $this->is_clone = $row['f_is_clone'];

            $this->uniqum = $row['f_uniqum'];
            $this->quick_add = $row['f_quick_add'];
            $this->relation = $row['f_relation'];
            $this->spec = $row['f_spec'];

            $this->class_id = $row['fg_class_id'];

        } else {


            $id = system::checkVar($id, isInt);

            if ($id !== false) {

                $row = db::q('SELECT * FROM <<fields>>, <<fgroup>>
								WHERE f_id = "'.$id.'" and f_group_id = fg_id;', record);

                if (!empty($row)) {

                    $this->id = $id;

                    $this->group_id = $row['f_group_id'];
                    $this->position = $row['f_position'];

                    $this->name = $row['f_name'];
                    $this->sname = $row['f_sname'];
                    $this->hint = $row['f_hint'];

                    $this->type = $row['f_type'];
                    $this->list_id = $row['f_list_id'];
                    $this->max_size = $row['f_max_size'];

                    $this->view = $row['f_view'];
                    $this->search = $row['f_search'];
                    $this->inherit = $row['f_inherit'];
                    $this->filter = $row['f_filter'];
                    $this->required = $row['f_required'];
                    $this->system = $row['f_system'];
                    $this->old_system = $row['f_system'];
                    $this->is_clone = $row['f_is_clone'];
                    $this->spec = $row['f_spec'];

                    $this->uniqum = $row['f_uniqum'];
                    $this->quick_add = $row['f_quick_add'];
                    $this->relation = $row['f_relation'];
                    $this->class_id = $row['fg_class_id'];

                }
            }
        }
    }

    // C помощью этого метода происходит копирование полей для классов наследников
    public function __clone(){

        if (!empty($this->copy_to)){

            $row = db::q('SELECT f_group_id, fg_class_id FROM <<fields>>, <<fgroup>>
							WHERE f_id = "'.$this->copy_to.'" and f_group_id = fg_id;', values);

            if (!empty($row)) {
                $this->id = $this->copy_to;
                $this->group_id = $row[0];
                $this->class_id = $row[1];
                $this->position = 0;
                $this->copy_to = 0;
            }

        } else {

            $this->id = 0;
            $this->group_id = 0;
            $this->class_id = 0;
        }

        $this->system = 0;
        $this->is_clone = 1;
    }

    /**
     * @return null
     * @param integer $field_id - ID поля в которое нужно скопировать данные текущего поля.
     * @desc Копирует данные текущего поля в указанное поле. Используется для организации механизма наследования.
     */
    public function copyTo($field_id){
        $this->copy_to = system::checkVar($field_id, isInt);
        $field = clone $this;
        $field->save();
    }


    // Вернет ID текущего поля
    public function id(){
        return $this->id;
    }

    // Вернет объект ormFieldsGroup, группы в которую входит текущее поле
    public function getGroup(){
        return new ormFieldsGroup($this->group_id);
    }

    // Вернет ID группы в которую входит текущее поле
    public function getGroupId(){
        return $this->group_id;
    }

    public function setGroupId($value){

        if ($this->group_id != $value)
            $this->old_group_id = $this->group_id;

        $this->group_id = system::checkVar($value, isInt);
        if ($this->group_id === false)
            $this->newError(1, 'Неправильно указан ID группы полей!');
    }

    // Получить ID Класса
    public function getClassId(){
        if (empty($this->class_id))
            $this->class_id = $this->getGroup()->getClassId();
        return $this->class_id;
    }

    // Вернет экземпляр класса ormClass
    public function getClass(){
        if (empty($this->class_id))
            $this->class_id = $this->getGroup()->getClassId();
        return ormClasses::get($this->class_id);
    }


    // Устанавливает позицию вывода поля
    public function setPosition($val){
        $this->old_pos = $this->position;
        $this->position = system::checkVar($val, isInt);
        if ($this->position === false)
            $this->newError(2, 'Неправильное значение позиции поля!');
    }

    // Название поля
    public function getName(){
        return $this->name;
    }

    public function setName($name){
        $this->name = db::specQuote(system::checkVar($name, isString));
        if ($this->name === false)
            $this->newError(3, 'Неправильно указано значение названия поля!');

        $this->inher_flag = true;
    }

    // Системное название поля
    public function getSName(){
        return $this->sname;
    }

    public function setSName($name){

        if (!empty($this->id) && empty($this->old_sname))
            $this->old_sname = $this->sname;

        if ($this->old_sname != 'name')	{

            $this->sname = system::checkVar($name, isVarName, 50);
            if ($this->sname === false)
                $this->newError(4, 'Неправильно указано значение системного названия поля!');
            else if (in_array($this->sname, $this->swear_words))
                $this->newError(10, 'Запрещено использовать данное системное имя для поля!');

            $this->inher_flag = true;
        }
    }

    // Вернет TRUE, если поле является наследником другого поля
    public function isClone(){
        return $this->is_clone;
    }

    // Текст подсказки
    public function getHint(){
        return $this->hint;
    }

    public function setHint($name){
        $this->hint = db::specQuote(system::checkVar($name, isString));
        if ($this->hint === false)
            $this->newError(5, 'Неправильно указано значение подсказки для поля!');

        $this->inher_flag = true;
    }

    // Тип поля
    public function getType(){
        return $this->type;
    }

    public function setType($val){

        if ($this->isNameField())
            if ($val > 20) $val = 10;

        if (!empty($this->id) && empty($this->old_type))
            $this->old_type = $this->type;

        $this->type = system::checkVar($val, isInt);
        if ($this->type === false)
            $this->newError(6, 'Неправильно указан тип поля!');

        $this->inher_flag = true;
    }

    // ID справочника, если есть. Ссылка на ORM-класс для полей списочного типа.
    public function getListId(){
        return $this->list_id;
    }

    public function setListId($val){
        $this->list_id = system::checkVar($val, isInt);
        if (empty($this->list_id))
            $this->list_id = 'NULL';
        $this->inher_flag = true;
    }

    // Максимальный размер
    public function getMaxSize(){
        return $this->max_size;
    }

    public function setMaxSize($val){
        $this->max_size = system::checkVar($val, isInt);
        $this->inher_flag = true;
    }

    // Видимость
    public function getView(){
        return $this->view;
    }

    public function setView($value){
        $this->view = system::checkVar($value, isBool);
        $this->inher_flag = true;
    }

    // Участие в поиске
    public function getSearch(){
        return $this->search;
    }

    public function setSearch($value){
        $this->search = system::checkVar($value, isBool);
        $this->inher_flag = true;
    }

    // Наследование
    public function getInherit(){
        return $this->inherit;
    }

    public function setInherit($value){
        $this->inherit = system::checkVar($value, isBool);
        $this->inher_flag = true;
    }

    // Участие в фильтрах
    public function getFilter(){
        return $this->filter;
    }

    public function setFilter($value){
        $this->filter = system::checkVar($value, isBool);
        $this->inher_flag = true;
    }


    // Обязательность заполения поля
    public function getRequired(){
        return $this->required;
    }

    public function setRequired($value){
        $this->required = system::checkVar($value, isBool);
        $this->inher_flag = true;
    }

    // Системное
    public function getSystem(){
        return $this->system;
    }

    public function setSystem($value){
        $this->system = system::checkVar($value, isBool);
    }


    // Уникальность значения
    public function getUniqum(){
        return $this->uniqum;
    }

    public function setUniqum($value){
        $this->uniqum = system::checkVar($value, isBool);
        $this->inher_flag = true;
    }

    // Быстрое добавление для полей справочников
    public function getQuickAdd(){
        return $this->quick_add;
    }

    public function setQuickAdd($value){
        $this->quick_add = system::checkVar($value, isBool);
        $this->inher_flag = true;
    }

    // Тип отношения для справочников
    public function getRelType(){
        return $this->relation;
    }

    public function setRelType($value){
        $this->relation = system::checkVar($value, isInt);

        if ($this->relation > 2)
            $this->relation = 0;

        $this->inher_flag = true;
    }

    // Специальное поле
    public function getSpec(){
        return $this->spec;
    }

    public function setSpec($value){
        $this->spec = system::checkVar($value, isInt);

        if ($this->spec > 2)
            $this->spec = 0;

        $this->inher_flag = true;
    }

    // Вернет TRUE, если сист. название поля специфично для страниц и принадлежит классу наследнику "section"
    public function isPageField(){

        $sname = $this->sname;

        if (!empty($this->old_sname))
            $sname = $this->old_sname;
        return (in_array($sname, $this->page_fields) && $this->getClass()->isPage()) ? true : false;
    }


    // Добавление или изменение поля
    public function save(){

        if (!empty($this->id) && $this->old_system && $this->inher_flag)
            $this->newError(8, 'Поле является системным, вам запрещено его редактирование!');

        if ($this->issetErrors()) {

            // Указанные характеристики поля не соответствуют требованиям
            return false;

        } else if (!empty($this->id)) {

            // Изменяем поле
            return $this->changeField();

        } else {

            // Добавляем новое поле
            return $this->createField();

        }

    }

    private function isNameField(){
        return ($this->old_sname == 'name' || $this->sname == 'name');
    }


    // Изменение поля     -		-		-		-		-		-		-		-		-		-		-
    private function changeField(){

        // Проверяем на уникальность системное имя
        if (!empty($this->old_sname) && $this->old_sname != $this->sname){

            if ($this->isPageField())
                $this->sname = $this->old_sname;
            else {
                $sql =  'SELECT count(f_id)
		  					 FROM <<fgroup>>,  <<fields>>
		        			 WHERE f_sname = "'.$this->sname.'" and
		        			 	   fg_id = f_group_id and
		        			 	   fg_class_id = "'.$this->getClassId().'";';

                if (db::q($sql, value) === false) {

                    $this->newError(9, 'Поле с указанным системным именем уже существует в данном классе!');
                    return false;
                }
            }
        }

        // Вычисляем позицию поля
        if (!empty($this->position)) {

            if (!empty($this->old_pos)) {

                if (!empty($this->old_group_id)) {

                    // Если перенесли в другую группу
                    // Меняем позиции в новой группе
                    db::q('UPDATE <<fields>> SET f_position = f_position + 1
	                           WHERE f_position >= "'.$this->position.'" and
	                                 f_group_id = "'.$this->group_id.'";');

                    // Сдвигаем позиции в старой группе
                    db::q('UPDATE <<fields>> SET f_position = f_position - 1
	                           WHERE f_position > "'.$this->old_pos.'" and
	                                 f_group_id = "'.$this->old_group_id.'";');

                } else if ($this->position < $this->old_pos) {

                    // Если перенесли ниже по списку
                    db::q('UPDATE <<fields>> SET f_position = f_position + 1
	                           WHERE f_position >= "'.$this->position.'" and
	                                 f_position < "'.$this->old_pos.'" and
	                                 f_group_id = "'.$this->group_id.'";');

                } else if ($this->position > $this->old_pos) {

                    // Если перенесли выше по списку
                    db::q('UPDATE <<fields>> SET f_position = f_position - 1
	                            WHERE f_position <= "'.$this->position.'" and
	                                  f_position > "'.$this->old_pos.'" and
	                                  f_group_id = "'.$this->group_id.'";');
                }
            }
            $sql_pos = ', f_position = "'.$this->position.'"';

        } else $sql_pos = '';

        $list_id = (empty($this->list_id)) ? '' : 'f_list_id = '.$this->list_id.',';

        // Изменяем данные
        if ($this->old_system && !$this->inher_flag)
            $sql = 'UPDATE <<fields>>
						SET f_group_id = "'.$this->group_id.'" '.$sql_pos.'
						WHERE f_id = "'.$this->id.'";';
        else if ($this->sname == 'name')
            $sql = 'UPDATE <<fields>>
						SET f_group_id = "'.$this->group_id.'",
							f_name = "'.$this->name.'",
							f_hint = "'.$this->hint.'",
							f_type = "'.$this->type.'",
							f_view = "'.$this->view.'",
							f_search = "'.$this->search.'",
							f_filter = "'.$this->filter.'",
							f_required = "'.$this->required.'",
							f_uniqum = "'.$this->uniqum.'",
							f_spec = "'.$this->spec.'",
							f_system = "'.$this->system.'"
							'.$sql_pos.'
						WHERE f_id = "'.$this->id.'";';
        else
            $sql = 'UPDATE <<fields>>
						SET f_group_id = "'.$this->group_id.'",
							f_name = "'.$this->name.'",
							f_sname = "'.$this->sname.'",
							f_hint = "'.$this->hint.'",
							f_type = "'.$this->type.'", '.$list_id.'
							f_view = "'.$this->view.'",
							f_search = "'.$this->search.'",
							f_inherit = "'.$this->inherit.'",
							f_filter = "'.$this->filter.'",
							f_required = "'.$this->required.'",
							f_max_size = "'.$this->max_size.'",
							f_uniqum = "'.$this->uniqum.'",
							f_spec = "'.$this->spec.'",
							f_quick_add = "'.$this->quick_add.'",
							f_system = "'.$this->system.'",
							f_relation = "'.$this->relation.'"
							'.$sql_pos.'
						WHERE f_id = "'.$this->id.'";';

        if(db::q($sql) !== false) {

            if (!$this->old_system && $this->inher_flag && !$this->isNameField()) {

                // Изменяем тип поля в привязанной SQL-таблице
                if (!$this->isPageField() && (!empty($this->old_type) || !empty($this->old_sname))) {

                    $type = $this->getSQLType();

                    // Меняем имя или тип SQL-поля
                    if ($type) {

                        $table = $this->getClass()->getSName();

                        // if ($this->old_type != 95 && $this->old_type != 90) {

                        if ($this->old_type < 90) {

                            if (empty($this->old_sname)) $this->old_sname = $this->sname;
                            $res = db::q('ALTER TABLE <<__'.$table.'>> CHANGE `'.$this->old_sname.'` `'.$this->sname.'` '.$type.' NOT NULL;');

                        } else {

                            $res = db::q('ALTER TABLE <<__'.$table.'>> ADD `'.$this->sname.'` '.$type.' NOT NULL;');

                        }

                        if ($res === false) {
                            $this->newError(11, 'Не возможно изменить данные в привязанной таблице!');
                            return false;
                        }

                        // Если сменился тип ORM-поля, но поле в привязанной SQL-таблице нам не нужно - удаляем его.
                    } else if (!empty($this->old_type) && $this->old_type < 90 && $this->delFieldInSqlTable() === false) return false;

                }

                // Изменяем данные унаследованных полей
                if ($this->inherit) {
                    $children = ormClasses::getInheritors($this->getClassId());
                    while(list($num, $id) = each($children)) {

                        $obj = ormClasses::get($id);
                        $sname = (!empty($this->old_sname)) ? $this->old_sname : $this->sname;

                        if (!$field = $obj->getField($sname)) {

                            $group_id = $obj->getGroupBySName($this->getGroup()->getSName(), 1);
                            $field = clone $this;
                            $field->setGroupId($group_id);
                            $field->save();

                        } else $this->copyTo($field->id());
                    }
                }

            }

            return $this->id;

        } else {
            $this->newError(12, 'Ошибка в SQL запросе!');
            return false;
        }
    }

    // Создание поля      -		-		-		-		-		-		-		-		-		-		-
    private function createField(){

        if (!empty($this->type)){

            $sql =  'SELECT count(f_id)
	  					 FROM <<fgroup>>,  <<fields>>
	        			 WHERE f_sname = "'.$this->sname.'" and
	        			 	   fg_id = f_group_id and
		        			   fg_class_id = "'.$this->getClassId().'";';
            $fcount = db::q($sql, value);

        } else $fcount = 0;

        if ($fcount == 0) {

            $sql = 'SELECT MAX(f_position) FROM <<fields>>
		        		WHERE f_group_id = "'.$this->group_id.'";';

            $pos = db::q($sql, value) + 1;

            $list_id = (empty($this->list_id)) ? '' : 'f_list_id = '.$this->list_id.',';

            $sql = 'INSERT INTO <<fields>>
						SET f_group_id = "'.$this->group_id.'",
							f_name = "'.$this->name.'",
							f_sname = "'.$this->sname.'",
							f_hint = "'.$this->hint.'",
							f_type = "'.$this->type.'",
                            '.$list_id.'
							f_view = "'.$this->view.'",
							f_search = "'.$this->search.'",
							f_inherit = "'.$this->inherit.'",
							f_filter = "'.$this->filter.'",
							f_required = "'.$this->required.'",
							f_system = "'.$this->system.'",
							f_is_clone = "'.$this->is_clone.'",
							f_max_size = "'.$this->max_size.'",
							f_spec = "'.$this->spec.'",
							f_uniqum = "'.$this->uniqum.'",
							f_quick_add = "'.$this->quick_add.'",
							f_relation = "'.$this->relation.'",
							f_position = "'.$pos.'";';

            $this->id = db::q($sql);

            if ($this->id !== false) {

                // Создаем новое поле в таблице
                $type = $this->getSQLType();

                if (!$this->isNameField()) {

                    if ($type) {
                        $table = $this->getClass()->getSName();

                        if (!$this->isPageField())
                            $res = db::q('ALTER TABLE <<__'.$table.'>> ADD `'.$this->sname.'` '.$type.' NOT NULL;');
                        else
                            $res = true;

                        if ($res === false) {
                            $this->newError(11, 'Не возможно изменить данные в привязанной таблице!');
                            return false;
                        }
                    }


                    // Создаем копии полей для каждого наследника
                    if ($this->inherit) {

                        $children = ormClasses::getInheritors($this->getClassId());
                        while(list($num, $id) = each($children)) {
                            $obj = ormClasses::get($id);
                            $group_id = $obj->getGroupBySName($this->getGroup()->getSName(), 1);

                            $field = clone $this;
                            $field->setGroupId($group_id);
                            $field->save();
                        }
                    }

                }

                return $this->id;

            } else {
                $this->newError(12, 'Ошибка в SQL запросе!');
                return false;
            }

        } else {

            $this->newError(9, 'Поле с указанным системным именем уже существует в данном классе!');
            return false;

        }

    }


    // Удаление поля    -		-		-		-		-		-		-		-		-		-		-
    public function delete(){

        if ($this->system || $this->isNameField()){

            $this->newError(8, 'Поле является системным, вам запрещено его редактирование!');
            return false;

        } else if (!empty($this->id)) {

            $ret = db::q('DELETE FROM <<fields>> WHERE f_id = "'.$this->id.'";');

            if ($ret !== false) {

                $type = $this->getSQLType();

                // Удаляем поле в привязанной таблице
                if ($type && $this->delFieldInSqlTable() === false)
                    return false;

                // Удаляем у наследников класса поля
                while($obj = ormClasses::getInheritor($this->getClassId()))
                    if ($field = $obj->getField($this->sname))
                        $field->delete();

                return $this->id;

            } else {
                $this->newError(12, 'Ошибка в SQL запросе!');
                return false;
            }

        } else return false;

    }

    // Удаляем поле в привязанной SQL-таблице
    private function delFieldInSqlTable(){

        if (!$this->isPageField()) {

            $table = $this->getClass()->getSName();
            $ret = db::q('ALTER TABLE <<__'.$table.'>> DROP `'.$this->sname.'`;');

            if ($ret === false) {
                $this->newError(11, 'Не возможно изменить данные в привязанной таблице!');
                return false;
            } else return true;

        } else return true;
    }

    // Возвращает тип SQL-поля в зависимости от выбранного типа ORM-поля
    private function getSQLType() {

        // Строка  E-mail  URL  Пароль		  Файл  Список Файлов	Изображение   Видео  Флеш-ролик
        if ($this->type == 10 || $this->type == 15 || $this->type == 20 || $this->type == 35 ||
            $this->type == 70 || $this->type == 73 || $this->type == 75 || $this->type == 80 || $this->type == 85){

            $type = 'VARCHAR( 255 )';

            // Число  Позиция в списке
        } else if ($this->type == 40 || $this->type == 65) {

            $type = 'INT( 11 )';

            // Число с точкой
        } else if ($this->type == 45) {

            $type = 'FLOAT(11,2)';

            // Цена
        } else if ($this->type == 47) {

            $type = 'FLOAT(11,2)';

            // Галочка (логический)
        } else if ($this->type == 50) {

            $type = 'TINYINT( 1 )';

            // Дата
        } else if ($this->type == 25) {

            $type = 'DATE';

            // Время
        } else if ($this->type == 30) {

            $type = 'TIME';

            // Дата и Время
        } else if ($this->type == 32) {

            $type = 'DATETIME';

            // Большой текст
        } else if ($this->type == 55) {

            $type = 'TEXT';

            // HTML – текст
        } else if ($this->type == 60) {

            $type = 'LONGTEXT';

            // Выпадающий список   Список c множественным выбором  	Ссылка на дерево
        } else $type = false;

        return $type;
    }




}
?>