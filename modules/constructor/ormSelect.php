<?php

define('asc', 'asc');
define('desc', 'desc');
define('random', 'random');
define('position', 'position');
define('parent', 'parent');


/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс для организации выборки ORM-объектов (ormObject и ormPage)
	с учетом нестандартных условий.
*/

class ormSelect {

    private $stop = false;
    private $class, $fields, $parent_id;
    private $class_list = array();
    private $sel_fields = '';
    private $tables = array();
    private $limit, $orderBy, $orderField, $orderParram, $where;
    private $psevdo = array('id', 'name', 'class_id', 'create_date', 'change_date', 'parents', 'children', 'position');
    private $deistvo = array('=', '<>', '<', '>', '<=', '>=', 'LIKE', 'BETWEEN', '!=');

    private $cache = array();
    private $classesList = array();
    private $relsToField = false;
    private $permission = true;
    private $find_page = 0;
    private $cur_domain_id = 0;
    private $cur_lang_id = 0;
    private $depends_link = -1;

    private $cl_fields = array();

    private $isset_page_field, $isset_base_field;

    // Список полей специфичных для ORM-страниц
    private $page_fields = array('active', 'is_home_page', 'view_in_menu', 'view_submenu', 'in_search', 'in_index',
                                 'title', 'h1', 'pseudo_url', 'keywords', 'description', 'img_h1', 'img_act', 'img_no_act',
                                 'template_id', 'template2_id', 'lang_id', 'domain_id', 'in_new_window', 'other_link', 'order_by', 'number_of_items');

    private $min_page_fields = array('pseudo_url', 'is_home_page', 'other_link', 'in_new_window');


    // Указывает объекты какого класса необходимо вывести
    public function __construct($class = ''){

        $this->isset_page_field = $this->isset_base_field = false;

        if (!empty($class)) {

            $pos = strpos($class, '|');

            if ($pos) {

                // Указан список классов
                $classes = explode('|', $class);

                foreach($classes as $val)
                    if ($class = ormClasses::get($val)) {

                        if (empty($this->classesList)) {
                            $this->class = $class;
                            $this->find_page = $this->class->isPage();
                        }

                        $this->classesList[] = $val;
                        $this->class_list[$class->id()] = $val;
                    }                

                $this->fields = ormClasses::getVirtual($this->classesList);

            } else {

                // Указан один класс
                if ($class[0] == '*') {
                    $class = str_replace('*', '', $class);
                    $with_inheriters = false;
                } else $with_inheriters = true;

                if ($this->class = ormClasses::get($class)) {

                    $this->find_page = $this->class->isPage();

                    // Получаем список всех наследников этого класса
                    if ($with_inheriters) {
                        $this->class_list = $this->class->getAllInheritors();
                        foreach($this->class_list as $val)
                            $this->classesList[] = $val;
                    }

                    $this->fields = $this->class->loadFields();
                } else $this->stop = true;
            }
        }
    }

    // Вернет ID базового ORM-класса выборки
    public function getObjectsClass(){

        if (!($this->class instanceof ormClass))
            $this->checkFieldList();

        if ($this->class instanceof ormClass)
            return $this->class->id();
    }

    // Вернет список классов объектов участвующих в выборке
    public function getClassesList(){

        if (empty($this->classesList))
            $this->checkFieldList();

        return $this->classesList;
    }

    /**
     * @return NULL
     * @param integer $lang_id - ID языковой версии, если не указан используется текущая.
     * @param integer $domain_id - ID домена, если не указан используется текущий.
     * @desc Указывает что выборка будет производиться только по страницам.
     */
    public function findInPages($lang_id = 0, $domain_id = 0){

        if (empty($lang_id))
            $this->cur_lang_id = languages::curId();

        if (empty($domain_id))
            $this->cur_domain_id = domains::curId();

        $this->find_page = 1;
    }

    /**
     * @return NULL
     * @param string $fields - Список полей через запятую.
     * @desc Устанавливает поля значения которых необходимо получить.
     */
    public function fields($fields){

        if (!empty($fields))
            $this->sel_fields = $fields;

    }

    // Проверяет был ли установлен список полей
    public function fieldsIsDefined(){
        return (!empty($this->sel_fields)) ? true : false;
    }

    // Вернет установленные для выборки поля
    public function getFields(){
        if (!empty($fields))
            return explode(',', str_replace(' ', '', $this->sel_fields));
        else
            return array();
    }

    /**
     * @return NULL
     * @param string $field - Системное имя поля, по которому будет проходить сортировка
                               Можно использовать SQL-синтаксис, например "date desc, name asc"
     * @param string $parram - Способ сортировки, используйте константы:
                                asc		 -	Сортировка по возрастанию
                                desc	 -	Сортировка по убыванию
                                random   -	В случайном порядке
                                position -	По позиции объекта в списке
                                parent -	По родителю
     * @desc Устанавливает параметры сортировки элементов выборки
     */
    public function orderBy($field, $parram = ''){

        if (empty($parram) && $field == random)

            $this->orderBy = ' ORDER BY RAND() ';

        else if ($field == position)

            if ($this->relsToField)
                $this->orderBy = ' ORDER BY r_position '.$parram.' ';
            else
                $this->orderBy = ' ORDER BY o_id '.$parram.' ';

        else if ($field == parent) {

            if ($this->relsToField)
                $this->orderBy = ' ORDER BY r_parent_id '.$parram.' ';
            else
                $this->orderBy = ' ORDER BY o_id '.$parram.' ';

        } else {

            $field = strtolower($field);

            $fields = explode(',', $field);

            if (count($fields) > 1) {

                $str = '';
                foreach($fields as $field) {

                    $tmp = explode(' ', trim($field));
                    $field = $tmp[0];
                    $parram = $tmp[1];

                    if ($this->issetField($field)) {

                        if (!in_array($field, $this->psevdo) && isset($this->fields[$field]))
                            $this->isset_base_field = true;

                        $sql_field = (in_array($field, $this->psevdo)) ? 'o_'.strtolower($field) : $field;

                        if (!empty($str)) $str .= ', ';
                        $str .= $sql_field.' '.$parram;

                        $this->orderField = $field;
                        $this->orderParram = (empty($parram)) ? 'asc' : $parram;
                    }
                }


                if (!empty($str)) {

                    $this->orderBy = ' ORDER BY '.$str.' ';

                } else if ($this->find_page)
                    if ($this->relsToField)
                        $this->orderBy = ' ORDER BY r_position asc ';
                    else
                        $this->orderBy = ' ORDER BY o_id asc ';

            } else {

                // Сортировка по одному полю
                if ($this->issetField($field)) {

                    if (!in_array($field, $this->psevdo) && isset($this->fields[$field]))
                        $this->isset_base_field = true;

                    $sql_field = (in_array($field, $this->psevdo)) ? 'o_'.strtolower($field) : $field;

                    $this->orderBy = ' ORDER BY '.$sql_field.' '.$parram.' ';
                    $this->orderField = $field;
                    $this->orderParram = (empty($parram)) ? 'asc' : $parram;

                } else if ($this->find_page)
                    if ($this->relsToField)
                        $this->orderBy = ' ORDER BY r_position asc ';
                    else
                        $this->orderBy = ' ORDER BY o_id asc ';
            }
        }
    }

    // Вернет поле по которому будет проходить сортировка
    public function orderField() {
        return $this->orderField;
    }

    // Вернет способ сортировки
    public function orderParram() {
        return $this->orderParram;
    }

    // Если вызван, выборка производится без учета прав доступа текущего пользователя
    public function withoutPermission() {
        $this->permission = false;
    }

    /**
     * @return NULL
     * @param string $start - Общее количество элементов в выборке, если $count == 0.
    Смещение с начала выборки, если $count > 0.
     * @param string $count - Общее количество элементов в выборке.
     * @desc Устанавливает лимит на количество объектов в выборке
     */
    public function limit($start, $count = 0){
        if (empty($count))
            $this->limit = 'LIMIT '.abs($start);
        else
            $this->limit = 'LIMIT '.abs($start).', '.abs($count);
    }

    // Объединение операндов в цепочку - Логическое И
    public function logAnd(){
        $arg = func_get_args();
        return $this->newLogStr($arg, 'and');
    }

    // Объединение операндов в цепочку - Логическое ИЛИ
    public function logOr(){
        $arg = func_get_args();
        return $this->newLogStr($arg, 'or');
    }

    // Вспомогательная функция объеденяет операнды в логическую цепочку
    private function newLogStr($arg, $operator){

        if (count($arg) == 1 && is_array($arg[0]))
            $arg = $arg[0];

        $str = '';
        while (list($key, $val) = each($arg)){
            if (!empty($val))
                $str .= (empty($str)) ? $val : ' '.$operator.' '.$val;
        }

        if (!empty($str))
            return '('.$str.')';
    }


    // Если класс для выборки не определен, определяем класс на основе ID родителя
    private function checkFieldList($par = ''){

        if (empty($this->class) && empty($this->fields)) {

            $field_id = 'r_field_id is NULL and ';

            if (is_array($this->parent_id)) {

                // Если несколько родителей
                $sql = '';
                while(list($num, $value) = each($this->parent_id)) {
                    $obj_id = (is_array($value)) ? $value['id'] : $value;
                    if (!empty($sql)) $sql .= ' OR ';
                    $sql .= ' r_parent_id = "'.$obj_id.'" ';
                }
                if (!empty($sql)) $sql = ' ('.$sql.') and ';

            } else if (!empty($this->parent_id)) {

                // Если это связь зависимости через depends()
                if ($this->depends_link > 0 || $this->depends_link == 'all') {
                    $field_id = ($this->depends_link == 'all') ? '' : 'r_field_id = "'.$this->depends_link.'" and ';
                    $sql = 'r_parent_id = "'.$this->parent_id.'" and ';
                } else
                    $sql = 'r_parent_id = "'.$this->parent_id.'" and ';

            } else if ($this->parent_id == 0 && $this->parent_id !== null)
                $sql = 'r_parent_id is NULL and ';
            else $sql = '';

            if ($this->find_page) {
                if (!empty($this->cur_lang_id) && !empty($this->cur_domain_id))
                    $sql .= ' lang_id = "'.$this->cur_lang_id.'" and domain_id = "'.$this->cur_domain_id.'" and ';
                $sql .= ' p_obj_id = o_id and ';
                $table = ', <<pages>>';
            } else $table = '';


            $c = db::q('/* '.$par.' */SELECT c_id, c_sname
							FROM <<classes>>, <<objects>>, <<rels>>'.$table.'
							WHERE '. $field_id . $sql .'
								r_children_id = o_id and
								o_to_trash = 0 and
								o_class_id = c_id and
								c_is_page = "'.$this->find_page.'"
							GROUP BY c_id;', records);

            $this->classesList = array();
            while(list($key, $val) = each($c)) {

                if (empty($key))
                    $this->class = ormClasses::get($val['c_id']);

                $this->classesList[] = $val['c_sname'];
                $this->class_list[$val['c_id']] = $val['c_sname'];
            }

            // Создаем виртуальный класс, имееющий общие свойства классов из полученого списка
            //$this->class = 'virtual';
            $this->fields = ormClasses::getVirtual($this->classesList);

        }
    }


    // В качестве параметров перечисляются условия выборки или указывается единственное условие
    function where(){
        $arg = func_get_args();

        if (count($arg) == 3 && in_array($arg[1], $this->deistvo))
            $tmp = $this->val($arg[0], $arg[1], $arg[2]);
        else if (count($arg) == 4 && in_array($arg[1], $this->deistvo))
            $tmp = $this->val($arg[0], $arg[1], $arg[2], $arg[3]);
        else if (count($arg) == 4 && ($arg[3] == 'OR' || $arg[3] == 'AND'))
            $tmp = $this->val($arg[0], $arg[1], $arg[2], $arg[3]);
        else
            $tmp = $this->newLogStr($arg, 'and');

        if (!empty($tmp))
            $this->where .= ' and '.$tmp;
    }

    /**
     * @return NULL
     * @param string $field - Системное имя поля
     * @param string $znak - Знак сравнения  =, <>, <, >, <=, >=, LIKE
     * @param string $val - Значение
     * @param string $val2 - Значение2, если используется оператор BETWEEN
     * @desc Добавляем новое условие в запрос
     */
    public function val($field, $znak, $val, $val2 = ''){

        if ($znak == '!=') $znak = '<>';

        if (in_array($znak, $this->deistvo))
            if (in_array($field, $this->psevdo)) {

                if ($field == 'parents') {

                    $this->relsToField = true;
                    if (empty($val2)) $val2 = 'OR'; else if ($val2 != 'OR') $val2 = 'AND';

                    $this->parent_id = $val;

                    if (!is_array($val)) {

                        $this->tables[', <<rels>> link_.&.'] = 1;

                        if ($val < 0)
                            $parent = '';
                        else if (empty($val))
                            $parent = ' and link_.&..r_parent_id is NULL ';
                        else
                            $parent = ' and link_.&..r_parent_id = "'.$val.'" ';

                        return '(
                            link_.&..r_children_id = obj_.&..o_id and
                            link_.&..r_field_id is NULL '.$parent.'
                        )';

                    } else {

                        $parents = '';
                        $number = 0;

                        if ($val2 == 'OR') {

                            $this->tables[', <<rels>> link_.&.'] = 1;

                            foreach($val as $value) {

                                $obj_id = (is_array($value)) ? $value['id'] : $value;

                                if (!empty($parents)) $parents .= $val2;

                                if (empty($obj_id))
                                    $parents .= ' link_.&..r_parent_id is NULL ';
                                else
                                    $parents .= ' link_.&..r_parent_id = "'.$obj_id.'" ';
                            }

                            if (!empty($parents))
                                $parents = '(
                                    link_.&..r_children_id = obj_.&..o_id and
                                    link_.&..r_field_id is NULL and ('.$parents.')
                                )';

                        } else {

                            foreach($val as $value) {

                                $obj_id = (is_array($value)) ? $value['id'] : $value;

                                $numlink = (empty($number)) ? '' : $number;
                                $number++;

                                $this->tables[', <<rels>> link'.$numlink.'_.&.'] = 1;

                                if (!empty($parents)) $parents .= $val2;

                                if (empty($obj_id))
                                    $parent = ' link'.$numlink.'_.&..r_parent_id is NULL ';
                                else
                                    $parent = ' link'.$numlink.'_.&..r_parent_id = "'.$obj_id.'" ';

                                $parents .= '(
                                    link'.$numlink.'_.&..r_children_id = obj_.&..o_id and
                                    link'.$numlink.'_.&..r_field_id is NULL and '.$parent.'
                                )';
                            }

                            if (!empty($parents))
                                $parents = '('.$parents.')';
                        }

                        return $parents;
                    }

                } else if ($field == 'position' && $this->relsToField) {

                    return 'link_.&..r_position '.$znak.' "'.$val.'"';

                } else if (in_array($field, $this->psevdo)) {

                    if ($znak == 'BETWEEN' && $val2 != '')
                        return 'obj_.&..o_'.$field.' BETWEEN "'.$val.'" and "'.$val2.'"';
                    else
                        return 'obj_.&..o_'.$field.' '.$znak.' "'.$val.'"';

                }

            } else if ($this->issetField($field)) {
                //  echo $field.' |'.$this->class->getSName().'<br/>';
                if (isset($this->fields[$field]) && $this->fields[$field]['f_type'] == 95 || $this->fields[$field]['f_type'] == 90) {

                    // Если это связь
                    $num = rand(100, 999);
                    $this->tables[', <<rels>> link_'.$num.'.&.'] = 1;

                    if (empty($val2)) $val2 = 'OR'; else if ($val2 != 'OR') $val2 = 'AND';

                    if ($val2 == 'OR' && is_array($val)) {

                        $rels = '';
                        foreach($val as $value) {

                            $obj_id = (is_array($value)) ? $value['id'] : $value;

                            if (!empty($rels)) $rels .= $val2;

                            $parent_field = ($this->fields[$field]['f_relation'] > 0) ? 'r_parent_id' : 'r_children_id';
                            $child_field = ($this->fields[$field]['f_relation'] > 0) ? 'r_children_id' : 'r_parent_id';

                            $rels .= '(link_'.$num.'.&..'.$child_field.' = obj_.&..o_id and
                                link_'.$num.'.&..'.$parent_field.' = "'.$obj_id.'" and
                                link_'.$num.'.&..r_field_id '.$znak.' "%field_'.$this->fields[$field]['f_sname'].'_repl%")';
                        }

                        if (!empty($rels))
                            return '(
                                    link_.&..r_children_id = obj_.&..o_id and
                                    link_.&..r_field_id is NULL and ('.$rels.')
                                )';

                    } else {

                        if ($this->fields[$field]['f_relation'] > 0)
                            return '(link_'.$num.'.&..r_children_id = obj_.&..o_id and
                                link_'.$num.'.&..r_parent_id = "'.$val.'" and
                                link_'.$num.'.&..r_field_id '.$znak.' "%field_'.$this->fields[$field]['f_sname'].'_repl%")';
                        else
                            return '(link_'.$num.'.&..r_parent_id = obj_.&..o_id and
                                link_'.$num.'.&..r_children_id = "'.$val.'" and
                                link_'.$num.'.&..r_field_id '.$znak.' "%field_'.$this->fields[$field]['f_sname'].'_repl%")';
                    }

                } else if ($this->fields[$field]['f_type'] == 105) {

                    // Поиск по тегам
                    $this->tables[', <<tags_rels>> tags_.&.'] = 1;
                    if (empty($val2)) $val2 = 'OR'; else if ($val2 != 'OR') $val2 = 'AND';
                    if (!is_array($val)) $val[] = $val;


                    if (empty($val)) {
                        $tags = ' tags_.&..tr_tag_id = "0" ';
                    } else {
                        $tags = '';
                        while(list($num, $value) = each($val)) {
                            $tag_id = (is_array($value)) ? $value['id'] : $value;
                            if (!empty($tags)) $tags .= $val2;
                            $tags .= ' tags_.&..tr_tag_id = "'.$tag_id.'" ';
                        }
                    }

                    return '(tags_.&..tr_obj_id = obj_.&..o_id and ('.$tags.'))';

                } else  {

                    // Если это обычное поле
                    $pt = ($this->find_page && in_array($field, $this->page_fields)) ? 'pt_' : '';

                    // Если не определен класс данных, будем его определять по полям указанным в условии.
                    if (empty($pt) && empty($this->class))
                        $this->cl_fields[] = $field;

                    if ($znak != 'BETWEEN')

                        return $pt.'.&..'.$field.' '.$znak.' "'.$val.'"';

                    else if ($znak == 'BETWEEN' && $val2 != '')

                        return $pt.'.&..'.$field.' BETWEEN "'.$val.'" and "'.$val2.'"';

                }

            }
    }


    public function val2($field, $znak, $val, $val2 = ''){

        if (in_array($znak, $this->deistvo))
            if (in_array($field, $this->psevdo)) {

                if ($field == 'parents') {

                    if (empty($this->parent_id))
                        $this->parent_id = $val;

                    // Если это связь - родитель
                    $this->tables[', <<rels>> link_.&.'] = 1;
                    $this->relsToField = true;

                    $parent = (empty($val)) ? 'r_parent_id is NULL' : 'r_parent_id = "'.$val.'"';
                    return '(link_.&..r_children_id = obj_.&..o_id and link_.&..'.$parent.' and link_.&..r_field_id is NULL)';

                } else if ($field == 'position' && $this->relsToField) {

                    return 'link_.&..r_position '.$znak.' "'.$val.'"';

                } else if (in_array($field, $this->psevdo)) {

                    if ($znak == 'BETWEEN' && $val2 != '')
                        return 'obj_.&..o_'.$field.' BETWEEN "'.$val.'" and "'.$val2.'"';
                    else
                        return 'obj_.&..o_'.$field.' '.$znak.' "'.$val.'"';

                }

            } else {

                // Если это обычное поле
                $pt = '';

                if ($znak != 'BETWEEN')

                    return $pt.'.&..'.$field.' '.$znak.' "'.$val.'"';

                else if ($znak == 'BETWEEN' && $val2 != '')

                    return $pt.'.&..'.$field.' BETWEEN "'.$val.'" and "'.$val2.'"';

            }
    }

    /**
     * @return null
     * @param string $class_name - Имя класса в объекты которого должен входить объекты выборки
     * @param string $parram - Дополнительные условия для объекта $class_name, перечисляются через запятую
    Для определения условий используйте метод val()
     * @desc Проверяет содержится ли объект выборки в указанном объекте

     * @simple Получаем активных пользователей, которые входят в активные группы:
     * 
            $sel = new ormSelect('user');
            $sel->where(
                $sel->val('active', '=', 1),
                $sel->containedIn('user_group',
                    $sel->val('active', '=', 1)
                )
            );
     */
    function containedIn(){
        $arg = func_get_args();

        list($key, $parentClassName) = each($arg);

        if ($class = ormClasses::get($parentClassName)) {

            $cn = $parentClassName;


            $str = '';
            while (list($key, $val) = each($arg)){
                if (!empty($val))
                    $str .= ' and '.str_replace('.&.', $cn, $val);
            }


            $this->tables[', <<rels>> link_.&.'] = 1;
            $this->tables[', <<rels>> link_'.$cn] = 1;
            $this->tables[', <<objects>> obj_'.$cn] = 1;
            $this->tables[', <<__'.$cn.'>> '.$cn] = 1;


            $cn2 = (strpos($str, 'r_children_id')) ? '.&.' : $cn;

            $ret = '(link_'.$cn2.'.r_parent_id = obj_'.$cn.'.o_id and
		        		'.$cn.'.obj_id = obj_'.$cn.'.o_id and
		        		obj_'.$cn.'.o_to_trash = 0 and
		        		link_'.$cn2.'.r_children_id = obj_.&..o_id and
		        		link_'.$cn2.'.r_field_id is NULL) ';




            return ' ('.$ret.' '.$str.')';
        }
    }

    // Добавляет проверку зависимости от объекта с указанным ID
    function depends($obj_id, $field_id = 0){

        if (!empty($obj_id)) {

            if (empty($this->parent_id))
                $this->parent_id = $obj_id;

            $this->depends_link = $field_id;

            $cn = 'ln'.rand(100, 999);

            $this->tables[', <<rels>> link_'.$cn] = 1;
            $this->tables[', <<objects>> obj_'.$cn] = 1;

            $ret = 'link_'.$cn.'.r_parent_id = obj_'.$cn.'.o_id and
		        		obj_'.$cn.'.o_to_trash = 0 and
		        		obj_'.$cn.'.o_id = "'.$obj_id.'" and
		        		link_'.$cn.'.r_children_id = obj_.&..o_id';

            if (!empty($field_id) && $field_id != 'all')
                $ret .= ' and link_'.$cn.'.r_field_id = "'.$field_id.'"';
            else if (empty($field_id))
                $ret .= ' and link_'.$cn.'.r_field_id is NULL';

            $this->where .= ' and ('.$ret.') ';
        }

    }


    /**
     * @return null
     * @param string $class_name - Имя класса объекты которого должны содержаться в объектах выборки.
     * @param string $parram - Дополнительные условия для объекта $class_name, перечисляются через запятую
    Для определения условий используйте метод ormSelect::val()
     * @desc Указывает что объекты выборки должны содержать указанный объект
     * @simple
     */
    function contains($obj_id, $field_id = 0) {

        if (!empty($obj_id)) {

            $cn = 'ln'.rand(100, 999);

            $this->tables[', <<rels>> link_'.$cn] = 1;
            $this->tables[', <<objects>> obj_'.$cn] = 1;

            $ret = 'link_'.$cn.'.r_parent_id = obj_.&..o_id and
		        		obj_'.$cn.'.o_to_trash = 0 and
		        		obj_'.$cn.'.o_id = "'.$obj_id.'" and
		        		link_'.$cn.'.r_children_id = obj_'.$cn.'.o_id';

            if (!empty($field_id) && $field_id != 'all')
                $ret .= ' and link_'.$cn.'.r_field_id = "'.$field_id.'"';
            else if (empty($field_id))
                $ret .= ' and link_'.$cn.'.r_field_id is NULL';

            $this->where .= ' and ('.$ret.') ';
        }

    }

    /*

      // Связи с родителем

               // Существует связь с родителем
               $sel->issetLinkWith(lParent);

               // Существует связь с родителем наследником класса category
               $sel->issetLinkWith(lParent, 'category');

               // Существует связь с родителем по полю с ID == 32
               $sel->issetLinkWith(lParent, 0, 32);


               // Связи с детьми

               // Существует зависимые объекты
               $sel->issetLinkWith(lChildren);

               // Существует зависимые объекты наследники класса category
               $sel->issetLinkWith(lChildren, 'category');

               // Существует зависимые объекты, связь по полю с ID = 45
               $sel->issetLinkWith(lChildren, 0, 'treners');


    */

    /**
     * @return null
     * @param string $field_name - Имя поля по которому проверяется наличие связи с объектыми
     * @param string $parram - Дополнительные условия для объекта, перечисляются через запятую.
    Для определения условий используйте метод ormSelect::val()
     * @desc Проверяет существует ли "связь по полю" объекта выборки с указанным объектом
     * @simple
     */
    function issetLink(){

    }

    /**
     * @return null
     * @param string $class_name - Имя класса
     * @param string $parram - Дополнительные условия для объекта $class_name, перечисляются через запятую
    Для определения условий используйте метод ormSelect::val()
     * @desc Проверяет есть ли связь между объекты класса $class_name и объектами выборки
     * @simple
     */
    function issetLinkWith(){

    }


    // Проверяет существование поля
    function issetField($fname, $without_pseudo = false){
        //
        if (!$without_pseudo && in_array($fname, $this->psevdo) || ($this->find_page && in_array($fname, $this->page_fields)))
            return true;

        //if ($without_pseudo) $fname .= '++';
        // Если это не стандартное поле, подгружаем список всех полей
        $this->checkFieldList($fname);

        if (isset($this->fields[$fname])) {
            if ($this->fields[$fname]['f_type'] != 105)
                $this->isset_base_field = true;
            return true;
        }

        return false;
    }

    // Функция добавляет указанное поле в запрос в раздел Select
    private function addFieldToSelect($sname, $type, $sn, $add = true){

        // Выставляем статусы, чтобы знать какие таблицы использовать
        if ($this->find_page && in_array($sname, $this->page_fields))
            $this->isset_page_field = true;
        else if (!in_array($sname, $this->psevdo) && isset($this->fields[$sname]) && $type != 90 && $type != 95 && $type != 97 && $type != 105 && $type != 100)
            $this->isset_base_field = true;
        //isset($this->fields[$sname])
        if ($add && !empty($sname) && !in_array($sname, $this->psevdo) && $type != 90 && $type != 95 && $type != 97 && $type != 100 && $type != 105)

            if ($this->find_page && in_array($sname, $this->page_fields)) {

                // Добавляем поле страницы
                if (!in_array($sname, $this->min_page_fields))
                    $this->sel_fields .= ', pt_'.$sn.'.'.$sname;

            } else {

                // Добавляем обычное поле
                $this->sel_fields .= ', '.$sn.'.'.$sname;
            }
    }

    // Вернет список объектов отвечающих параметрам выборки
    private function createQuery($parram){

        if ($this->stop)
            return ($parram != 2) ? array() : 0;

        // Добавляем ID родителя в результаты выборки, если работаем со страницами
        if ($this->find_page && !$this->relsToField) {

            if (!strpos('link_.&..r_children_id = obj_.&..o_id', $this->where)) {

                $this->tables[', <<rels>> link_.&.'] = 1;

                $this->where .= '
                    and link_.&..r_children_id = obj_.&..o_id
                    and link_.&..r_field_id is NULL
                ';

                $this->relsToField = true;
            }
        }

        if (!empty($this->class))
            $sn = $this->class->getSName();
        else
            $sn = 'obj';

        // +++		Формируем блок SELECT		+++

        // Делаем специальное преобразование
        if (count($this->class_list) == 1 && !empty($this->fields))
            while (list($key, $val) = each($this->fields))
                $this->where = str_replace('%field_'.$val['f_sname'].'_repl%', $val['f_id'], $this->where);

        // Формируем список полей. Попутно перепроверяем, какие контентные таблицы нам нужны
        //$this->isset_page_field = $this->isset_base_field = false;

        if (empty($this->sel_fields)) {

            // Выбираем все поля
            $this->checkFieldList(2);

            reset($this->fields);
            while (list($key, $val) = each($this->fields))

                $this->addFieldToSelect($val['f_sname'], $val['f_type'], $sn, ($parram != 2));


        } else {

            // Выбираем только указанные пользователем поля
            $this->sel_fields = str_replace(' ', '', $this->sel_fields);
            $mas = explode(',', $this->sel_fields);
            if ($parram != 2)
                $this->sel_fields = '';

            foreach($mas as $val)
                if ($this->issetField($val)) {
                    $type = (isset($this->fields[$val])) ? $this->fields[$val]['f_type'] : 0;
                    $this->addFieldToSelect($val, $type, $sn, ($parram != 2));
                }

            if (!empty($this->orderField)) {
                $type = (isset($this->fields[$this->orderField])) ? $this->fields[$this->orderField]['f_type'] : 0;
                $this->addFieldToSelect($this->orderField, $type, $sn, ($parram != 2));
            }
        }

        // Формируем список полей в зависимости от типа запроса: получение количества или выборки
        if ($parram < 2) {

            $rels_f = ($this->relsToField) ? ', link_'.$sn.'.r_position r_position, link_'.$sn.'.r_parent_id r_parent_id' : '';

            if ($this->find_page) {


                $page_field = ', pt_'.$sn.'.other_link other_link,
                				pt_'.$sn.'.pseudo_url pseudo_url,
                				pt_'.$sn.'.in_new_window in_new_window,
                				pt_'.$sn.'.is_home_page is_home_page,
                				pt_'.$sn.'.lang_id lang_id,
                				pt_'.$sn.'.domain_id domain_id,
                				pt_'.$sn.'.template_id template_id,
                				pt_'.$sn.'.template2_id template2_id';

                if ($this->permission)
                    $page_field .= ', MAX(r_state) r_state';

            } else $page_field = '';



            $select = '/* list */ SELECT obj_'.$sn.'.o_id o_id,
			            	   obj_'.$sn.'.o_name o_name,
			            	   obj_'.$sn.'.o_class_id o_class_id,
			            	   obj_'.$sn.'.o_create_date o_create_date,
			            	   obj_'.$sn.'.o_change_date o_change_date'.$page_field.$rels_f.'
			            	   '.$this->sel_fields.' ';

        } else {

            $select = '/* count */ SELECT obj_'.$sn.'.o_id id';
        }



        // +++		Формируем блок FROM		+++

        // Cписок используемых таблиц
        $tables = '';
        reset($this->tables);
        while (list($key, $val) = each($this->tables))
            $tables .= $key;

        // Устанавливаем условие на выборку с учетом прав доступа
        if ($this->find_page && $this->permission) {
            $rights_where = str_replace('GROUP BY o_id', '', ormPages::getSqlForRights());
            $tables .= ', <<rights>>';
        } else $rights_where = '';

        // +++		Собираем основную часть запроса		+++

        reset($this->class_list);

        if ($this->isset_base_field && count($this->class_list) > 1) {

            // Работаем с несколькими типами данных

            $sql = '';
            $num = 0;

            while (list($id, $sname) = each($this->class_list)) {
                $num ++;
                $union = ($num >= 2) ? ' UNION ' : '';
                $sql .= $union.'('.$this->getBasePartQuery($sn, $select, $tables, $rights_where, $id, $sname).')';
            }

        } else {

            // Работаем с одним типом данных или без выборки из таблицы данных

            if (count($this->class_list) && $this->isset_base_field)
                list($id, $sname) = each($this->class_list);
            else
                $id = $sname = '';

            $sql = $this->getBasePartQuery($sn, $select, $tables, $rights_where, $id, $sname);
        }


        // Добавляем сортировку и лимит, получаем результат
        if ($parram != 2) {

            if (!empty($this->orderBy))
                $sql .= ' '.$this->orderBy;
            //  else
            //$sql .= 'ORDER BY r_position ASC';

            if (!empty($this->limit))
                $sql .= ' '.$this->limit;



            // echo $sql.'<br/><br/>';
            $obj = db::q($sql, records, 0);

        } else $obj = count(db::q($sql, records, 0));

        return $obj;
    }

    // Формируем основную часть запроса
    private function getBasePartQuery($sn, $select, $tables, $rights_where, $id = '', $sname = ''){

        // Подставляем ID полей для каждого класса

        $where = $this->where;
        if (count($this->class_list) > 1 && $this->isset_base_field){
            $class = ormClasses::get($id);
            $fields = $class->loadFields();
            foreach($fields as $f)
                $where = str_replace('%field_'.$f['f_sname'].'_repl%', $f['f_id'], $where);
        }

        // Выборка с учетом классов объектов?
        if (!empty($sname) && !empty($id)) {

            $tables .= ', <<__'.$sname.'>> '.$sn;
            $bwt = 'and obj_'.$sn.'.o_class_id = "'.$id.'" and
                    			obj_'.$sn.'.o_id = '.$sn.'.obj_id';

        } else if (!empty($this->class) && count($this->class_list) < 2) {

            $bwt = 'and obj_'.$sn.'.o_class_id = "'.$this->class->id().'"';

        } else if (count($this->class_list) > 1) {

            $list = '';
            while(list($cid, $cname) = each($this->class_list)) {
                if (!empty($list)) $list .= ' or ';
                $list .= ' obj_'.$sn.'.o_class_id = "'.$cid.'"';
            }

            $bwt = (!empty($list)) ? 'and ('.$list.') ' : '';

        } else
            $bwt = '';



        // Формируем запрос
        if ($this->find_page)  {

            // В режиме выборки страниц
            if ($this->permission)
                $where .= str_replace('o_id', 'obj_'.$sn.'.o_id', $rights_where);
            // $where .= $rights_where;

            if (!empty($this->cur_lang_id) && !empty($this->cur_domain_id))
                $where .= ' and pt_'.$sn.'.lang_id = "'.$this->cur_lang_id.'" and
                    		pt_'.$sn.'.domain_id = "'.$this->cur_domain_id.'" ';

            $sql = $select.'
	            		FROM
	            			<<objects>> obj_'.$sn.',
	            			<<pages>> pt_'.$sn.$tables.'
	                    WHERE pt_'.$sn.'.p_obj_id = obj_'.$sn.'.o_id and
	                    	  obj_'.$sn.'.o_to_trash = 0 '.$bwt.$where.'
	                   	GROUP BY obj_'.$sn.'.o_id';
        } else
            // В режиме выборки объектов
            $sql = $select.'
	            		FROM <<objects>> obj_'.$sn.$tables.'
	                    WHERE obj_'.$sn.'.o_to_trash = 0  '.$bwt.$where.'
	                   	GROUP BY obj_'.$sn.'.o_id';

        $sql = str_replace('.&.', $sn, $sql);
        return $sql;
    }






    // Скидывает флаг текущего элемента для getObject()
    public function reset(){
        $this->cache['num'] = -1;
    }

    // Вернет следующий по списку объект в выборке
    public function getObject(){

        if (!isset($this->cache['data']))
            $this->cache['data'] = $this->createQuery(1);

        if (!isset($this->cache['num']))
            $this->reset();

        $this->cache['num'] ++;

        if ($this->cache['num'] < count($this->cache['data'])) {
            $obj_data = $this->cache['data'][$this->cache['num']];

            if (($class = ormClasses::get($obj_data['o_class_id'])) && $class->isPage())
                return ormPages::get($obj_data);
            else
                return ormObjects::get($obj_data);

        }

    }

    // Возвращает номер объекта в выборке, во время перебора объектов через getObject()
    public function getObjectNum(){
        return $this->cache['num'];
    }

    // Возвращает количество объектов в выборке, во время переборов объектов через getObject()
    public function getObjectCount(){

        if (!isset($this->cache['data']))
            $this->cache['data'] = $this->createQuery(1);

        return count($this->cache['data']);
    }

    // Вернет данные объекта как массив
    public function getData(){
        if (!isset($this->cache['data']))
            $this->cache['data'] = $this->createQuery(1);

        return $this->cache['data'];
    }

    // Вернет количество объектов удовлетворяющих условиям в выборке без учета limit()
    public function getCount(){

        if (!isset($this->cache['count']))
            $this->cache['count'] = $this->createQuery(2);

        return $this->cache['count'];
    }


}
?>