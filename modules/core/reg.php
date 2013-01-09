<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс для работы с системным реестром. Класс является статическим.
*/

class reg {

    static private $keys = '';

    private static function init() {

        if (self::$keys == '') {

            if (!(self::$keys = cache::get('global-settings'))) {

                $sql = 'SELECT SQL_CACHE r_id id, r_section_id section, r_key name, r_value value, r_description description FROM <<register>>;';
                self::$keys = db::q($sql, records);

                if (db::issetError()) die;
                
                // Записываем в кэш
                cache::set('global-settings', self::$keys);
            }
        }

    }

    // Очищаем кэш
    static function clearCache() {
        cache::delete('global-settings');
    }

    // Изменяем существующий ключ
    private static function updKey($id, $value, $desc = '') {

//		if ($desc != '') {
			db::q('UPDATE <<register>> SET r_value="'.$value.'", r_description="'.$desc.'" WHERE r_id="'.$id.'"');
//		} else {
//			db::q('UPDATE <<register>> SET r_value="'.$value.'" WHERE r_id="'.$id.'"');
//		}

       $num = self::findInMas($id);
       if ($num !== false) {
		   self::$keys[$num]['value'] = $value;
		   self::$keys[$num]['description'] = $desc;
	   }
       	

    }

    // Добавляем новый ключ
    private static function newKey($name, $value, $section, $desc = '') {
		
       $sql = 'INSERT INTO <<register>>
       	   		 SET r_section_id="'.$section.'",
       	   	   		 r_description="'.$desc.'",
       	   	   		 r_key="'.$name.'",
       	   	   		 r_value="'.$value.'";';

       $id = db::q($sql);

       if ($id) {

           if ($name == 'auto_id') {
           		db::q('UPDATE <<register>> SET r_key="'.$id.'" WHERE r_id="'.$id.'";');
                $name = $id;
           }

	       $elem['id'] = $id;
	       $elem['section'] = $section;
	       $elem['description'] = $desc;
	       $elem['name'] = $name;
	       $elem['value'] = $value;
	       self::$keys[] = $elem;
       }

       return $id;
    }

    // Читаем ключ
    private static function readKey($id) {

       $num = self::findInMas($id);
       $value = ($num !== false) ? self::$keys[$num]['value'] : false;

       return $value;
    }

    // Удаляем ключ
    private static function deleteKey($id) {

       $num = self::findInMas($id);
       if ($num !== false) {

           $child = db::q('SELECT r_id FROM <<register>> WHERE r_section_id="'.$id.'";', records);

           for ($i = 0; $i < count($child); $i++)
           	self::deleteKey($child[$i]['r_id']);

           db::q('DELETE FROM <<register>> WHERE r_id="'.$id.'";');

           array_splice(self::$keys, $num, 1);
           return true;

       } else return false;
    }

    // Узнаем ID ключа
    private static function getIDKey($way, $level=0, $id=0){

        $ret['state'] = true;
 		if (count(self::$keys) > 0) {
			for ($i=0; $i<count(self::$keys); $i++) {

               //echo $way[$level].' == '.self::$keys[$i]['name'].' and '.$id.' == '.self::$keys[$i]['section'].'<br>';

 			  	if ($way[$level] == self::$keys[$i]['name'] and $id == self::$keys[$i]['section']){

                   $ret['id'] = self::$keys[$i]['id'];
                   $ret['value'] = self::$keys[$i]['value'];
   				if (count($way)-1 !== $level){

   					$ret = self::getIDKey($way, $level+1, self::$keys[$i]['id']);
                       if ($ret['state'] === true) $flag = 1;

   				} else $flag = 1;

 				} else
 					if ($ret['state'] === true) {
 						  $ret['state'] = $id;
 						  $ret['level'] = $level;
 					//	  echo '+'.$level;
 		            }

 				if (isset($flag)) {
 					$ret['state'] = true;
 					break;
 				}
           }
        } else {
 			$ret['state'] = $id;
 			$ret['level'] = $level;
 		}

 		return $ret;
    }

    // Ищем в массиве нужный элемент
    private static function findInMas($id) {

       $ret = false;
       if (count(self::$keys) > 1)
		for ($i=0; $i<count(self::$keys); $i++)
 			  	if ($id == self::$keys[$i]['id'])
 			  	    $ret = $i;

 		return $ret;
    }

    // Перегоняем ключ в массив
    private static function getWay($key) {
        $way = Array();
        if ($key <> ''){
        	$tmp = strtok($key, '/');
           while ($tmp != '') {
             $way[] = $tmp;
             $tmp = strtok('/');
           }
        }

        return $way;
    }

   // ***************************	Публичные методы	********************************

    // Проверяем существование ключа
    static function existKey($key) {

        self::init();
    	$keyID = self::getIDKey(self::getWay($key));

        if ($keyID['state'] === true)
        	return true;
        else
        	return false;

    }

    /**
	* @return boolean В случае успеха true
	* @param string $key - Ключ или ID ключа
	* @param string $value - Записываемое значение
	* @desc Изменение значения ключа. Если ключ не существует, он будет создан.
	*/
    static function setKey($key, $value = '', $desc = '') {

        self::init();

        $value = system::checkVar($value, isText);

        if (!is_numeric($key)) {
	        $way = self::getWay($key);
	        $keyID = self::getIDKey($way);
        } else $keyID = array('id' => $key, 'state' => true);

        if ($keyID['state'] === true) {

        	// Изменяем ключ
        	self::updKey($keyID['id'], $value, $desc);
        	return true;

        } else {

           // Добавляем ключ
           $sect = $keyID['state'];
           // echo count($way);
           for ($i = $keyID['level']; $i < count($way); $i++)
             if ($sect !== false) {
              // echo $way[$i].'|'.$val.'|'.$sect.'<br>';
              	$val = (count($way)-1 == $i) ? $value : '';
              	$sect = self::newKey($way[$i], $val, $sect, $desc);
             }

        	return true;
        }

    }

    /**
	* @return boolean В случае успеха true
	* @param string $key - Ключ в котором будет хранится список
	* @param string $value - Записываемое значение
	* @desc Добавляем значения в список. Все значения записанные в список идентифицируются по ID.
			Метод удобно использовать, когда не важно какой ключ будет у значений списка.

     Пример:
		 reg::addToList('/core/test_list', 'La');
		 reg::addToList('/core/test_list', 'Ma');
		 reg::addToList('/core/test_list', 'Pa');
		 print_r(reg::getList('/core/test_list'));

	 Вернет:
		 Array (
		 	[705] => La
		 	[706] => Ma
		 	[707] => Pa
		 )

	*/
    static function addToList($key, $value = '', $desc = '') {

        self::init();

	    $value = system::checkVar($value, isText);
	    $way = self::getWay($key);
	    $keyID = self::getIDKey($way);

        if ($keyID['state'] === true) {

           // Добавляем ключ
           $sect = $keyID['id'];
           for ($i = $keyID['level']; $i < count($way); $i++)
             if ($sect !== false) {
             	$w = (count($way) == $i) ? $way[$i] : 'auto_id';
              	$val = (count($way)-1 == $i) ? $value : '';
				$desc = (count($way)-1 == $i) ? $desc : '';
              	$sect = self::newKey($w, $val, $sect, $desc);
             }

        	return true;
        }
    }

    // Вернет значение ключа, где $key это ключ или ID ключа
    static function getKey($key) {

		self::init();

        if (!is_numeric($key))
	        $keyID = self::getIDKey(self::getWay($key));
        else
        	$keyID = array('id' => $key, 'state' => true);

        if ($keyID['state'] === true) {
        	return $keyID['value'];
        } else
        	return false;

    }

    // Удаление ключа, где $key это ключ или ID ключа
    static function delKey($key) {

        self::init();

        if (!is_numeric($key))
	        $keyID = self::getIDKey(self::getWay($key));
        else
        	$keyID = array('id' => $key, 'state' => true);

        if ($keyID['state'] === true) {
        	return self::deleteKey($keyID['id']);
        } else
        	return false;

    }


    /**
	* @return array Список значений
	* @param string $key - Ключ в котором будет хранится список
	* @param boolean $with_id - Если true, вернет ассоциативный массив
	* @desc Возвращаем список ключей для указанной ветки в виде массива
	*/
    static function getList($key, $with_id = false) {

       self::init();

       if (!is_numeric($key))
	        $keyID = self::getIDKey(self::getWay($key));
        else
        	$keyID = array('id' => $key, 'state' => true);

       $list = array();

    	if (count(self::$keys) > 1 && isset($keyID['id']))
			for ($i=0; $i<count(self::$keys); $i++)
				if (self::$keys[$i]['section'] == $keyID['id'])
                    if ($with_id)
                    	$list[] = array(
	                    	'id' => self::$keys[$i]['id'],
	                    	'description' => self::$keys[$i]['description'],
	                    	'name' => self::$keys[$i]['name'],
	                    	'value' => self::$keys[$i]['value']
                    	);
                    else
				    	$list[self::$keys[$i]['name']] = self::$keys[$i]['value'];

		return $list;

    }

}

?>