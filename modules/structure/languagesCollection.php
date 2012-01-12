<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Статический класс коллекция для работы с языковыми версиями сайтов (языки).
	Позволяет:
		-	Быстро получить информацию о языковой версии
		-	Определить текущую языковую версию
*/

class languages {

    private static $langs = array();
    private static $langs_obj = array();
    private static $langs_rel = array();
    private static $cur_lang;
    private static $isCliped = false;

    // Иницилизация класса
    private static function init(){

        if (empty(self::$cur_lang)){

        	$lang_id = reg::getKey('/core/cur_lang/id');

	        if (!empty($lang_id)) {

                // Если язык в системе один, загружаем его данные из реестра
                $lang_prefix = reg::getKey('/core/cur_lang/prefix');

            	self::$langs[$lang_id] = array(
            		'l_id' => $lang_id,
            		'l_name' => reg::getKey('/core/cur_lang/name'),
            		'l_prefix' => $lang_prefix,
            		'id' => $lang_id
            	);

				self::$langs_rel[$lang_prefix] = $lang_id;

	        } else {

                // Языков много, определяем язык по URL`y
				if (system::issetUrl(0) && strlen(system::url(0)) < 6) {

		        	self::$cur_lang = self::get(system::url(0));

		        	if (self::$cur_lang instanceof language) {
			        	system::clipUrl();
			        	self::$isCliped = true;
		        	}
		        }
            }

			if (!(self::$cur_lang instanceof language))
				self::$cur_lang = self::get(domains::curDomain()->getDefLang());

			if (!(self::$cur_lang instanceof language))
				die('не могу определить язык');
		}
	}

	/**
	* @return null
	* @param integer $lang_id - ID языковой версии
	* @desc Принудительная установка текущей языковой версии
	*/
	static function setCurLang($lang_id) {
    	if (!self::$isCliped && system::$isAdmin && is_numeric($lang_id)) {
    		$tmp = self::get($lang_id);
    		if ($tmp instanceof language)
    			self::$cur_lang = $tmp;
    	}
	}

    /**
	* @return array
	* @param boolean $prinud - Если true, принудительно читает данные из БД.
	* @desc Вернет все языковые версии системы
	*/
	static function getAll($prinud = false){

		if (empty(self::$langs) || $prinud) {
			$mas = db::q('SELECT *, l_id id FROM <<langs>>;', records);
			self::$langs = array();
			while(list($key, $lang) = each($mas)) {
				self::$langs[$lang['l_id']] = $lang;
				self::$langs_rel[$lang['l_prefix']] = $lang['l_id'];
			}
		}
		return self::$langs;
	}

    /**
	* @return object
	* @param string $val - ID или префикс языковой версии
	* @param boolean $prinud - Если true, принудительно пересоздает класс.
	* @desc Вернет экземпляр указанной языковой версии
	*/
	static function get($val, $prinud = false){
		self::getAll();

		if (!is_numeric($val) && isset(self::$langs_rel[$val]))
        	$val = self::$langs_rel[$val];

        if (isset(self::$langs[$val])) {

		    if (!isset(self::$langs_obj[$val]) || $prinud)
             	self::$langs_obj[$val] = new language(self::$langs[$val]);

			return self::$langs_obj[$val];
		}
	}

    // Текущая языковая версия, объект класса language
	static function curLang(){
		self::init();
		return self::$cur_lang;
	}

    // ID текущей языковой версии
	static function curId(){
		self::init();
		return self::$cur_lang->id();
	}

    // Префикс текущей языковой версии
	static function curPrefix(){
		self::init();
		return self::$cur_lang->getPrefix();
	}

    // Вернет префикс для формирования ссылок, исходя из текущей языковой версии
	static function pre(){
		self::init();
		if (self::$cur_lang->id() != domains::curDomain()->getDefLang())
			return '/'.self::$cur_lang->getPrefix();
		else
			return '';
	}

}

?>