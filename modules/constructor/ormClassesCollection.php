<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Статический класс коллекция для работы с ORM-классами.
*/

class ormClasses {

    private static $classes_data = Array();      // "Голлые" данные классов из БД
    private static $classes = Array();           // Список экзепляров класса ormClass
    private static $cl_names = Array();          // Отношение "системное имя класса <-> ID класса"
    private static $classes_rel = Array();       // Отношение "ID класса <-> список ID подклассов"
    private static $classes_num = Array();       // Счетчик, используется для перебора наследников

    private static function init(){

        if (empty(self::$classes_data)){

            $tmp = db::q('SELECT * FROM <<classes>>;', records);

            while(list($num, $class) = each($tmp)) {

                self::$classes_data[$class['c_id']] = $class;
                self::$cl_names[$class['c_sname']] = $class['c_id'];

                if (empty($class['c_parent_id'])) $class['c_parent_id'] = 0;
                self::$classes_rel[$class['c_parent_id']][] = $class['c_id'];
            }
        }
    }

    // Возвращает указаный Класс, где $id это ID или Системное имя класса
    public static function get($id){

        $class = false;

        if (!empty($id) && !is_array($id)) {

            self::init();

            // Проверяем переданные данные, определяем ID
            if (is_numeric($id)) {
                $id = system::checkVar($id, isInt);
            } else if (!is_array($id)) {

                $sname = system::checkVar($id, isVarName);
                if (!empty($sname) && array_key_exists($sname, self::$cl_names))
                    $id = self::$cl_names[$sname];
            }

            // Загрузка класса
            if (is_numeric($id) && isset(self::$classes[$id]))

                $class = self::$classes[$id];

            else if (isset(self::$classes_data[$id])) {

                $class = new ormClass(self::$classes_data[$id]);
                if (!$class->issetErrors()) {

                    self::$classes[$class->id()] = $class;

                } else {
                    $class = false;

                }

            }
        }

        if ($class)
            return $class;
    }

    // Регистрация нового класса в коллекции. Используется системой при добавлении нового класса.
    public static function registration($class) {
        if ($class instanceof ormClass)
            self::$classes[$class->id()] = $class;
    }

    // Для теста. Вывод спска общих полей для метода getVirtual()
    private static function printMas($title, $f){

        echo '<div style="float:left;width:200px;height:400px;display:block;">';
        echo $title.'<br /><br />';
        while(list($key, $class_name) = each($f)) {
            echo $key.'<br />';
        }
        echo '</div>';
    }

    // Создаем виртуальный класс на основе списка полей. Получаем на выходе список общих полей.
    public static function getVirtual($classes_list){

        $f = array();
        while(list($key, $class_name) = each($classes_list)) {
            if (empty($f)) {
                $f = ormClasses::get($class_name)->loadFields();
                //self::printMas($class_name, $f);
            } else {
                $fields = ormClasses::get($class_name)->loadFields();
                //self::printMas($class_name, $fields);
                $f = array_intersect_key($f, $fields);
            }

        }

        // self::printMas('virtual', $f);
        // print_r($f);
        // die();
        return $f;
    }


    /**
     * @return ormClass
     * @param integer $parent_id - ID родительского ORM-класса.
     * @desc Скидывает индекс для перебора списка наследников методом getInheritor().
     */
    public static function resetFor($parent_id = 0){
        self::$classes_num[$parent_id] = -1;
    }


    /**
     * @return ormClass
     * @param integer $parent_id - ID родительского ORM-класса для которого делается перебор наследников.
     * @desc Вернет следующий экзепляр наследника, используйте для перебора в цикле.
     */
    public static function getInheritor($parent_id = 0){

        self::init();

        if (!isset(self::$classes_num[$parent_id]))
            self::$classes_num[$parent_id] = 0;
        else
            self::$classes_num[$parent_id] ++;

        if (isset(self::$classes_rel[$parent_id][self::$classes_num[$parent_id]]))
            return self::get(self::$classes_rel[$parent_id][self::$classes_num[$parent_id]]);
    }

    /**
     * @return array - Список наследников
     * @param integer $parent_id - ID родительского ORM-класса
     * @desc Вернет список прямых наследников ORM-класса
     */
    public static function getInheritors($parent_id = 0){

        self::init();
        
        if (isset(self::$classes_rel[$parent_id]))
            return self::$classes_rel[$parent_id];
        else
            return array();
    }

    // Возвращает все классы-справочники в системе
    public static function getHandbooks(){

        $sql =  'SELECT c_id, c_name
   					 FROM <<classes>>
   					 WHERE c_is_list = "1";';

        $classes = db::q($sql, records);

        return $classes;
    }

    /**
     * @return array - Список классов
     * @param integer $parent_id - ID родительского ORM-класса
     * @param array $class_list - Список классов для перебора. Используется для организации рекурсии.
     * @param array $ret_list - Возвращаемый список значений. Используется для организации рекурсии.
     * @param integer $level - Уровень вложенности вызовов. Используется для организации рекурсии.
     * @desc Получаем список ORM-классов для объектов ormPage для вывода в списках с иерархией
     */
    public function getPagesClassList($parent = '', $class_list = array(), $ret_list = array(), $level = '') {

        if (empty($class_list)) {
            $classes = self::get('section')->getAllInheritors();
            while(list($id, $name) = each($classes)) {
                $obj = self::get($id);
                $class_list[$obj->id()] = array(
                    'id' => $obj->id(),
                    'parent' => $obj->getParentId(),
                    'name' => $obj->getName(),
                    'sname' => $obj->getSName()
                );
            }
        } else $level .= '&nbsp;&nbsp;&nbsp;&nbsp;';

        reset($class_list);
        while(list($id, $class) = each($class_list)) {
            if ($parent == $class['parent']) {
                $ret_list[$id] = $level.$class['name'].' ('.$class['sname'].')';
                $ret_list = self::getPagesClassList($class['id'], $class_list, $ret_list, $level);
            }
        }

        return $ret_list;
    }

}

?>