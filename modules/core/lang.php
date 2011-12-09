<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс позволяет обращаться к языковым переменным из любой точки системы.
*/

class lang  {

    static private $lang = array();
    static private $right = array();
    static private $module = array();


    /**
    * @return null
    * @param string $name - Имя переменной
    * @param integer $num - Номер элемента, если переменная является массивом
    * @desc Получить значение языковой переменной
    */
    static function get($name, $num = ''){

        if ($num == '')
    		return (isset(self::$lang[$name])) ? self::$lang[$name] : false;
    	else
    		return (isset(self::$lang[$name][$num])) ? self::$lang[$name][$num] : false;

    }

    // Получить название модуля
    static function module($name){

    	if (isset(self::$module[$name]))
    		return self::$module[$name];
    	else
    		return false;

    }

    /**
    * @return null
    * @param string $name - Имя права
    * @param string $mod - Имя модуля. Если не указано, то для текущего.
    * @desc Получить название права по системному имени.
    */
    static function right($name, $mod = ''){

        if (empty($mod))
        	$mod = system::url(0);

    	if (isset(self::$right[$mod][$name]))
    		return self::$right[$mod][$name];
    	else
    		return false;
    }

    // Присваивает массив с языковыми переменными
    static function setLang($mas){
    	self::$lang = $mas;
    }

    // Присваивает массив с названиями модулей
    static function setModule($mas){
    	self::$module = $mas;
    }

    // Присваивает массив с названиями прав
    static function setRight($mas){
    	self::$right = $mas;
    }
}

?>