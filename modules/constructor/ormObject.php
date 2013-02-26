<?php

function save_subject($obj, $parent_id) {
    if ($obj->id == '')
        $obj->setParent($parent_id);
    return true;
}

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс для работы с ORM-объектами.
	Нужно учитывать, что с помощью данного класса нельзя работать с ORM-страницами.
	В случае попытки класс выдаст предупреждение.
*/

class ormObject extends innerErrorList {

    protected $empty = '`19-87`';     // Последовательность символов по которой класс определяет, что данные поля не были загружены.
    protected $next_prinud = false;
    protected $del_parents = false;
    protected $id, $parent_id, $position, $name, $create_date, $change_date, $to_trash;
    protected $child_num = 0;

    protected $class;
    protected $cur_prop = Array();	// Текущие значения свойств объекта
    protected $new_prop = Array();    // Измененые (для сохранения) значения свойств объекта
    protected $fields = Array();      // Правила для работы со свойствами объекта (Список полей класса)
    protected $parents = Array();     // Родители объекта к которым он прикреплен (в которые входит)
    protected $new_parents;           // Список новых владельцев текщего объекта
    protected $childr = Array();      // Объекты прикрепленные к текущему объекту
    protected $tabu_list = Array();   // Список полей запрещенных для обработки методом loadFromPost()

    protected $links = Array();   	  // Список всех привязанных объектов для полей типа 95, 90 и пр.
    protected $links2 = Array();   	  // Список объектов к которым привязан текущий для полей типа 95, 90 и пр.
    protected $subject = Array();


    // Поля специфичные для объектов наследников класса "section"
    protected $page_fields = array('active', 'is_home_page', 'view_in_menu', 'view_submenu', 'in_search', 'in_index',
                                   'title', 'h1', 'pseudo_url', 'keywords', 'description', 'img_h1', 'img_act', 'img_no_act',
                                   'template_id', 'template2_id', 'lang_id', 'domain_id', 'in_new_window', 'other_link', 'order_by', 'number_of_items');



    // Создаем класс
    public function __construct($obj_id = 0) {

        if (!empty($obj_id) && is_numeric($obj_id)) {

            $obj_id = system::checkVar($obj_id, isInt);

            $sql = 'SELECT *
        	    		FROM <<objects>>
        				WHERE o_id = "'.$obj_id.'";';

            $row = db::q($sql, record);

            if ($row !== false && !empty($row)) {

                $this->id = $obj_id;
                $this->name = $row['o_name'];
                $this->create_date = $row['o_create_date'];
                $this->change_date = $row['o_change_date'];
                $this->to_trash = $row['o_to_trash'];
                $this->class = ormClasses::get($row['o_class_id']);

                if ($this->class->isPage())
                    $this->newError(28, 'Вы пытаетесь загрузить объект-страницу с ID = '.$obj_id.'.
                    		Используйте для этого ormPages::get();!');

            }

        } else if (is_array($obj_id) && isset($obj_id['o_id']) && isset($obj_id['o_class_id'])) {

            /*
            Загрузка данных в объект из другого источника (ormSelect::getObject()).
            Используется для уменьшения дублирующих запросов, например при выборке
            объектов одного класса.
            */

            $this->id = system::checkVar($obj_id['o_id'], isInt);
            $this->class = ormClasses::get($obj_id['o_class_id']);

            if (isset($obj_id['o_name']))
                $this->name = system::checkVar($obj_id['o_name'], isString);

            if (isset($obj_id['o_create_date']))
                $this->create_date = $obj_id['o_create_date'];

            if (isset($obj_id['o_change_date']))
                $this->change_date = $obj_id['o_change_date'];

            if (isset($obj_id['o_to_trash']))
                $this->to_trash = $obj_id['o_to_trash'];

            // Установка значений основных полей
            $this->loadFields();
            while (list($key, $field) = each ($this->fields))
                if ($field['f_type'] > 1 && $field['f_type'] < 90) {
                    $this->cur_prop[$key] = (isset($obj_id[$key])) ? $obj_id[$key] : $this->empty;
                }

            if ($this->class->isPage())
                $this->newError(28, 'Вы пытаетесь загрузить объект-страницу с ID = '.$obj_id['o_id'].'.
                    		Используйте для этого ormPages::get();!');

        } else if (!empty($obj_id))
            $this->newError(7, 'Запрашиваемый объект не найден!');

    }



    /*
        Дозагружаем данные уже созданного объекта из указанного массива.
        Метод для организации внутренного кеширования данных объектов.
    */
    public function supplementData($data) {
        if (!empty($data) && is_array($data)) {

            $this->loadFields();
            reset($this->fields);
            while (list($key, $field) = each ($this->fields))
                if ($field['f_type'] > 1 && $field['f_type'] < 90 && isset($data[$key]))
                    $this->cur_prop[$key] = $data[$key];
        }
    }

    // Загружаем данные о полях класса
    protected function loadFields() {
        if (empty($this->fields) && ($this->class instanceof ormClass))
            $this->fields = $this->class->loadFields();
    }

    // Загружаем данные о полях класса
    protected function loadData($prinud = false, $fname = '') {

        if (empty($this->cur_prop) || $prinud) {

            if (!empty($this->id) && ($this->class instanceof ormClass)) {

                $sql =  '/* '.$fname.' */
	                        SELECT *
			  				 FROM <<__'.$this->class->getSName().'>>
			        		 WHERE obj_id = "'.$this->id.'";';

                $tmp = db::q($sql, record);

                if (!empty($tmp))
                    $this->cur_prop = array_merge($this->cur_prop, $tmp);

            }
        }
    }

    // Запись свойств объекта
    public function __set($name, $value) {

        //$value = str_replace('%', '`%`', $value);

        if ($this->class instanceof ormClass) {

            $this->loadFields();

            if (isset($this->fields[$name]))
                $this->checkValue($name, $value);
        }
    }


    // Проверка значений на соответствие типам данных
    private function checkValue($field, $value) {

        $type = $this->fields[$field]['f_type'];
        $tmp = '';

        switch ($type) {

            // Строка
            case 10:
                $tmp = system::checkVar($value, isString);
                break;

            // E-mail
            case 15:
                $tmp = system::checkVar($value, isEmail);
                break;

            // URL
            case 20:
                $tmp = system::checkVar($value, isUrl);
                break;

            // Пароль
            case 35:
                $tmp2 = system::checkVar($value, isPassword);
                if (!empty($tmp2) || $tmp2 === false) $tmp = $tmp2;
                break;

            // Число
            case 40:
                $tmp = system::checkVar($value, isInt);
                break;

            // Позиция в списке
            case 65:
                $tmp = system::checkVar($value, isInt);
                break;

            // Число  с точкой
            case 45:
                $tmp = system::checkVar($value, isNum);
                break;

            // Число  с точкой
            case 47:
                $tmp = system::checkVar($value, isPrice);
                break;

            // Галочка (логический)
            case 50:
                $tmp = system::checkVar($value, isBool);
                break;



            // Дата
            case 25:

                if (!empty($value))
                    $tmp = system::checkVar($value, isDate);

                break;

            // Время
            case 30:
                $tmp = system::checkVar($value, isDateTime);
                break;

            // Дата и Время
            case 32:
                if (!empty($value))
                    $tmp = system::checkVar($value, isDateTime);
                break;

            // Большой текст
            case 55:
                $value = str_replace(array('<', '>'), array('&lt;', '&gt;'), $value);
                $tmp = system::checkVar($value, isText);
                break;

            // HTML – текст
            case 60:
                $tmp = system::checkVar($value, isText);
                break;



            // Файл
            case 70:
                $tmp = $this->checkLoadFile($field, $value, '', '', $this->fields[$field]['f_max_size']);
                break;

            // Список файлов
            case 73:

                if (isset($_FILES[$value]) && count($_FILES[$value]['name']) > 0) {

                    $file_var = $_FILES[$value];

                    $tmp = $value;

                    for ($i = 0; $i < count($file_var['name']); $i++) {

                        if (isset($file_var['tmp_name'][$i]) && file_exists($file_var['tmp_name'][$i])){

                            if (!empty($file_var['error'][$i]))

                                $this->newError(30, 'Произошла ошибка при загрузке файлов для поля "'.$field.'"!');

                        } else $tmp = false;
                    }
                }

                break;

            // Изображение
            case 75:
                $tmp = $this->checkLoadFile($field, $value,
                                            array('png', 'gif', 'jpg', 'jpeg'),
                                            'jpg, jpeg, gif, png',
                                            $this->fields[$field]['f_max_size']);
                break;

            // Видео
            case 80:
                $tmp = $this->checkLoadFile($field, $value,
                                            array('avi', 'mpeg'),
                                            'avi, mpeg',
                                            $this->fields[$field]['f_max_size']);
                break;

            // Флеш-ролик
            case 85:
                $tmp = $this->checkLoadFile($field, $value,
                                            array('flv'),
                                            'flv',
                                            $this->fields[$field]['f_max_size']);
                break;

            // Выпадающий список
            case 90:
                if (empty($value)) $value = 0;
                $tmp = system::checkVar($value, isString);
                break;

            // Список c множественным выбором
            case 95:
                if (empty($value)) $value = array();
                $tmp = (is_array($value)) ? $value : false;
                break;

            // Ссылка на дерево
            case 100:
                if (empty($value)) $value = array();
                $tmp = (is_array($value)) ? $value : false;
                break;

            // Теги
            case 105:
                $tmp = system::checkVar($value, isString);
                break;

            default:
                $this->newError(31, 'Тип данных поля "'.$field.'" не поддерживается системой!', $field);


        }

        // Проверка уникальности значения поля
        if ($this->fields[$field]['f_uniqum']) {

            $class_name = ($this->class->isInheritor('user')) ? 'user' : $this->class->getSName();
            $sel = new ormSelect($class_name);
            $sel->where($field, '=', $tmp);
            if (!empty($this->id))
                $sel->where('id', '<>', $this->id);

            if ($sel->getCount() > 0)
                if ($field == 'name')
                    $this->newError(29, 'В системе уже есть объект с указанным именем. Выберите другое имя.', 'name');
                else
                    $this->newError(32, 'Поле "'.$field.'" должно содержать уникальное не повторяющееся значение. Укажите другое значение.', $field);
        }




        if (!((empty($tmp) && ($type == 35 || $type == 25 || $type == 32)) || $this->issetErrors()))

            if ($tmp !== false){

                if ($field == 'name')
                    $this->name = $tmp;

                $this->new_prop[$field] = $tmp;

            } else {

                $this->newError(33, 'Неправильно указано значение для поля "'.$type.'"!', $field);
            }

    }

    // Проверка корректности ссылки на файл или загрузки файла
    private function checkLoadFile($field, $value, $exe = 0, $text = '', $max_size = 0) {


        if (system::checkVar($value, isAbsUrl)) {

            $file = true;

        } else {

            // Проверяем загружали файл или нет
            $file = system::checkLoadFile('file_'.$field, $exe, $max_size);

            if (!empty($file) && $file !== false && $file !== true) {

                $this->newError(30, 'Произошла ошибка при загрузке файла для поля "'.$field.'"!');

            } else if ($file === false) {

                // Проверяем была ли указана сслыка на файл
                $value = str_replace('..', '', $value);

                if (!empty($value) && $value != $this->empty)
                    $file = system::checkLoadedFile($value, $exe, $max_size);
                else $file = true;

                if ($file === false)
                    $this->newError(34, 'Указанный для поля "'.$field.'" файл не найден на сервере!');
            }
        }

        if ($file === 0)
            $this->newError(35, 'Не поддерживаемый тип файла для поля "'.$field.'"! Разрешенные типы файлов: '.$text.'.');
        else if ($file === 5)
            $this->newError(36, 'Файл для поля "'.$field.'" превышает максимально допустимый размер '.round(($max_size/1024), 0).' Кб.');
        else if ($file === true)
            return $value;
    }


    // Чтение свойств объекта
    public function __get($name) {

        if ($name == 'name')

            return $this->name;

        else if ($name == 'id')

            return $this->id;

        else if ($name == 'create_date')

            return $this->create_date;

        else if ($name == 'change_date')

            return $this->change_date;

        else if ($name == 'parents')

            return $this->getParents();

        else if ($name == 'parent_id')

            return $this->getParentId();

        else if ($name == 'children')

            return $this->getChildren();

        else if ($name == 'first_children_id')

            return $this->getChildren(0);

        else if ($name == 'count_children')

            return count($this->getChildren());

        else {

            // Смотрим, есть ли флаг для справочников, о получении имени
            if (substr($name, 0, 1) == '_') {
                $name = substr($name, 1, strlen($name) - 1);
                $get_name = true;
            } else $get_name = false;
            // echo '='.$name.'=';
            $this->loadFields();

            if (isset($this->fields[$name])) {

                $this->loadData($this->next_prinud, $name);
                $this->next_prinud = false;

                // $values2 = (empty($this->cur_prop)) ? 1 : 2;
                // Если текущих значений у объекта нет, считаем временные значения текущими
                // и делаем из них выборку
                $values = (empty($this->cur_prop)) ? $this->new_prop : $this->cur_prop;

                //if ($name == 'tags')
                //  print_r($values);
                //   echo $values2.'!';
                if (isset($values[$name]) || ($get_name && isset($values['_'.$name]))){

                    // Если значение не прогружено, загружаем все данные объекта
                    if (!$get_name) {


                        if ($values[$name] === $this->empty) {
                            $this->loadData(true, $name);
                            $values = $this->cur_prop;
                        }

                        // Отбрасываем лишние нули после запятой для поля типа "Цена"
                        if ($this->fields[$name]['f_type'] == 47)
                            $values[$name] += 0;

                        //if (isset($values[$name]))
                            return $values[$name];

                    } else if (isset($values['_'.$name]))
                        return $values['_'.$name];

                } else  {

                    // Теги
                    if ($this->fields[$name]['f_type'] == 105 && !empty($this->id)) {

                        $this->cur_prop[$name] = tags::getTagsForObject($this->id);
                        $this->cur_prop['_'.$name] = '';

                        if (!empty($this->cur_prop[$name])) {
                            while (list($num, $tag) = each($this->cur_prop[$name])) {
                                $zpt = (empty($num)) ? '' : ', ';
                                $this->cur_prop['_'.$name] .= $zpt.$tag['name'];
                            }
                            reset($this->cur_prop[$name]);
                        }

                        if ($get_name) {
                            if (isset($this->cur_prop['_'.$name]))
                                return $this->cur_prop['_'.$name];
                        } else
                            if (isset($this->cur_prop[$name]))
                                return $this->cur_prop[$name];

                        // Справочник "Выбор родителя"
                    } else if ($this->fields[$name]['f_relation'] == 2) {

                        // Ищем родителя нужного класса
                        $this->getParents();

                        $ret_mas = array();

                        if (count($this->parents) > 0) {
                            reset($this->parents);

                            while (list($obj_id, $val) = each($this->parents))
                                if (empty($val['parent_class']) || $val['parent_class'] == $this->fields[$name]['f_list_id'])
                                    $ret_mas[] = $val;
                        }

                        return $ret_mas;

                        // Выпадающий список
                    } else if ($this->fields[$name]['f_type'] == 90) {

                        /*
            $this->cur_prop['_'.$name] = '';
            $this->cur_prop[$name] = 0;
                        */
                        if ($this->fields[$name]['f_relation'] > 0) {

                            $this->getLinks2();

                            if (isset($this->links2[$this->fields[$name]['f_id']][0])) {
                                $this->cur_prop[$name] = $this->links2[$this->fields[$name]['f_id']][0]['id'];
                                $this->cur_prop['_'.$name] = $this->links2[$this->fields[$name]['f_id']][0]['name'];
                            }

                        } else {

                            $this->getLinks();

                            if (isset($this->links[$this->fields[$name]['f_id']][0])) {
                                $this->cur_prop[$name] = $this->links[$this->fields[$name]['f_id']][0]['id'];
                                $this->cur_prop['_'.$name] = $this->links[$this->fields[$name]['f_id']][0]['name'];
                            }
                        }

                        if ($get_name) {
                            if (isset($this->cur_prop['_'.$name]))
                                return $this->cur_prop['_'.$name];
                        } else
                            if (isset($this->cur_prop[$name]))
                                return $this->cur_prop[$name];

                        // Вып. список с множ. выбором
                    } else if ($this->fields[$name]['f_type'] == 95 || $this->fields[$name]['f_type'] == 100) {
                        /*

           $this->cur_prop[$name] = array();
                        */
                        if ($this->fields[$name]['f_relation'] > 0) {
                            $this->getLinks2();
                            $links = $this->links2;
                        } else {
                            $this->getLinks();
                            $links = $this->links;
                        }

                        if (isset($links[$this->fields[$name]['f_id']])) {

                            $this->cur_prop['_'.$name] = '';

                            while(list($num, $val) = each($links[$this->fields[$name]['f_id']])) {

                                $this->cur_prop[$name][] = $val['id'];

                                if (!empty($this->cur_prop['_'.$name]))
                                    $this->cur_prop['_'.$name] .= ', ';

                                $this->cur_prop['_'.$name] .= $val['name'];
                            }
                        }

                        if ($get_name) {
                            if (isset($this->cur_prop['_'.$name]))
                                return $this->cur_prop['_'.$name];
                        } else
                            if (isset($this->cur_prop[$name]))
                                return $this->cur_prop[$name];

                        // Зависимый справочник
                    } else if ($this->fields[$name]['f_type'] == 97) {

                        if ($get_name)
                            return '';
                        else
                            return array();

                        // Связь с объектом
                    } else if ($this->fields[$name]['f_type'] == 100) {

                        if ($get_name)
                            return '';
                        else
                            return array();
                    }

                }

            } else return false;

        }

    }


    // Получаем ID всех привязанных объектов
    protected function getLinks(){

        if (empty($this->links) && $this->id != '') {

            $sql =  'SELECT r_field_id, r_children_id, o_name
        				 FROM <<rels>>, <<objects>>
						 WHERE r_parent_id = "'.$this->id.'" and
							   r_children_id = o_id and
							   r_field_id IS NOT NULL;';

            $tmp = db::q($sql, records);

            $this->links = array();
            while(list($key, $val) = each($tmp)) {

                if (!isset($this->links[$val['r_field_id']]))
                    $this->links[$val['r_field_id']] = array();

                $this->links[$val['r_field_id']][] = array(
                    'id' => $val['r_children_id'],
                    'name' => $val['o_name']
                );
            }
        }
    }

    // Получаем ID всех объектов к которым привязанны
    protected function getLinks2(){

        if (empty($this->links2) && $this->id != '') {

            $sql =  'SELECT r_field_id, r_parent_id, o_name
        				 FROM <<rels>>, <<objects>>
						 WHERE r_children_id = "'.$this->id.'" and
							   r_parent_id = o_id and
							   r_field_id IS NOT NULL;';

            $tmp = db::q($sql, records);

            $this->links2 = array();
            while(list($key, $val) = each($tmp)) {

                if (!isset($this->links2[$val['r_field_id']]))
                    $this->links2[$val['r_field_id']] = array();

                $this->links2[$val['r_field_id']][] = array(
                    'id' => $val['r_parent_id'],
                    'name' => $val['o_name']
                );
            }
        }
    }



    // Чтение новых (не сохраненных) значений свойств объекта
    public function newVal($name) {

        if ($name == 'name')
            return $this->name;
        else if ($name == 'id')
            return $this->id;
        else
            return (isset($this->new_prop[$name])) ? $this->new_prop[$name] : '';
    }



    // Добавление новый или изменяет свойства существующего объекта
    public function save(){

        if(!($this->class instanceof ormClass))
            $this->newError(37, 'Невозможно создать/изменить объект, т.к. не определен класс для объекта!');
        else if ($this->to_trash)
            $this->newError(38, 'Вы не можете изменить объект помеченный для удаления!');
        // else if(empty($this->name))
        //	$this->newError(39, 'Имя объекта не может быть пустым!', 'name');

        if ($this->issetErrors()) {

            // характеристики не соответствуют требованиям
            return false;

        } else if (!empty($this->id)) {

            // Изменяем свойства объекта
            return $this->changeObject();

        } else {

            // Добавляем новый объект
            return $this->createObject();

        }

    }


    protected function isPageField($sname){
        return (in_array($sname, $this->page_fields) && $this->isInheritor('section')) ? true : false;
    }

    // Изменение объекта
    protected function changeObject(){

        // Смотрим, если не было изменений полей, изменяем только Родителя
        if (empty($this->new_prop)) {
            $this->changeParents();
            return $this->id;
        }

        $this->loadData();

        // Проверяем обязательность полей
        reset($this->fields);
        while (list($fname, $field) = each ($this->fields)) {

            if ($field['f_required'] && $field['f_relation'] == 2) {

                if (empty($this->new_parents) && !$this->issetParents())
                    $this->newError(40, 'Поле "'.$field['f_name'].'" обязательно для заполнения!', $fname);

            } else if (($field['f_type'] == 40 || $field['f_type'] == 45) && isset($this->new_prop[$fname]) && empty($this->new_prop[$fname]))
                $this->new_prop[$fname] = 0;
            else if ($field['f_required'] && empty($this->new_prop[$fname]) && ($this->__get($fname) == '' || isset($this->new_prop[$fname])))
                $this->newError(40, 'Поле "'.$field['f_name'].'" обязательно для заполнения!', $fname);
        }

        if ($this->issetErrors() > 0)
            return false;

        $change = true;

        // Изменяем данные объекта
        if (!empty($this->new_prop))  {

            $fields = '';
            reset($this->new_prop);
            while (list($key, $value) = each ($this->new_prop))
                if (!$this->isPageField($key) && $key != 'name')
                    if ($this->fields[$key]['f_type'] != 105)
                        $fields .= $this->procValue($key, $value);
                    else
                        tags::changeTags($value, $this->id);

            $fields = substr($fields, 0, strlen($fields)-2);

            if (!empty($fields)) {
                $sql = 'UPDATE <<__'.$this->class->getSName().'>>
							SET '.$fields.'
							WHERE obj_id = "'.$this->id.'";';
                $change = (db::q($sql) !== false) ? true : false;
            }
        }

        if($change) {

            $sql = 'UPDATE <<objects>>
						SET o_name = "'.$this->name.'",
							o_change_date = "'.date('Y-m-d H:i:s').'"
						WHERE o_id = "'.$this->id.'";';

            if (db::q($sql, 0, 0) !== false) {

                // Все хорошо, обновляем данные объекта
                reset($this->new_prop);
                while (list($key, $value) = each($this->new_prop))
                    $this->cur_prop[$key] = $value;

                while (list($key, $field_id) = each ($this->subject)) {
                    $form = new ormMultiForm('subject_list_'.$field_id);
                    $form->process('save_subject', $this->id);
                }

                $this->changeParents();

                return $this->id;

            } else{
                $this->newError(41, 'Ошибка в SQL запросе, при обновлении данных объекта!');
                return false;
            }

        } else {
            $this->newError(41, 'Ошибка в SQL запросе, при обновлении данных объекта!');
            return false;
        }


    }

    // Создание нового объекта    -		-		-		-		-		-		-		-
    protected function createObject(){

        // Проверяем обязательность полей
        reset($this->fields);
        while (list($fname, $field) = each ($this->fields))

            if ($field['f_required'] && $field['f_relation'] == 2) {

                if (empty($this->new_parents) && !$this->issetParents())
                    $this->newError(40, 'Поле "'.$field['f_name'].'" обязательно для заполнения!', $fname);

            } else if (($field['f_type'] == 40 || $field['f_type'] == 45) && isset($this->new_prop[$fname]) && empty($this->new_prop[$fname]))
                $this->new_prop[$fname] = 0;
            else if ($field['f_required'] && empty($this->new_prop[$fname]) && (empty($this->cur_prop[$fname]) || isset($this->new_prop[$fname])))
                $this->newError(40, 'Поле "'.$field['f_name'].'" обязательно для заполнения!', $fname);

        if ($this->issetErrors() > 0)
            return false;


        // Основные характеристики объекта
        $sql = 'INSERT INTO <<objects>>
					SET o_class_id = "'.$this->class->id().'",
						o_name = "'.$this->name.'",
						o_change_date = "'.date('Y-m-d H:i:s').'",
						o_create_date = "'.date('Y-m-d H:i:s').'";';

        $this->id = db::q($sql);

        if ($this->id === false) {
            $this->newError(42, 'Ошибка в SQL запросе, при добавлении объекта!');
            return false;
        }

        // Изменяем данные объекта
        $fields = '';
        if (!empty($this->new_prop))
            while (list($key, $value) = each ($this->new_prop))
                if (!$this->isPageField($key) && $key != 'name')
                    if ($this->fields[$key]['f_type'] != 105)
                        $fields .= $this->procValue($key, $value);
                    else
                        tags::changeTags($value, $this->id);

        $fields = substr($fields, 0, strlen($fields)-2);

        if (!empty($fields)) $fields = ', '.$fields;

        $sql = 'INSERT INTO  <<__'.$this->class->getSName().'>>
					SET obj_id = "'.$this->id.'"'.$fields.';';

        if (db::q($sql) !== false) {
            /*
        if (!empty($this->new_prop))
            while (list($key, $value) = each ($this->new_prop))
                $this->procValue($key, $value);
            */

            while (list($key, $field_id) = each ($this->subject)) {
                $form = new ormMultiForm('subject_list_'.$field_id);
                $form->process('save_subject', $this->id);
            }

            $this->next_prinud = true;
            $this->changeParents(false);

            return $this->id;

        } else {
            $this->newError(42, 'Ошибка в SQL запросе, при добавлении данных объекта!');
            return false;
        }
    }

    // Изменение родителя объекта
    protected function changeParents($isUpd = true) {

        $ret = true;

        if ((!empty($this->new_parents) || $this->del_parents) && is_array($this->new_parents)) {

            // Удаляем все старые связи
            if ($isUpd && $this->del_parents)
                $ret = db::q('DELETE FROM <<rels>> WHERE r_children_id = "'.$this->id.'" and r_field_id is NULL;');
            $this->del_parents = false;

            // Добавляем новые связи
            reset($this->new_parents);
            while(list($key, $val) = each($this->new_parents)) {

                if (empty($val['parent_id'])){
                    $parent_sql = 'r_parent_id is NULL and';
                    $parent_sql2 = '';
                } else {
                    $parent_sql = 'r_parent_id = "'.$val['parent_id'].'" and';
                    $parent_sql2 = 'r_parent_id = "'.$val['parent_id'].'",';
                }


                if (!$isUpd || empty($val['position'])) {

                    // Определяем позицию при добавлении объекта
                    $sql = 'SELECT MAX(r_position)
	                    		FROM <<rels>>, <<objects>>, <<classes>>
			        			WHERE '.$parent_sql.'
			        				  r_field_id is NULL and
			        				  r_children_id = o_id and
			        				  o_class_id = c_id and
			        				  c_is_page = 0;';

                    $val['position'] = db::q($sql, value) + 1;

                } else if ($isUpd && !empty($val['position'])) {

                    $parent_sql = 'r.'.$parent_sql;
                    // Изменения при обновлении объекта
                    $old_pos = (isset($this->parents[$val['parent_id']])) ? $this->parents[$val['parent_id']]['position'] : 0;

                    if (empty($old_pos) && !empty($val['position'])) {

                        // Если добавили нового родителя
                        db::q('UPDATE <<rels>> r, <<objects>> o, <<classes>> c
								   SET r.r_position = r.r_position + 1
			                       WHERE r.r_position >= "'.$val['position'].'" and
			                             '.$parent_sql.'
			        				  	 r.r_field_id is NULL and
			        				  r.r_children_id = o.o_id and
			        				  o.o_class_id = c.c_id and
			        				  c.c_is_page = 0;');

                    } else if ($val['position'] < $old_pos) {

                        // Если перенесли ниже по списку
                        db::q('UPDATE <<rels>> r, <<objects>> o, <<classes>> c
		             			   SET r.r_position = r.r_position + 1
		                           WHERE r.r_position >= "'.$val['position'].'" and
		                                 r.r_position < "'.$old_pos.'" and
		                                 '.$parent_sql.'
			        				  	 r.r_field_id is NULL and
			        				  r.r_children_id = o.o_id and
			        				  o.o_class_id = c.c_id and
			        				  c.c_is_page = 0;');

                    } else if ($val['position'] > $old_pos) {

                        // Если перенесли выше по списку
                        db::q('UPDATE <<rels>> r, <<objects>> o, <<classes>> c
		              			   SET r.r_position = r.r_position - 1
		                           WHERE r.r_position <= "'.$val['position'].'" and
		                                 r.r_position > "'.$old_pos.'" and
		                                 '.$parent_sql.'
			        				  	 r.r_field_id is NULL and
			        				  r.r_children_id = o.o_id and
			        				  o.o_class_id = c.c_id and
			        				  c.c_is_page = 0;');
                    }
                }

                if (empty($tmp_parent_id)) {
                    $tmp_parent_id = $val['parent_id'];
                    $tmp_position = $val['position'];
                }

                // Добавляем связь с родителем
                $sql = 'INSERT INTO <<rels>>
							SET '.$parent_sql2.'
								r_position = "'.$val['position'].'",
								r_children_id = "'.$this->id.'";';

                $ret = db::q($sql);
            }
        }

        // Если все нормально, обновляем свойства объекта
        if ($ret === false) {

            $this->newError(43, 'Произошла ошибка при изменении родителя объекта!');

        } else if (is_array($this->new_parents) && !empty($this->new_parents)) {

            $this->parents = $this->new_parents;
            $this->new_parents = array();

            if (!empty($tmp_parent_id)) {
                $this->parent_id = $tmp_parent_id;
                $this->position = $tmp_position;
            }
        }


    }

    // Специальная обработка некоторых типов данных
    protected function procValue($field, $value) {

        $type = $this->fields[$field]['f_type'];

        switch ($type) {

            // Файл
            case 70:
				
				$cur_file = $this->__get($field);
				
                if (isset($_FILES['file_'.$field])) {
                    $tmp = system::copyFile($_FILES['file_'.$field]['tmp_name'], $_FILES['file_'.$field]['name'], '/upload/file');
                    $value = (empty($tmp)) ? $value : $tmp;
                }
				
                if (system::fileName($value) != system::fileName($cur_file)) {
					@unlink(ROOT_DIR.$cur_file); //удаляем прошлый файл
                }
				
                return '`'.$field.'` = "'.$value.'", ';

                break;

            // Список файлов
            case 73:
                $file_var = $_FILES[$value];
                //  print_r($file_var);
                $fn = '';
                for ($i = 0; $i < count($file_var['name']); $i++) {
                    // echo $file_var['tmp_name'][$i].' - '.$file_var['name'][$i];
                    $fn .= system::copyFile($file_var['tmp_name'][$i], $file_var['name'][$i], '/upload/file').';';
                }
                return '`'.$field.'` = "'.$fn.'", ';
                break;

            // Изображение
            case 75:

                $cur_file = $this->__get($field);

                if (isset($_FILES['file_'.$field])) {
                    $tmp = system::copyFile($_FILES['file_'.$field]['tmp_name'], $_FILES['file_'.$field]['name'], '/upload/image');
                    $value = (empty($tmp)) ? $value : $tmp;
                }

                // Удаляем КЭШ рисунков, если были изменения
                if (system::fileName($value) != system::fileName($cur_file)) {
                    $this->preResizeJpeg($value);
                    $this->deleteCacheImages(system::fileName($cur_file));
					@unlink(ROOT_DIR.$cur_file); //удаляем прошлый файл
                }

                return '`'.$field.'` = "'.$value.'", ';

                break;

            // Видео
            case 80:

				$cur_file = $this->__get($field);
				
                if (isset($_FILES['file_'.$field])) {
                    $tmp = system::copyFile($_FILES['file_'.$field]['tmp_name'], $_FILES['file_'.$field]['name'], '/upload/media');
                    $value = (empty($tmp)) ? $value : $tmp;
                }

				if (system::fileName($value) != system::fileName($cur_file)) {
					@unlink(ROOT_DIR.$cur_file); //удаляем прошлый файл
                }
				
                return '`'.$field.'` = "'.$value.'", ';
                break;

            // Флеш-ролик
            case 85:
				
				$cur_file = $this->__get($field);
				
                if (isset($_FILES['file_'.$field])) {
                    $tmp = system::copyFile($_FILES['file_'.$field]['tmp_name'], $_FILES['file_'.$field]['name'], '/upload/flash');
                    $value = (empty($tmp)) ? $value : $tmp;
                }

				if (system::fileName($value) != system::fileName($cur_file)) {
					@unlink(ROOT_DIR.$cur_file); //удаляем прошлый файл
                }
				
                return '`'.$field.'` = "'.$value.'", ';
                break;

            // Теги
            case 105:
                // ....
                break;


            // Выпадающий список
            case 90:

                $class_id = $this->fields[$field]['f_list_id'];

                if (ormClasses::get($class_id)->isPage())
                    $isset_obj = (($obj = ormPages::get($value, $class_id)) || empty($value));
                else
                    $isset_obj = (($obj = ormObjects::get($value, $class_id)) || empty($value));


                // Если необходимо добавляем новое значение в справочник
                if (!$isset_obj && $this->fields[$field]['f_quick_add']) {
                    $obj = new ormObject();
                    $obj->setClass($class_id);
                    $obj->name = $value;
                    $value = $obj->save();
                    $isset_obj = ($value);
                }



                if ($isset_obj && (is_numeric($value) || empty($value))) {

                    // Удаляем все старые связи
                    if ($this->fields[$field]['f_relation'] > 0)
                        $sql = 'DELETE FROM <<rels>>
									WHERE r_children_id = "'.$this->id.'" and
										  r_field_id = "'.$this->fields[$field]['f_id'].'";';
                    else
                        $sql = 'DELETE FROM <<rels>>
									WHERE r_parent_id = "'.$this->id.'" and
										  r_field_id = "'.$this->fields[$field]['f_id'].'";';
                    db::q($sql);

                    // Добавляем новые связи
                    if (!empty($value)) {

                        if ($this->fields[$field]['f_relation'] > 0)
                            $sql = 'INSERT INTO <<rels>>
										SET r_parent_id = "'.$value.'",
											r_children_id = "'.$this->id.'",
											r_field_id = "'.$this->fields[$field]['f_id'].'";';
                        else
                            $sql = 'INSERT INTO <<rels>>
										SET r_parent_id = "'.$this->id.'",
											r_children_id = "'.$value.'",
											r_field_id = "'.$this->fields[$field]['f_id'].'";';
                        db::q($sql);
                    }
                }

                break;

            // Список c множественным выбором
            case 95:

                // Удаляем все старые связи
                if ($this->fields[$field]['f_relation'] > 0)
                    $sql = 'DELETE FROM <<rels>>
								WHERE r_children_id = "'.$this->id.'" and
									  r_field_id = "'.$this->fields[$field]['f_id'].'";';
                else
                    $sql = 'DELETE FROM <<rels>>
								WHERE r_parent_id = "'.$this->id.'" and
									  r_field_id = "'.$this->fields[$field]['f_id'].'";';
                db::q($sql);

                // Добавляем новые связи
                foreach($value as $val){

                    $class_id = $this->fields[$field]['f_list_id'];

                    if (ormClasses::get($class_id)->isPage())
                        $isset_obj = (empty($val) || ($obj = ormPages::get($val, $class_id)));
                    else
                        $isset_obj = (empty($val) || ($obj = ormObjects::get($val, $class_id)));


                    // Если необходимо добавляем новое значение в справочник
                    if (!$isset_obj && $this->fields[$field]['f_quick_add']) {
                        $obj = new ormObject();
                        $obj->setClass($class_id);
                        $obj->name = $val;
                        $val = $obj->save();
                        $isset_obj = ($val);
                    }

                    if ($isset_obj && (is_numeric($val) || empty($val))) {

                        if ($this->fields[$field]['f_relation'] > 0) {

                            if (empty($val))
                                $sql = 'INSERT INTO <<rels>>
                                            SET r_parent_id = NULL,
                                                r_children_id = "'.$this->id.'",
                                                r_field_id = "'.$this->fields[$field]['f_id'].'";';
                            else
                                $sql = 'INSERT INTO <<rels>>
                                            SET r_parent_id = "'.$val.'",
                                                r_children_id = "'.$this->id.'",
                                                r_field_id = "'.$this->fields[$field]['f_id'].'";';
                            db::q($sql);

                        } else if (!empty($val)) {

                            $sql = 'INSERT INTO <<rels>>
										SET r_parent_id = "'.$this->id.'",
											r_children_id = "'.$val.'",
											r_field_id = "'.$this->fields[$field]['f_id'].'";';
                            db::q($sql);
                        }


                    } //else print_r($obj->getErrorList());
                }

                break;

            // Ссылка на дерево
            case 100:

                // Удаляем все старые связи
                if ($this->fields[$field]['f_relation'] > 0)
                    $sql = 'DELETE FROM <<rels>>
								WHERE r_children_id = "'.$this->id.'" and
									  r_field_id = "'.$this->fields[$field]['f_id'].'";';
                else
                    $sql = 'DELETE FROM <<rels>>
								WHERE r_parent_id = "'.$this->id.'" and
									  r_field_id = "'.$this->fields[$field]['f_id'].'";';
                db::q($sql);

                // Добавляем новые связи
                foreach($value as $val){

                    if ($val !== false && !empty($val) && ($obj = ormPages::get($val))) {

                        if ($this->fields[$field]['f_relation'] > 0)
                            $sql = 'INSERT INTO <<rels>>
										SET r_parent_id = "'.$val.'",
											r_children_id = "'.$this->id.'",
											r_field_id = "'.$this->fields[$field]['f_id'].'";';
                        else
                            $sql = 'INSERT INTO <<rels>>
										SET r_parent_id = "'.$this->id.'",
											r_children_id = "'.$val.'",
											r_field_id = "'.$this->fields[$field]['f_id'].'";';
                        db::q($sql);
                    }
                }

                break;

            default:
                return '`'.$field.'` = "'.$value.'", ';

        }
    }

    // Делаем предварительное изменение размеров для Jpeg, экономим врямя для последующих ресайзов
    private function preResizeJpeg($file) {

        if (system::fileExtIs($file, array('jpeg', 'jpg')) && reg::getKey('/core/scaleBigJpeg')) {

            $ava = imagecreatefromjpeg(ROOT_DIR.$file);
            $x = imagesx($ava);
            $y = imagesy($ava);

            $sra = ($x > $y ? $x : $y);

            if ($sra > reg::getKey('/core/sizeBigJpeg')) {
                $qr = $sra / reg::getKey('/core/sizeBigJpeg');
                $nx = round($x / $qr);
                $ny = round($y / $qr);
                $sava = imagecreatetruecolor($nx, $ny);
                imagecopyresized($sava, $ava, 0, 0, 0, 0, $nx, $ny, $x, $y);
                imagedestroy($ava);
                imagejpeg($sava, ROOT_DIR.$file);
                imagedestroy($sava);

            } else imagedestroy($ava);

        }
    }

    // Удаляем кешированые миниатюры изображений
    private function deleteCacheImages($del_file, $from_path = '/cache/img/') {

        if (is_dir(ROOT_DIR.$from_path) && !empty($del_file)) {

            chdir(ROOT_DIR.$from_path);
            $handle = opendir('.');

            while (($file = readdir($handle)) !== false) {
                if ($file != "." && $file != "..") {

                    if (is_dir(ROOT_DIR.$from_path.$file)) {
                        $this->deleteCacheImages($del_file, $from_path.$file.'/');
                        chdir(ROOT_DIR.$from_path);
                    }

                    if (is_file(ROOT_DIR.$from_path.$file) && $file == $del_file)
                        @unlink(ROOT_DIR.$from_path.$file);
                }
            }
            closedir($handle);
        }
    }


    // Удаление объекта данных       -		-		-		-		-		-		-		-
    public function delete(){

		
		
        if (!empty($this->id)) {

            // Удаляем вложенные объекты. Только тех, которые не имеют нескольких родителей.
            $this->resetChild();
            while($obj = $this->getChild(true))
                if (count($obj->getParents(true)) < 2)
                    $obj->delete();
			
			foreach ($this->getClass()->loadFields() as $name => $field) {
				if (in_array($field['f_type'], array(70, 75, 80, 85))) {
					
					@unlink(ROOT_DIR.$this->$name);
					
					if ($field['f_type'] == 75) {
						$this->deleteCacheImages(system::fileName($this->$name));
					}
				}
			}
			
            $ret = db::q('DELETE FROM <<objects>> WHERE o_id = "'.$this->id.'";');

            if($ret === false) {
                $this->newError(12, 'Ошибка в SQL запросе!');
                return false;
            }

            return true;

        } else return false;

    }

    // Вернет 1 - если объект помечен на удаление, 0 - если нет.
    public function inTrash(){
        return $this->to_trash;
    }

    // Помещает объект в мусорную корзину (помечает на удаление)
    public function toTrash(){

        if (!empty($this->id)) {

            if (reg::getKey('/core/delToTrash')){

                $ret = db::q('UPDATE <<objects>> SET o_to_trash = 1 WHERE o_id = "'.$this->id.'";');

                if($ret === false) {
                    $this->newError(12, 'Ошибка в SQL запросе!');
                    return false;
                }

                system::revue($this, lang::get('REVUE_TO_TRASH'), info, 1);
                return true;

            } else $this->delete();

        } else return false;
    }

    // Восстанавливает объект из корзины
    public function restore(){

        if (!empty($this->id)) {

            $ret = db::q('UPDATE <<objects>> SET o_to_trash = 0 WHERE o_id = "'.$this->id.'";');

            if($ret === false) {
                $this->newError(12, 'Ошибка в SQL запросе!');
                return false;
            }

            system::revue($this, lang::get('REVUE_RESTORE'), info);

            return true;

        } else return false;
    }



    // Устанавливает поля, которые не будут автоматически обработаны методом loadFromPost()
    public function tabuList() {
        $this->tabu_list = func_get_args();
    }

    public function parseAllFields($prefix = 'obj') {

        if($this->class instanceof ormClass) {

            $this->loadFields();
            $this->loadData();

            if (!empty($prefix))
                $prefix .= '.';

            reset($this->fields);
            while (list($key, $field) = each ($this->fields))
                if ($field['f_type'] > 2)
                    page::assign($prefix.$key, $this->__get($key));
        }
    }

    // Автоматически сохраняет данные объекта пришедшие через $_POST
    public function loadFromPost() {

        if($this->class instanceof ormClass) {

            $this->loadFields();
            $this->loadData();
            // print_r($_POST);
            reset($this->fields);
            while (list($key, $field) = each ($this->fields)) {

                if ($field['f_view'] && !in_array($key, $this->tabu_list))

                    if ($field['f_type'] == 97) {

                        // Подчиненный справочник
                        $this->subject[] = $field['f_id'];

                    } else if (($field['f_type'] > 89 && $field['f_type'] < 101) && $field['f_relation'] == 2 && isset($_POST[$key])) {

                        // Справочник с типом "Выбор родителя"
                        $this->clearParents();
                        $ps = $this->getParents();
                        $parents = $_POST[$key];

                        if (!empty($parents)) {
                            if (is_numeric($parents) && !empty($parents)) {

                                $pos = (isset($ps[$parents])) ? $ps[$parents]['position'] : 0;
                                $this->setNewParent($parents, $pos);

                            } else if (is_array($parents)) {

                                while(list($key, $val) = each($parents))
                                    if (!empty($val)) {
                                        $pos = (isset($ps[$val])) ? $ps[$val]['position'] : 0;
                                        $this->setNewParent($val, $pos);
                                    }
                            }
                        }

                    } else if (isset($_POST[$key])) {

                        $this->__set($key, $_POST[$key]);

                    } else if ($field['f_type'] == 50 && !isset($_POST[$key])) {

                        // Галочка
                        $this->__set($key, false);

                    } else if (!empty($_POST[$key.'_date']) && isset($_POST[$key.'_time'])) {

                        // Дата и время
                        $datetime = $_POST[$key.'_date'].' '.$_POST[$key.'_time'].':00';
                        $this->__set($key, $datetime);

                    } else if(isset($_FILES['file_list_'.$key])) {

                        // Список файлов
                        $this->__set($key, 'file_list_'.$key);

                    }
            }
        }
    }

    // Обнуляет счетчик для метода getChild()
    public function resetChild(){
        $this->child_num = 0;
    }

    // Вернет следующий по списку вложенный объект (Есть аналогичный метод в ormPage)
    public function getChild($with_trash = false){

        $this->getChildren(-1, $with_trash);

        if (count($this->childr) > 0) {
            if (isset($this->childr[$this->child_num]))  {

                $obj = ormObjects::get($this->childr[$this->child_num]);

                if (!($obj instanceof ormObject))
                    $obj = ormPages::get($this->childr[$this->child_num]);

                if ($obj instanceof ormObject) {

                    $this->child_num++;
                    if ($this->child_num > count($this->childr))
                        $this->child_num = 0;

                    return $obj;
                }
            }
        }
    }


    /**
     * @return integer ID ORM-объекта
     * @param integer $num - Если указан, определит какой именно объект нужен.
     * @desc Вернет список ID вложенных объектов
     */
    protected function getChildren($num = -1, $with_remote = false){

        if (!empty($this->id)) {

            if (empty($this->childr)) {

                $trash = ($with_remote) ? '' : 'and o_to_trash <> 1';

                $sql = 'SELECT r_children_id
	        	    		FROM <<rels>>, <<objects>>
	        				WHERE r_parent_id = "'.$this->id.'" and
	        					  r_field_id is NULL and
	        					  o_id = r_children_id
	        					  '.$trash.'
	        				ORDER BY r_position;';

                $row = db::q($sql, records);

                if ($row !== false) {

                    $this->childr = Array();
                    while (list($key, $val) = each ($row))
                        $this->childr[] = $val['r_children_id'];
                }
            }

            if ($num >= 0 && isset($this->childr[$num]))
                return $this->childr[$num];
            else
                return $this->childr;
        }
    }

    // Вернет TRUE, если у текущего объекта есть вложенные объекты
    public function issetChildren(){
        $this->getChildren();
        return (count($this->childr) > 0) ? true : false;
    }

    // Вернет количество вложенных объектов
    public function countChildren(){
        $this->getChildren();
        return count($this->childr);
    }

    // Вернет список ID объектов-родителей которым принадлежит объект
    public function getParents($prinud = false) {

        if (empty($this->parents) || $prinud) {

            if (!empty($this->id)) {

                $this->parents = Array();
                /*
                            $sql = 'SELECT r_parent_id, o_name, r_position
                                    FROM <<rels>>, <<objects>>
                                    WHERE r_children_id = "'.$this->id.'" and
                                          r_field_id is NULL and
                                          o_id = r_parent_id;';
                                                 */

                $sql = 'SELECT r_parent_id, o_name, o_class_id, r_position
		        	    	FROM <<rels>> LEFT JOIN <<objects>> ON o_id = r_parent_id
		        			WHERE r_children_id = "'.$this->id.'" and r_field_id is NULL;';

                $row = db::q($sql, records);

                if ($row !== false)
                    while (list($key, $val) = each ($row)) {

                        if (empty($key)) {
                            $this->parent_id = $val['r_parent_id'];
                            $this->position = $val['r_position'];
                        }

                        if (empty($val['r_parent_id']))
                            $val['r_parent_id'] = 0;

                        $this->parents[$val['r_parent_id']] = array(
                            'parent_id' => $val['r_parent_id'],
                            'parent_name' => $val['o_name'],
                            'parent_class' => $val['o_class_id'],
                            'position' => $val['r_position']
                        );
                    }

            } else {

                if (!empty($this->new_parents)) {
                    reset($this->new_parents);
                    list($this->parent_id, $this->position) = each($this->new_parents);
                }

                $this->parents = $this->new_parents;

            }
        }

        return $this->parents;
    }

    // Вернет TRUE, если у текущего объекта есть родитель
    public function issetParents() {
        $this->getParents();
        return (!empty($this->parents) && !isset($this->parents[0]));
    }

    // Вернет ID родителя. Если объектов родителей несколько, вернет ID первого по списку родителя.
    public function getParentId($class_name = '') {

        if (empty($class_name)) {

            $this->getParents();
            return (!empty($this->parent_id)) ? $this->parent_id : 0;

        } else {

            if ($parent = $this->getParent($class_name))
                return $parent->id;
        }

        return false;
    }

    // Вернет экземпляр родителя. Если родителей несколько, вернет экземпляр первого по списку родителя.
    public function getParent($class_name = '') {

        if (empty($class_name)) {

            if ($this instanceof ormPage)
                return ormPages::get($this->getParentId());
            else
                return ormObjects::get($this->getParentId());

        } else if ($class = ormClasses::get($class_name)) {

            // Ищем родителя нужного класса
            $this->getParents();
            reset($this->parents);
            while (list($obj_id, $val) = each($this->parents)) {
                if ($val['parent_class'] == $class->id())
                    if ($class->isPage())
                        return ormPages::get($obj_id);
                    else
                        return ormObjects::get($obj_id);
            }
        }

        return false;
    }


    /**
     * @return integer Позиция объекта в списке
     * @param integer $parent_id - ID родительского ORM-объекта для которого нужно узнать позицию.
     * @desc Вернет позицию объекта в списке подобъектов родителя.
    Если родителей несколько вернет позицию в первом по списку родителе.
     */
    public function getPosition($parent_id = 0) {
        $this->getParents();
        if (empty($parent_id)) {
            return $this->position;
        } else {
            if (isset($this->parents[$parent_id]))
                return $this->parents[$parent_id]['position'];
            else
                return false;
        }
    }


    //
    public function delParent($parent_id) {

        $parent_id = system::checkVar($parent_id, isInt);

        $sql = (empty($parent_id)) ? 'r_parent_id is NULL and' : 'r_parent_id = "'.$parent_id.'" and';

        db::q('DELETE FROM <<rels>>
        		   WHERE r_children_id = "'.$this->id.'" and
        				 '.$sql.'
        				 r_field_id is NULL;');

        if (isset($this->parents[$parent_id]))
            unset($this->parents[$parent_id]);

    }


    // Метод удаляет все связи объекта с родителями.
    public function clearParents() {
        $this->new_parents = array();
        $this->del_parents = true;
    }

    // Устанавливает нового родителя, удаляя все связи текущего объекта с другими родителями
    public function setParent($id, $position = 0) {
        $this->new_parents = array();
        $this->setNewParent($id, $position);
    }


    /**
     * @return null
     * @param integer $parent_id - ID родительского ORM-объекта к которому прикрепляем данный объект.
     * @param integer $position - Позиция в которую устанавливаем данный объект
     * @desc Устанавливает нового родителя, сохраняя связи с другими родителями
     */
    public function setNewParent($parent_id, $position = 0) {
        $id = system::checkVar($parent_id, isInt);
        $position = system::checkVar($position, isInt);
        $this->new_parents[$id] = array('parent_id' => $id, 'position' => $position);
    }


    // -		-		-		-		-		-		-		-		-		-

    // Получаем экземпляр класса на основе которого создан объект
    public function getClass() {
        if ($this->class instanceof ormClass)
            return $this->class;
    }

    // Указываем системное имя класса на базе которого будет создан объект (только в режиме добавления объекта)
    public function setClass($sname) {

        if (empty($this->id))
            $this->class = ormClasses::get($sname);
        else
            return false;
    }

    // Проверяет, создал ли текущий объект на основе наследника указанного ORM-класса
    public function isInheritor($class_name) {
        if ($this->class instanceof ormClass)
            return $this->class->isInheritor($class_name);
        else
            return false;
    }



    public function copy($with_child = true, $copyTo = 0) {

        if (!empty($this->id)) {

            $copy = new ormObject();
            $copy->setClass($this->getClass()->id());

            // Перенос данных полей
            $fields = $this->getClass()->loadFields();
            while(list($fname, $field) = each($fields))
                if (!empty($field['f_type']) && $field['f_type'] != 97 && $field['f_relation'] < 2)
                    $copy->__set($fname, $this->__get($fname));

            if (empty($copyTo))
                $copy->__set('name', $this->__get('name') . lang::get('copy'));

            // Устанавливаем родителя
            if (empty($copyTo)) {
                $parents = $this->getParents();
                while(list($id, $parent) = each($parents))
                    $copy->setNewParent($id);
            } else $copy->setNewParent($copyTo);

            $copy->save();

            if (!$copy->issetErrors() && $with_child) {

                while($child = $this->getChild())
                    $child->copy(true, $copy->id);

                return true;

            } else if ($copy->issetErrors())
                return false;
        }
    }

}

?>