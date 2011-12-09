<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Статический класс коллекция для работы с ORM-объектами
*/

class ormObjects {

    private static $objects = Array();

    private static function isLoad($obj_id){
        return (bool) array_key_exists($obj_id, self::$objects);
    }

    // Возвращает указаный Объект
    public static function get($obj_id, $filter_class = ''){

        if (!empty($obj_id)) {

            $obj = false;

            if (!is_array($obj_id))
                $id = system::checkVar($obj_id, isInt);

            if (!is_array($obj_id) && self::isLoad($id)) {

                // Подгружаем использованный ранее объект
                $obj = self::$objects[$id];

            } else if (is_array($obj_id)) {


                // Подгрузка данных объекта через массив
                if (isset($obj_id['o_id']) && self::isLoad($obj_id['o_id'])) {
                    $obj = self::$objects[$obj_id['o_id']];
                    $obj->supplementData($obj_id);
                } else {
                    $obj = new ormObject($obj_id);
                    if ($obj->id == 0)
                        $obj = false;
                    else
                        self::$objects[$obj->id] = $obj;
                }

            } else  if (!empty($id)){

                // Загрузка данных объекта из БД

                $obj = new ormObject($id);
                if ($obj->issetErrors() || $obj->id != $id)
                    $obj = false;
                else
                    self::$objects[$id] = $obj;
            }

            if (is_a($obj, 'ormObject') && !$obj->inTrash())
                if (empty($filter_class) || $obj->isInheritor($filter_class))
                    return $obj;
        }
    }


    // Возвращает список объектов созданных на основе указанного класса
    public static function getObjectsByClass($class_id){

        $class_id = system::checkVar($class_id, isVarName);

        if (is_numeric($class_id))
            $sql =  'SELECT o_id id, o_name name
	   				 	 FROM <<objects>>
	   				 	 WHERE o_class_id = "'.$class_id.'" and
	   				 	 	   o_to_trash = 0;';
        else
            $sql =  'SELECT o_id id, o_name name
	   				 	 FROM <<objects>>, <<classes>>
	   				 	 WHERE o_class_id = c_id and
	   				 	 	   c_sname = "'.$class_id.'" and
	   				 	 	   o_to_trash = 0;';

        return db::q($sql, records);
    }

    // Возвращает список объектов перемещенных в корзину
    public static function getCountTrashObjects(){

        $sql =  'SELECT o_id
	   				 FROM <<revue>>, <<classes>>, <<objects>>
	   				 WHERE o_class_id = c_id and
	   				 	   o_to_trash = 1 and
	   				 	   o_id = rev_obj_id and
	   				 	   rev_type = 1
	   				 GROUP BY o_id;';

        return count(db::q($sql, records));
    }

    // Возвращает список объектов перемещенных в корзину
    public static function getTrashObjects($start = 0, $max_count = 0){

        $limit = '';
        if (!empty($start) || !empty($max_count))
            if (!empty($max_count))
                $limit = ' LIMIT '.$start.', '.$max_count;
            else
                $limit = ' LIMIT '.$start;

        $sql =  'SELECT o_id id, o_name name, c_name class, MAX(rev_datetime) date, rev_user user
	   				 FROM <<revue>>, <<classes>>, <<objects>>
	   				 WHERE o_class_id = c_id and
	   				 	   o_to_trash = 1 and
	   				 	   o_id = rev_obj_id and
	   				 	   rev_type = 1
	   				 GROUP BY o_id
	   				 ORDER BY MAX(rev_datetime) DESC'.$limit.';';

        return db::q($sql, records);
    }




}
?>