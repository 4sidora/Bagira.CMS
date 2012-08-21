<?php

// Константы для методов журнализации system::log() и system::revue()
define('info', 1);
define('error', 2);
define('warning', 3);

// Вспомогательные константы для метода system::checkVar();
define('isInt', 101);
define('isNum', 102);
define('isText', 103);
define('isEmail', 104);
define('isString', 105);
define('isPseudoUrl', 106);
define('isMD5', 107);
define('isSHA1', 108);
define('isBool', 109);
define('isDate', 110);
define('isDateTime', 111);
define('isPhone', 112);
define('isVarName', 113);
define('isUrl', 114);
define('isPassword', 115);
define('isDomain', 116);
define('isPrice', 117);
define('isRuDomain', 118);
define('isAbsUrl', 119);



// Способы масштабирования рисунков для класса resizer
define('stRateably', 1);
define('stSquare', 2);
define('stInSquare', 3);


/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Главный класс системы, с него начинается запуск.
	Реализует основную логику работы системы, а так же содержит ряд вспомогательных методов.
	Класс является статическим.
*/
class system {

    static private $classes = array();

    static private $clip_url = 0;

    static private $current_url, $current_url_pn, $current_nav_num, $current_nav_val;
    static private $url_parram = array();

    static private $SUMTimeLabel = array();
    static private $TimeLabel = array();

    static public $defTemplate;
    public static $isAdmin = false;


    // Метод, запускающий систему
    static function start(){

        header('Content-Type: text/html; charset=utf-8');
        
    	self::parseUrl();

        // Опредяем текущий язык и домен (в случае необходимости обрезаем URL)
        languages::curLang();

        // Устанавливаем в доступ языковые переменные
        if (file_exists(MODUL_DIR.'/core/sitelang-'.languages::curPrefix().'.php')) {
        	include(MODUL_DIR.'/core/sitelang-'.languages::curPrefix().'.php');
        	lang::setLang($LANG);
        }

        // Подключаем класс для работы с текущим пользователем
        user::init();

        // активация шаблонной системы
        self::$defTemplate = '/structure/default.tpl';
        page::init('%', '%');

        page::globalVar('h1', '');
        page::globalVar('title', '');
        page::globalVar('site_name', domains::curDomain()->getSiteName());
        page::globalVar('base_email', domains::curDomain()->getEmail());
        page::globalVar('user_id', user::get('id'));
        page::globalVar('pre_lang', languages::pre());
        page::globalVar('time', time());
        page::globalVar('current_url', self::getCurrentUrl());
        page::globalVar('current_url_pn', self::getCurrentUrlPN());
        page::assign('current_url', self::getCurrentUrl());
        page::assign('current_url_pn', self::getCurrentUrlPN());


        // Заглушка для IE6
        if(reg::getKey('/core/noIE6') && preg_match( '/msie/i', $_SERVER['HTTP_USER_AGENT']) && !preg_match( '/opera/i', $_SERVER['HTTP_USER_AGENT'])){
        	$val = explode(" ", stristr($_SERVER['HTTP_USER_AGENT'], 'msie'));
			if ($val[1] == '6.0;') { page::parseIt('/ieDie.tpl', false, true); system::stop(); }
		}

		// Заглушка для IE7
		if(reg::getKey('/core/noIE7') && preg_match( '/msie/i', $_SERVER['HTTP_USER_AGENT']) && !preg_match( '/opera/i', $_SERVER['HTTP_USER_AGENT'])){
			$val = explode(" ", stristr($_SERVER['HTTP_USER_AGENT'], 'msie'));
			if ($val[1] == '7.0;') { page::parseIt('/ieDie.tpl', false, true); system::stop(); }
		}

        // Оффлайн сообщение
        if (!domains::curDomain()->online() && !user::isAdmin() && !(self::issetUrl(0) && self::url(0) == 'mpanel')){
        	page::globalVar('content', domains::curDomain()->getOfflineMsg());
            page::parseIt('/offline.tpl', false, true);
        	system::stop();
        }

        // Генерация файлов robots.txt и sitemap.xml
        ormPages::getContentFileRobots();
        ormPages::getContentFileSiteMap();

        // Проверяем, есть ли текущая страница в кэше
        page::checkCache();

        // Вызов макроса через Ajax
        page::callAjaxMacros();

        // Парсим мето-информацию "по умолчанию"
        page::globalVar('keywords', reg::getKey(ormPages::getPrefix().'/keywords'));
		page::globalVar('description', reg::getKey(ormPages::getPrefix().'/description'));


        // Загрузка необходимого функционала в зависимости от адреса
        $content = '';

        // Пытаемся загрузить МОДУЛЬ
    	if (self::issetUrl(0))
            $content = self::loadController('controller');

     	// Пытаемся загрузить СТРАНИЦУ
     	if (!self::issetUrl(0) || $content === false)
        	$content = self::loadController('controller', 1, 'structure');

    	// Отображение сообщения "Страница не найдена"
    	if (empty($content))
	    	$content = ormPages::get404();

        // Парсим контент на страницу
        page::fParse('content', $content);

        // Версия для печати
        $print_file = '/structure/_'.self::getCurrentNavVal().'.tpl';
        if (self::getCurrentNavVal() != '' && file_exists(TEMPL_DIR.$print_file))
        	self::$defTemplate = $print_file;

        if (!self::$isAdmin) {
	        $title = str_replace('%text%', page::getGlobalVar('title'), reg::getKey(ormPages::getPrefix().'/title_prefix'));
	        page::globalVar('title', $title);

	        // Мини-тексты, если есть
			$list = reg::getList(ormPages::getPrefix().'/minitext');
	        while(list($id, $val) = each($list))
	        	page::globalVar('text_'.$id, $val);
        }

        // Выводим содержимое на экран
        page::parseIt(self::$defTemplate);

        // Завершение работы
        self::stop();

    }


    /**
	* @return string Вернет контент, если удалось вызвать нужный функционал.
	* @param string $controller_name -  Имя контролера, которого необходимо вызвать
	* @param Integer $level -  Уровень вложенности вызовов
	* @param string $module - Имя модуля функционал которого собираемся использовать
	* @desc Рекурсивный метод вызова функционала модуля
	*/
    private static function loadController($controller_name, $level = 1, $module = ''){

        if (empty($module)) $module = self::url(0);
    	$mod_name = MODUL_DIR.'/'.$module.'/'.$controller_name.'.php';
        $content = ($level == 1) ? false : '';
        $class_name = ($level == 1) ? 'controller' : self::url($level-1).'Controller';
        $class_name = str_replace('-', '_', $class_name);

     	// Пытаемся подгрузить модуль
    	if (file_exists($mod_name)) {

    	    include($mod_name);

    	    if (class_exists($class_name)) {

               	eval('$c = new '.$class_name.'();');

                if (self::issetUrl($level)) {

                    $action_name = str_replace('-', '_', self::url($level)).'Action';

	    			if (method_exists($c, $action_name)) {

	                	// Загружаем обычный метод
	                	$content = call_user_func(array($c, $action_name));

		    		} else if (!is_numeric(self::url($level))) {

                        // Метод не найден, пытаемся найти вложенный контроллер
	                    $cn = ($level == 1) ? '.'.self::url($level) : $controller_name.'.'.self::url($level);
			    		$content = self::loadController($cn, ($level + 1));
		    		}
				}

                // Загружаем метод по умолчанию
		    	if (empty($content))
			    	if (method_exists($c, 'defAction'))
		                $content = call_user_func(array($c, 'defAction'));
		        	else
		        		$content = '';
        	}

         	//if ($content === false)
             //    trigger_error ('I can`t include module "'.$mod_name.'"!', E_USER_ERROR);

    	}

    	return $content;
    }

    // Выполняет разбор адресной строки
	private static function parseUrl() {

        if (isset($_GET['way'])){

                $get_way = $_GET['way'];

                if ($get_way[0] == 'P') $num = 5; else $num = 4;
                $way_str = substr($get_way, $num, strlen($get_way)-(9+$num));

                // убираем сессии
                $pos = strrpos($way_str, '?');
                if ($pos !== false)
                $way_str = substr($way_str, 0, $pos);

                self::$current_url_pn = $way_str;

                // постраничная
                $navig_val = '';
                $navig_pos = strpos ($way_str, '=');

                if ($navig_pos !== false) {

                	$navig_num = substr($way_str, $navig_pos + 1, strlen($way_str)-($navig_pos + 1));

                 	if (is_numeric($navig_num))
                  		settype($navig_num, "integer");
                  	else {
                   		$navig_val = $navig_num;
                        $navig_num = 0;
                	}

                 	$way_str = substr($way_str, 0, $navig_pos);

                } else $navig_num = 0;
                if ($navig_num == 0) $navig_num = 1;

                self::$current_url = $way_str;
                self::$current_nav_num = $navig_num;
                self::$current_nav_val = $navig_val;

                // переганяем все в массив
                if (isset($way_str) && $way_str != ''){
                	$tmp = strtok($way_str, '/');
                 	while ($tmp != '') {
                  		self::$url_parram[] = $tmp;
                    	$tmp = strtok("/");
                  	}
                }
        }
 	}

 	/**
	* @return null
	* @param string $class_name - Имя класса
	* @param string $class_file - Путь до php-файла в котором описан класс
	* @desc Добавляет класс в список автозагрузки. После этого класс можно использовать в любом месте системы.
	*/
	static function addClass($class_name, $class_file){
		if (isset(self::$classes[$class_name]))
			trigger_error ('Класс "'.$class_name.'" уже подключен', E_USER_ERROR);
		self::$classes[$class_name] = $class_file;
	}

	// Метод выполняем автозагрузку класса. Используется функцией spl_autoload_register().
	static function loadClass($class_name){
		if (!isset(self::$classes[$class_name]))
 		    trigger_error ('Not found class "'.$class_name.'"', E_USER_ERROR);
 	    else
 	    	include_once(self::$classes[$class_name]);
	}

	// Выводит статистику по количеству запросов и времени обработки страницы
    private static function printStat(){

		if (SHOW_SPEED && !self::isAjax()) {

	    	echo '<br clear="all" />';

	    	list($msec, $sec) = explode(chr(32), microtime());
			echo round($sec + $msec - START_TIME, 5)."<br />";

			$q = db::getQueryList();

            echo system::getSumTimeLabel(999, false).'<br />';
			echo count($q).'<br /><br />';


			while(list($num, $val) = each($q))
				echo '('.($num+1).')<br />'.$val.'<br /><br />';

		}
    }

    // Метод, завершающий работу системы
    static function stop(){
        self::printStat();
    	db::close();
    	die;
    }

    // +++	Функции для работы с URL`ом	+++

    // Возвращает строку псевдо-адреса
	static function getCurrentUrl() {
		return self::$current_url;
	}

	// Возвращает полностью строку псевдо-адреса со значениями постраничной навигации
	static function getCurrentUrlPN() {
		return self::$current_url_pn;
	}

	// Возвращает значение постраничной, как число
	static function getCurrentNavNum() {
		return self::$current_nav_num;
	}

	// Возвращает значение постраничной, как строка
	static function getCurrentNavVal() {
		return self::$current_nav_val;
	}

    // Смещает влево "индекс доступности" адресной строки
	static function clipUrl() {
		self::$clip_url++;
	}

    // Вспомогательная ф-я для определения индекса смещения в адресной строке.
    // Реализует механиз относительной адресации при работе с методом system::url() и подобными.
    private static function getTrueNum($num) {

    	if (self::$isAdmin)
    		$num = $num + 1;

        return $num + self::$clip_url;
    }

	// Вернет указанный параметр адресной строки, нумерация параметров с 0.
	// Если указанного параметра нет возвращает false
	static function url($num) {

        $num = self::getTrueNum($num);

	    if (isset(self::$url_parram[$num]))
			return self::$url_parram[$num];
		else
			return false;
	}

    // Возвращает тип обработчика, только для режима администрирования
	static function action() {

        $num = self::getTrueNum(2) - 1;

	    if (self::$isAdmin && isset(self::$url_parram[$num])){

	        $s = self::$url_parram[$num];
			$pos = strpos($s, '_');
			return substr($s, $pos + 1, strlen($s) - $pos);

		} else return false;
	}

    /**
	* @return null
	* @param integer $num - Номер элемента адресной строки
	* @param string $value - Значение которое необходимо присвоить
	* @desc Принудительно изменяет значение параметров адресной строки
	*/
	static function setUrl($num, $value) {
	    $num = self::getTrueNum($num);
	    self::$url_parram[$num] = $value;
	}

    // Проверяем существование параметра адресной строки
	static function issetUrl($num) {

	    $num = self::getTrueNum($num);
	    return (isset(self::$url_parram[$num]) && self::$url_parram[$num] !== '') ? true : false;
	}

	// Обрезает один уровень сзади у указаной ссылки
	static function preUrl($path) {

        if (!empty($path)){
            $len = strlen($path) - 1;
            if ($path[$len] == '/') $path[$len] = '';
            $pos = strrpos($path, '/');
            $path = (!$pos || $pos == $len) ? '' : substr($path, 0, $pos);
        }
        return $path;
	}

    // Вернет префикс для урла в админке, в зависимости от текущего домена и языка
	static function au() {
		return languages::pre().ADMIN_URL.domains::pre();
	}

	/**
	* @return null
	* @param string $url -  URL на который необходимо сделать редирект
	* @param boolean $absolut -  Если true, считает что $url указан абсолютный.
		Если false, $url обрабатывается с учетом состояния системы: учитывается текущий домен,
		языковая версия, находимся ли мы в панели управления.
	* @desc Делает редирект и корректно завершает работу системы
	*/
    static function redirect($url, $absolut = false){

    	if (!$absolut)
            $url = (self::$isAdmin) ? self::au().$url : languages::pre().$url;

    	Header('Location: '.$url);
    	self::stop();
    }






    // +++	Функции журнализации	+++

    /**
	* @return null
	* @param string $text -  Текст сообщения
	* @param const $state -  Статус сообщения, используйте одну из контант:
			info	-	В порядке информации
			error	-	Ошибка
			warning	-	Предупреждение
	* @desc Добавляет событие в системный журнал
	*/
	static function log($text, $state = error){

        $znaks = array(info => 'info', error => 'error', warning => 'warning');

        $file = @fopen(ROOT_DIR."/revue.log", "a");

        if (isset($_SESSION['curUser']['name']) && $_SESSION['curUser']['name'] != 'none')
            $skobka = "[".$_SESSION['curUser']['login']."]";
        else
            $skobka = "[SYSTEM]";

        $stroka = $_SERVER['REMOTE_ADDR']."\t[".date("d.m.Y H:i:s")."]\t[".$znaks[$state]."]\t".$skobka."\t".$text."\n";

        @fwrite ($file, $stroka);
        @fclose ($file);
	}

    /**
	* @return null
	* @param ormObject $obj -  ORM-Объект информация о котором заносится в журнал
	* @param string $text -  Текст сообщения
	* @param const $state -  Статус сообщения, используйте одну из контант:
			info	-	В порядке информации
			error	-	Ошибка
			warning	-	Предупреждение
	* @param string $type -  Тип записи (для системных нужд, использовать не рекомендуется)
			0 - обычная,
			1 - информаци о помещении объекта в корзину
	* @desc Функция реализует добавление событий об изменение ORM-объектов в базу данных
	*/
	static function revue(ormObject $obj, $text, $state = error, $type = 0){

        $state = system::checkVar($state, isInt);
        $type = system::checkVar($type, isInt);
        $text = system::checkVar($text, isString);

 		db::q('INSERT INTO <<revue>>
 			   SET rev_state = "'.$state.'",
 				   rev_type = "'.$type.'",
 				   rev_obj_id = "'.$obj->id.'",
 				   rev_class_id = "'.$obj->getClass()->id().'",
 				   rev_user_id = "'.user::get('id').'",
 				   rev_user = "'.user::get('name').'",
 				   rev_datetime = "'.date('Y-m-d H:i:s').'",
 				   rev_message = "'.$text.'",
 				   rev_ip = "'.$_SERVER['REMOTE_ADDR'].'";');

	}





    // +++	Работа с файловой системой	+++


    // Выделяет из пути к файлу - имя файла с расширением
	static function fileName($file_name) {
    	return substr(strrchr($file_name, "/"), 1);
    }

    // Выделяет из пути к файлу - расширение файла
    static function fileExt($file_name) {
    	return strtolower(substr(strrchr($file_name, "."), 1));
    }

    /**
	* @return
	* @param String $file_name - Имя файла
	* @param Array $exe_array - Массив, список допустимых расширений
	* @desc Проверяет, входит ли расширение файла в допустимый список
	*/
    static function fileExtIs($file_name, $exe_array) {
    	return (in_array(self::fileExt($file_name), $exe_array)) ? true : false;
    }

    /**
	* @return Результат проверки:
			true  - Файл существует и отвечает всем требованиям.
			0 	  - Расширение загруженого файла не соответсвует указанному списку.
			1	  - Размер принятого файла превысил максимально допустимый размер, который задан директивой upload_max_filesize.
			2	  - Размер загружаемого файла превысил значение MAX_FILE_SIZE, указанное в HTML-форме.
			3	  - Загружаемый файл был получен только частично.
			4     - Файл не был загружен.
			5 	  - Размер файла привысил максимально допустимое значение $max_size
			false - Файл даже и не пытались загружать.
	* @param String $value - Имя переменной в массиве $_FILES
	* @param Array $exe_array - Массив со списком доступных расширений файла. Если не указан, проверка на расширение не выполняется.
	* @param Int $max_size - Максимальный размер файла
	* @desc Проверяет существует ли загруженный файл
	*/
	static function checkLoadFile($value, $exe_array = 0, $max_size = 0) {

	    if (isset($_FILES[$value]['tmp_name']) && file_exists($_FILES[$value]['tmp_name'])){

			if (!empty($_FILES[$value]['error'])) {

				return $_FILES[$value]['error'];

			} else {

				if (empty($exe_array) || self::fileExtIs($_FILES[$value]['name'], $exe_array)) {

					if (!empty($max_size)){
						$info = stat($_FILES[$value]['tmp_name']);
						if ($info[7] > $max_size) return 5;
					}

					return true;
				} else
					return 0;
			}

		} else return false;

	}

	/**
	* @return Результат проверки:
			true  - Файл существует и отвечает всем требованиям.
			0 	  - Расширение загруженого файла не соответсвует указанному списку.
			5 	  - Размер файла привысил максимально допустимое значение $max_size
			false - Файл не существует.
	* @param String $file_name - Имя файла на сервере
	* @param Array $exe_array - Массив со списком доступных расширений файла. Если не указан, проверка на расширение не выполняется.
	* @param Int $max_size - Максимальный размер файла
	* @desc Проверяет существует ли указанный файл на сервере и соответствует ли его расширение.
	*/
	static function checkLoadedFile($file_name, $exe_array = 0, $max_size = 0) {

	    if (!empty($file_name) && file_exists(ROOT_DIR.$file_name)){

			if (empty($exe_array) || self::fileExtIs($file_name, $exe_array)) {

				if (!empty($max_size)){
					$info = stat(ROOT_DIR.$file_name);
					if ($info[7] > $max_size) return 5;
				}

				return true;
			} else
				return 0;

		} else return false;

	}

    /**
	* @return String Имя созданного файла
	* @param String $tmp_filename - Путь к исходному файлу
	* @param String $user_filename - Исходное (желаемое) имя файла
	* @param String $pathTo - Папка в которую необходимо скопировать файл
	* @desc Копирует файл в указанную папку с автоподбором имени и транслятирацией русского названия
	*/
 	static function copyFile($tmp_filename, $user_filename, $pathTo) {

		if (!empty($tmp_filename) && file_exists($tmp_filename)){

			$exe = system::fileExt($user_filename);
			$pos = strpos($user_filename, '.');
			$fname = substr($user_filename, 0, strlen($user_filename) - (strlen($user_filename)-$pos));

			$fname = system::translite($fname);

	  		// Проверяем, есть ли файл с таким же именем
	  		$new_filename = $pathTo.'/'.$fname.'.'.$exe;
			if (file_exists(ROOT_DIR.$new_filename)) {
	  			// Если есть, придумываем другое название файлу
			   	$i = 0; $exist = true;
				while ($exist && ++$i < 999) {
					$new_filename = $pathTo.'/'.$fname.'_'.$i .'.'.$exe;
					if (!file_exists(ROOT_DIR.$new_filename)) $exist = false;
				}
			}

	  		// Копируем файл в указанную папку
			copy($tmp_filename, ROOT_DIR.$new_filename);
			return $new_filename;
		}
    }

    /**
	* @return null
	* @param String $folder - Путь к папке
	* @desc Удаляет папку со всеми вложенными файлами и папками
	*/
    function deleteDir($folder){
        if (is_dir($folder)){

        	$handle = opendir($folder);
         	while ($subfile = readdir($handle)){
          		if ($subfile == '.' or $subfile == '..') continue;
            	if (is_file($subfile))
            		unlink("{$folder}/{$subfile}");
             	else
             		system::deleteDir("{$folder}/{$subfile}");
          	}

           	closedir($handle);
            rmdir ($folder);

        } else unlink($folder);
	}





	// +++	Функционал для тестирования скорости работы отдельных участков системы	+++

	/**
	* @return null
	* @param integer $num - Номер временой метки
	* @desc Ставит временную метку от которой начнется отсчет времени.
	*/
	static function setTimeLabel($num){

        list($msec,$sec)=explode(chr(32),microtime());
        self::$TimeLabel[$num] = $sec + $msec;

        if (!isset(self::$SUMTimeLabel[$num]))
        	self::$SUMTimeLabel[$num] = 0;
	}

	/**
	* @return String Если $echo == false вернет прошедшее время в секундах
	* @param integer $num - Номер временой метки
	* @param boolean $echo - Если true, выведет время на экран, иначе вернет как результат
	* @param string $text - Текс-подсказка с которым необходимо вывести прошедщее время
	* @desc Вернет (выведет на страницу) прошедшее время с момента выполнения setTimeLabel().
	*/
	static function getTimeLabel($num, $echo = true, $text = ''){

        list($msec,$sec)=explode(chr(32),microtime());
        $mini_time = round(($sec+$msec) - self::$TimeLabel[$num], 5);

        self::$SUMTimeLabel[$num] += $mini_time;

        $text = ($text == '') ? ' - TimeLabel #'.$num : $text;

        if ($echo)
        	echo $mini_time.$text.'<br>';
        else
        	return $mini_time;

	}

	/**
	* @return String Если $echo == false вернет прошедшее время в секундах
	* @param integer $num - Номер временой метки
	* @param boolean $echo - Если true, выведет время на экран, иначе вернет как результат
	* @param string $text - Текс-подсказка с которым необходимо вывести прошедщее время
	* @desc Вернет (выведет на страницу) сумму всех временных меток с указанным индексом.
	*/
	static function getSumTimeLabel($num, $echo = true, $text = ''){

        $text = ($text == '') ? ' - SUMTimeLabel #'.$num : $text;

        $time = (isset(self::$SUMTimeLabel[$num])) ? self::$SUMTimeLabel[$num] : 0;
        
        if ($echo)
        	echo $time.$text.'<br>';
        else
        	return $time;

	}

    /**
	* @return null
	* @param integer $num - Номер временой метки
	* @desc Обнуляет значение временной метки
	*/
	static function clearSumTimeLabel($num){
    	self::$SUMTimeLabel[$num] = 0;
	}





	// +++	Другие функции	+++

    

	// функция превода текста с кириллицы в транслит
	static function translite($str) {

	    $transtable = array(
	        'А' => 'A',
	        'Б' => 'B',
	        'В' => 'V',
	        'Г' => 'G',
	        'Д' => 'D',
	        'Е' => 'E',
	        'Ё' => 'Yo',
	        'Ж' => 'Zh',
	        'З' => 'Z',
	        'И' => 'I',
	        'Й' => 'Y',
	        'К' => 'K',
	        'Л' => 'L',
	        'М' => 'M',
	        'Н' => 'N',
	        'О' => 'O',
	        'П' => 'P',
	        'Р' => 'R',
	        'С' => 'S',
	        'Т' => 'T',
	        'У' => 'U',
	        'Ф' => 'F',
	        'Х' => 'H',
	        'Ц' => 'Ts',
	        'Ч' => 'Ch',
	        'Ш' => 'Sh',
	        'Щ' => 'Shch',
	        'Ъ' => '',
	        'Ы' => 'I',
	        'Ь' => '',
	        'Э' => 'E',
	        'Ю' => 'Yu',
	        'Я' => 'Ya',
	        'а' => 'a',
	        'б' => 'b',
	        'в' => 'v',
	        'г' => 'g',
	        'д' => 'd',
	        'е' => 'e',
	        'ё' => 'yo',
	        'ж' => 'zh',
	        'з' => 'z',
	        'и' => 'i',
	        'й' => 'y',
	        'к' => 'k',
	        'л' => 'l',
	        'м' => 'm',
	        'н' => 'n',
	        'о' => 'o',
	        'п' => 'p',
	        'р' => 'r',
	        'с' => 's',
	        'т' => 't',
	        'у' => 'u',
	        'ф' => 'f',
	        'х' => 'h',
	        'ц' => 'ts',
	        'ч' => 'ch',
	        'ш' => 'sh',
	        'щ' => 'shch',
	        'ъ' => '',
	        'ы' => 'i',
	        'ь' => '',
	        'э' => 'e',
	        'ю' => 'yu',
	        'я' => 'ya',
	        ' ' => '_',
            '%' => '_',
            ',' => '_',
            '(' => '_',
            ')' => '_',
            '+' => '_');

	    $str = strtr($str, $transtable);
	    return $str;
	}

    // Вернет true, если система запущена на локальном сервере
    static function isLocalhost() {
        return ($_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_ADDR'] == 'localhost');
    }

	// Вернет true, если запрос был передан через Ajax
	static function isAjax() {

        // Проверяем, есть ли функция. Нужно если PHP запущен как CGI-приложение
	    if (!function_exists('getallheaders')){
		    function getallheaders() {
		       foreach ($_SERVER as $name => $value)
		           if (substr($name, 0, 5) == 'HTTP_')
		               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
		       return $headers;
		    }
		}

        // Определяем по заголовкам
		$allHeaders = getallheaders();
	    foreach ($allHeaders as $name => $value )
	    	if (strtolower($name) == 'x-requested-with' && $value == 'XMLHttpRequest' )
	        	return true;

	   return false;
	}

	/**
	* @return Обработанное значение, если переменной нет вернет false
	* @param any $var - Имя переменной в массиве $_POST
	* @param const $type - Способ проверки переменной
	* @param integer $maxlen - Максимальная длина значения
	* @desc Вернет проверенную (если указан $type) переменную массива $_POST.
			Работает аналогично методу system::checkVar();
	*/
	static function POST($var, $type = 0, $maxlen = 0) {
	    if (!isset($_POST[$var]))
	    	return false;
	    else if (empty($type))
	    	return $_POST[$var];
	    else
			return self::checkVar($_POST[$var], $type, $maxlen);
	}

	// Сохраняет в сессию все пришедшие через POST переменные. Используется совместно с методом page::assignSavingPost().
	static function savePostToSession() {
	    if (is_array($_POST) && count($_POST) > 0)
	    	while(list($key, $text) = each($_POST))
	    		$_SESSION['SAVING_POST'][$key] = $text;
	}

    /**
	* @param string $errorIndex - Идентификатор ошибки, любая строка
	* @param array $param - Массив может иметь следующие параметры:
     *                       alert_msg - сообщение об ошибке
     *                       alert_field - имя поля вызвавшего ошибку
     *                       alert_error - код ошибки
	* @desc Сохраняет в сессию информацию об ошибке.
	*/
	static function saveErrorToSession($errorIndex, $param) {

        if (is_array($param)) {

            foreach($param as $key => $val)
                $_SESSION['error'][$errorIndex]['alert_'.$key] = $val;

            return true;
        } 

        return false;
	}

    // Проверяет имеет ли указанное поле(пришедшее через POST) значение равное коду капчи.
    static function validCapcha($field_name) {

        $ret = (system::POST($field_name) == $_SESSION['core_secret_number']);
        $_SESSION['core_secret_number'] = '';
        
        return $ret;
    }

    // Вернет в браузер ответ в формате Json. $param - список параметров ответа.
    static function json($param) {
        echo json_encode($param);
        system::stop();
    }

    /**
	* @return null
	* @param string $templ_name - Полный путь до шаблона письма, от папки /template
	* @param string $email - E-mail на который нужно отправить письмо.
	* @param string $from - E-mail от кого отправляется письмо
	* @param string $from_name - Имя автора письма
	* @desc Парсит письмо по указанному шаблону и отправляет его на указанный ящик.
	*/
	static function sendMail($templ_name, $email, $from = '', $from_name = '') {

        $TEMPLATE = page::getTemplate($templ_name);

	    if (is_array($TEMPLATE)) {

            page::assign('domain', domains::curDomain()->getName());
            page::assign('site_name', domains::curDomain()->getSiteName());
	        page::assign('base_email', domains::curDomain()->getEmail());

	        if (empty($from))
	        	$from = domains::curDomain()->getEmail();

            if (empty($from_name))
	        	$from_name = domains::curDomain()->getSiteName();

            // Отправляет письмо с инструкциями
	        $mail = new phpmailer();
	        $mail->From = $from;
	        $mail->FromName = $from_name;
	        $mail->AddAddress($email);
	        $mail->WordWrap = 50;
	        $mail->IsHTML(true);
	        $mail->Subject = page::parse($TEMPLATE['subject']);
	        $mail->Body = page::parse($TEMPLATE['frame']);
	        $mail->Send();
	    }
	}

    // Вспомогательная функция для проверки входящих данных классом Jevix
    private static function checkByJevix($text, $autolink = true) {

        $jevix = new Jevix();

        //Конфигурация

        if (user::isAdmin()) {

            // Администратору доверяем больше, разрешаем ему втавлять потенциально опасные теги - object, param, embed, video, iframe

            // 1. Устанавливаем разрешённые теги. (Все не разрешенные теги считаются запрещенными.)
            $jevix->cfgAllowTags(array('table', 'tr', 'td', 'th', 'p', 'a', 'img', 'i', 'b', 'u', 'em', 'strong', 'nobr',
                                      'li', 'ol', 'ul', 'sup', 'abbr', 'pre', 'acronym', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                                      'adabracut', 'br', 'code', 'div', 'span', 'object', 'param', 'embed', 'video', 'code', 'iframe', 'hr'));

            // 2. Устанавливаем коротие теги. (не имеющие закрывающего тега)
            $jevix->cfgSetTagShort(array('br','img', 'param', 'embed', 'hr'));

            // 3. Устанавливаем преформатированные теги. (в них все будет заменятся на HTML сущности)
            $jevix->cfgSetTagPreformatted(array('pre'));

            // 4. Устанавливаем теги, которые необходимо вырезать из текста вместе с контентом.
            $jevix->cfgSetTagCutWithContent(array('script', 'javascript', 'style', 'applet'));

            // 5. Устанавливаем разрешённые параметры тегов. Также можно устанавливать допустимые значения этих параметров.
            $jevix->cfgAllowTagParams('div', array('class', 'id', 'style'));
            $jevix->cfgAllowTagParams('p', array('style'));
            $jevix->cfgAllowTagParams('h1', array('style'));
            $jevix->cfgAllowTagParams('h2', array('style'));
            $jevix->cfgAllowTagParams('h3', array('style'));
            $jevix->cfgAllowTagParams('h4', array('style'));
            $jevix->cfgAllowTagParams('h5', array('style'));
            $jevix->cfgAllowTagParams('h6', array('style'));
            $jevix->cfgAllowTagParams('span', array('class', 'id', 'style'));
            $jevix->cfgAllowTagParams('a', array('title', 'href', 'target', 'class', 'id', 'rel', 'style'));
            $jevix->cfgAllowTagParams('img', array('style', 'src', 'alt' => '#text', 'title', 'align' => array('right', 'left', 'center'), 'width' => '#int', 'height' => '#int', 'hspace' => '#int', 'vspace' => '#int'));
            $jevix->cfgAllowTagParams('table', array('border', 'class', 'width', 'align', 'valign', 'style', 'id'));
            $jevix->cfgAllowTagParams('tr', array('height', 'class', 'style'));
            $jevix->cfgAllowTagParams('td', array('colspan', 'rowspan', 'class', 'width', 'height', 'align', 'valign', 'style'));
            $jevix->cfgAllowTagParams('th', array('colspan', 'rowspan', 'class', 'width', 'height', 'align', 'valign', 'style'));
            $jevix->cfgAllowTagParams('object', array('width', 'height'));
            $jevix->cfgAllowTagParams('param', array('name', 'value'));
            $jevix->cfgAllowTagParams('embed', array('src', 'type', 'allowscriptaccess', 'allowfullscreen', 'width', 'height', 'wmode'));
            $jevix->cfgAllowTagParams('iframe', array('src', 'type', 'allowscriptaccess', 'allowfullscreen', 'width', 'height', 'wmode', 'frameborder'));

            // 6. Устанавливаем параметры тегов являющиеся обязательными. Без них вырезает тег оставляя содержимое.
            $jevix->cfgSetTagParamsRequired('img', 'src');

            // 9. Устанавливаем автозамену
            $jevix->cfgSetAutoReplace(array('+/-', '(c)', '(r)'), array('±', '©', '®'));

            // 10. Включаем или выключаем режим XHTML. (по умолчанию включен)
            $jevix->cfgSetXHTMLMode(true);

            // 11. Включаем или выключаем режим замены переноса строк на тег
            $jevix->cfgSetAutoBrMode(false);

            // 12. Включаем или выключаем режим автоматического определения ссылок. (по умолчанию включен)
            $jevix->cfgSetAutoLinkMode($autolink);

            // 13. Отключаем типографирование в определенном теге
            $jevix->cfgSetTagNoTypography('code','video','iframe');

            // 14. Устанавливаем пустые теги
            $jevix->cfgSetTagIsEmpty('iframe');


        } else {
            

            // 1. Устанавливаем разрешённые теги. (Все не разрешенные теги считаются запрещенными.)
            $jevix->cfgAllowTags(array('table', 'tr', 'td', 'th', 'p', 'a', 'img', 'i', 'b', 'u', 'em', 'strong', 'nobr',
                                      'li', 'ol', 'ul', 'sup', 'abbr', 'pre', 'acronym', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                                      'adabracut', 'br', 'code', 'div', 'span'));

            // 2. Устанавливаем коротие теги. (не имеющие закрывающего тега)
            $jevix->cfgSetTagShort(array('br','img'));

            // 3. Устанавливаем преформатированные теги. (в них все будет заменятся на HTML сущности)
            $jevix->cfgSetTagPreformatted(array('pre'));

            // 4. Устанавливаем теги, которые необходимо вырезать из текста вместе с контентом.
            $jevix->cfgSetTagCutWithContent(array('script', 'javascript', 'object', 'iframe', 'style', 'embed', 'applet'));

            // 5. Устанавливаем разрешённые параметры тегов. Также можно устанавливать допустимые значения этих параметров.
            $jevix->cfgAllowTagParams('div', array('class', 'id'));
            $jevix->cfgAllowTagParams('span', array('class', 'id'));
            $jevix->cfgAllowTagParams('a', array('title', 'href', 'target', 'class', 'id'));
            $jevix->cfgAllowTagParams('img', array('src', 'alt' => '#text', 'title', 'align' => array('right', 'left', 'center'), 'width' => '#int', 'height' => '#int', 'hspace' => '#int', 'vspace' => '#int'));
            $jevix->cfgAllowTagParams('table', array('border', 'class', 'width', 'align', 'valign'));
            $jevix->cfgAllowTagParams('tr', array('height', 'class'));
            $jevix->cfgAllowTagParams('td', array('colspan', 'rowspan', 'class', 'width', 'height', 'align', 'valign'));
            $jevix->cfgAllowTagParams('th', array('colspan', 'rowspan', 'class', 'width', 'height', 'align', 'valign'));

            // 6. Устанавливаем параметры тегов являющиеся обязательными. Без них вырезает тег оставляя содержимое.
            $jevix->cfgSetTagParamsRequired('img', 'src');
            //$jevix->cfgSetTagParamsRequired('a', 'href');

            // 7. Устанавливаем теги которые может содержать тег контейнер
            //    cfgSetTagChilds($tag, $childs, $isContainerOnly, $isChildOnly)
            //       $isContainerOnly : тег является только контейнером для других тегов и не может содержать текст (по умолчанию false)
            //       $isChildOnly : вложенные теги не могут присутствовать нигде кроме указанного тега (по умолчанию false)
            //$jevix->cfgSetTagChilds('ul', 'li', true, false);

            // 8. Устанавливаем атрибуты тегов, которые будут добавлятся автоматически
            //$jevix->cfgSetTagParamDefault('a', 'rel', null, true);
            //$jevix->cfgSetTagParamsAutoAdd('a', array('rel' => 'nofollow'));
            //$jevix->cfgSetTagParamsAutoAdd('a', array('name'=>'rel', 'value' => 'nofollow', 'rewrite' => true));

            //$jevix->cfgSetTagParamDefault('img', 'width',  '300px');
            //$jevix->cfgSetTagParamDefault('img', 'height', '300px');
            //$jevix->cfgSetTagParamsAutoAdd('img', array('width' => '300', 'height' => '300'));
            //$jevix->cfgSetTagParamsAutoAdd('img', array(array('name'=>'width', 'value' => '300'), array('name'=>'height', 'value' => '300') ));

            // 9. Устанавливаем автозамену
            $jevix->cfgSetAutoReplace(array('+/-', '(c)', '(r)'), array('±', '©', '®'));

            // 10. Включаем или выключаем режим XHTML. (по умолчанию включен)
            $jevix->cfgSetXHTMLMode(true);

            // 11. Включаем или выключаем режим замены переноса строк на тег <br/>. (по умолчанию включен)
            $jevix->cfgSetAutoBrMode(false);

            // 12. Включаем или выключаем режим автоматического определения ссылок. (по умолчанию включен)
            $jevix->cfgSetAutoLinkMode($autolink);

            // 13. Отключаем типографирование в определенном теге
            $jevix->cfgSetTagNoTypography('code');

        }

        // Переменная, в которую будут записыватся ошибки
        $errors = null;

        return $jevix->parse($text, $errors);
    }


	/**
	* @return Переданное значение, если значение не соотвествует, возвращает FALSE
	* @param any $var -  Значение переменной
	* @param Int $type -  Тип которому должно соответствовать значение
	                      Предпочтительней использовать константы:
				            isInt -        Значение Integer, число без дробной части
				            isNum -        Значение Integer или Float, число с дробной частью
				            isText -       Любой большой или малый текст передаваеммый в SQL-запрос, в том числе и HTML
				            isEmail -      Email адрес
				            isString -     Русские и английские буквы, а так же цифры.
				            isPseudoUrl -  Строчные английские буквы, цифры, а так же "тире"
				            isMD5 -        Хэш MD5
				            isSHA1 -       Хэш SHA1
				            isBool -       Бинарное значение
				            isDate -       Значение Date соотвествено типу в MySql
				            isDateTime -   Значение DateTime соотвествено типу в MySql
				            isVarName -    Названия переменных - Числа и символы, знак "_".
				            isPhone -	   Цифры, пробел, и символы "() -,+"
				            isUrl -    	   Ссылка или адрес сайта (с http и без)
				            isPassword -   Проверяет значение на количество символов и кодирует в соотвествии с правилами системы.
				            isDomain -     Доменное имя сайта, без http.
                            isRuDomain -   Доменное имя сайта, без http с русскими символами для указания доменов .РФ
				            isPrice -      Значение integer или float, не отрицательное.
	* @param Int $maxlen - Максимальный размер (длина) передаваемого значения. Если == 0, не проверяет длину
	* @param boolean $ckeckByJevix - Если true, будет выполнена дополнительная проверка с помощью библиотеки Jevix для типов isString и isText.
    * @desc Проверка значений на соответствие типу
	*/
	static function checkVar($var, $type, $maxlen = 0, $ckeckByJevix = true) {

        $retarr=array();

        if(is_array($var)) { // если нужно обработать массив с именами
            foreach($var as $v)
                $retarr[$v] = self::checkVar($v, $type, $maxlen);

            if(sizeof($retarr)>0)
                return $retarr; // возвращаем массив
        }

        // убираем лишние бэкслэши
        if(get_magic_quotes_gpc())
            $var = stripslashes($var);

        //обрубаем лишнее
        if ($maxlen > 0)
            $var = substr($var, 0, $maxlen);

        // теперь обрабатываем в соответствии с типом
        switch($type) {

            case isInt : // число integer
                return is_integer($var) ? $var : intval($var);
                break;

            case isNum : // число integer или float
                $var = str_replace(',', '.', $var);
                if (empty($var)) return 0;
                return is_numeric($var) ? $var : false;
                break;

            case isPrice : // число integer или float не отрицательный
                $var = str_replace(',', '.', $var);
                if (empty($var)) return 0;
                return (is_numeric($var) && $var >= 0) ? $var : false;
                break;

            case isString : // числовые и буквенные символы

                $var = str_replace(array('"', '\\'), array('&quot;', ''), trim($var));
                if (empty($var)) return '';

                if ($ckeckByJevix)
                    $var = self::checkByJevix($var, false);

                return preg_match("/^[а-яА-ЯёЁa-zA-Z0-9.,!?%№ -—_«…»|']+$/u", $var) ? $var : false;
                break;

            case isPseudoUrl : // числовые и буквенные символы
                return preg_match("/^[a-z0-9-_]+$/u", trim($var)) ? trim($var) : false;
                break;

            case isVarName : // числовые и буквенные символы
                return preg_match("/^[a-z0-9_]+$/u", trim($var)) ? trim($var) : false;
                break;

            case isText : // строка, которая попадет в SQL-запрос

                if ($var == '')
                    return '';

                if ($ckeckByJevix)
                    $var = self::checkByJevix($var, false);

                return addslashes($var);
                break;

            case isEmail: // email-адрес
                if ($var == '') return ''; else
                    return preg_match('/^[\w\.\-]+@\w+[\w\.\-]*?\.\w{2,4}$/',$var) ? $var : false;
                break;

            case isMD5 : // md5-хэш
                return preg_match("/^[a-fA-F0-9]{32}+$/",$var) ? $var : false;
                break;

            case isSHA1 : // sha1-хэш
                return preg_match("/^[a-fA-F0-9]{40}+$/",$var) ? $var : false;
                break;

            case isBool: // булева величина
                return ($var == "1" || $var === 1 || $var === true) ? 1 : 0;
                break;

            case isDate: // тип Date
                if (!is_integer($var)) $var = strtotime($var);
                return date('Y-m-d', $var);
                break;

            case isDateTime: // тип DateTime
                if (!is_integer($var)) $var = strtotime($var);
                return date('Y-m-d H:i:s', $var);
                break;

            case isPhone: // телефонный номер
                return preg_match("/^[0-9 -),(+]+$/", trim($var)) ? $var : false;
                break;

            case isUrl: // относительный или абсолютный урл
                if ($var == '') return ''; else
                    return preg_match("[(((f|ht){1}tp:/)*/[-a-zA-Z0-9@:%_\+.~#?&//=]+)|^\/{1}]", trim($var)) ? $var : false;
                break;

            case isAbsUrl: // Абсолютный урл
                if ($var == '') return ''; else
                    return preg_match("[(((f|ht){1}tp:/)./[-a-zA-Z0-9@:%_\+.~#?&//=]+)|^\/{1}]", trim($var)) ? false : $var ;
                break;

            case isPassword: // Пароль, проверка на длину и кодирование
                if (empty($var))
                    return '';
                if (strlen($var) > 5)
                    return md5(PASS_PREFIX.md5($var));
                else
                    return false;
                break;

            case isDomain : // числовые и буквенные символы
                return preg_match("/^[a-z0-9-.]+$/u", trim($var)) ? $var : false;
                break;

            case isRuDomain : // в том числе и .РФ
                return preg_match("/^[а-яёa-z0-9-.]+$/u", trim($var)) ? $var : false;
                break;

        }
    }

}

// Регистрируем метод обработки автоподгрузки классов
function reg_autoload($class_name) {
	system::loadClass($class_name);
}
spl_autoload_register('reg_autoload');

?>