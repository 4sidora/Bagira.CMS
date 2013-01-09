<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс для обработки шаблонов. Класс является статическим.
*/

class page {

    static private $page_mas = array();
    static private $assign = array();               // массив значениями "ключевое слово -> значение"
    static private $macro = array();                // кэш макросов
    static private $delimiters = array('%', '%');   // Разграничители
    static private $arr;                            // Содержит переработанный шаблон, используется для вывода на экран
    static public $macros = 1;

    static private $isCachePage = null;


    // Список запрещенных для кэширования страниц (со всеми вложенными подразделами)
    static private $cacheTabuPages =  array(
        '/mpanel',
        '/core',
        '/comments',
        '/eshop',
        '/feedback',
        '/search',
        '/structure',
        '/subscription',
        '/users',
        '/voting',
		'/booking'
    );


    /*
        Список исключений. Страницы, которые можно кэшировать. Используются регулярные выражения.

        В качестве примера:
            /\/structure\/map\z/            Точное совпадение, без подразделов /structure/map
            /\/structure\/map/              Страница /structure/map со всеми подразделами
            /\/structure\/[0-9]+\z/i        Точное совпадение, второй параметр число /structure/(число)
     */
    static private $cacheExcludePages =  array(
        '/\/structure\/map\z/',
		'/\/booking\/schedule/',
		'/\/booking\/[0-9]+\z/i'
    );


    // Список запрещенных для кэширования макросов
    static private $cacheTabuMacroses =  array(
        'core.resize',
        'users.authForm',
        'users.editForm',
        'users.addForm',
        'users.recover',
        'users.changePassword',
        'voting.objView',
        'voting.objList',
        'comments.count',
        'comments.tree',
        'comments.form',
        'feedback.form',
        'feedback.autoForm',
        'structure.filter'
    );


    // Список запрещенных для вызова через Ajax макросов
    // Если список пустой, запрещены все макросы
    static private $tabuMacroses =  array(
        'core.resize',
		'booking.bookingList',
		'booking.form',
		'booking.printBooking'
    );



    /**
     * @return null
     * @param string $d1 - Ограничитель слева
     * @param string $d2 - Ограничитель справа
     * @desc Устанавливает и проверяет на корекность разграничители
     */
    static function init($d1 = '', $d2 = '') {
        
        if (!empty($d1) && !empty($d2)) {
            self::$delimiters[0] = preg_quote($d1);
            self::$delimiters[1] = preg_quote($d2);
        } else {
            self::$delimiters[0] = preg_quote('%');
            self::$delimiters[1] = preg_quote('%');
        }

    }

    // Загружает нужный файл шаблона из кеша
    static function getTemplate($file) {
        $site_prefix = (domains::curId() == 1 && languages::curId() == 1) ? '' : '/__'.str_replace('.', '_', domains::curDomain()->getName()).'_'.languages::curPrefix();

        if (file_exists(TEMPL_DIR.$site_prefix.$file)){

            include(TEMPL_DIR.$site_prefix.$file);
            return (isset($TEMPLATE)) ? $TEMPLATE : false;

        } else return false;
    }

    /**
     * @return null
     * @param array $array - Ассоциативный массив со значениями
     * @param string $param - Префик для парсинга на страницу. По умолчанию text, будут парситься макросы вида %text.key%
     * @desc Связывает значения указанного массива для парсинга на страницу
     */
    static function assignArray($array, $param = 'text') {
        if (!empty($array))
            while (list($key, $text) = each ($array))
                page::assign($param.'.'.$key, $text);
    }

    /**
     * @return null
     * @param string $param - Префик для парсинга на страницу. По умолчанию "obj", будут парситься макросы вида %obj.key%
     * @desc Связывает все сохраненные методом system::savePostToSession() переменные для парсинга на страницу
     */
    static function assignSavingPost($param = 'obj') {
        if (isset($_SESSION['SAVING_POST']) && is_array($_SESSION['SAVING_POST']) > 0) {
            while(list($key, $text) = each($_SESSION['SAVING_POST']))
                page::assign($param.'.'.$key, $text);
            $_SESSION['SAVING_POST'] = '';
        }
    }

    /**
     * @return null
     * @param string $errorIndex - Префик ошибки
     * @desc Парсит все данные об ошибке хранящиеся в сессии, записанные методом system::saveErrorToSession();
     */
    static function parseError($errorIndex) {

        if (!empty($_SESSION['error'][$errorIndex])) {

            $ses = $_SESSION['error'][$errorIndex];
            unset($_SESSION['error'][$errorIndex]);
            
        } else $ses = array();

        page::assign('alert_msg', (!empty($ses['alert_msg'])) ? $ses['alert_msg'] : '');
        page::assign('alert_error', (!empty($ses['alert_error'])) ? $ses['alert_error'] : '');
        page::assign('alert_field', (!empty($ses['alert_field'])) ? $ses['alert_field'] : '');
    }

    /**
     * @return null
     * @param string $arr - Имя макроса
     * @param string $val - Значение
     * @desc Объявляет макрос и присваивает ему значение для парсинга на страницу
     */
    static function assign($arr, $val = '') {

        if (empty($arr))
            trigger_error ('page::assign(): no given parameters', E_USER_ERROR);

        if (!is_array($arr)) {

            self::$assign[$arr] = $val;

        } else {

            while (list($key, $val) = each ($arr))
                if (!empty($key))
                    self::$assign[$key] = $val;
        }
    }

    /**
     * @return null
     * @param string $name - Имя макроса
     * @param string $text - Значение
     * @desc Объявляет глобальный макрос и присваивает ему значение для парсинга на страницу
    Глобальные макросы парсятся на страницу в последний момент, перед выводом содержимого на экран
     */
    static function globalVar($name, $text = ''){
        self::$page_mas[$name] = $text;
    }

    // Вернет значение глобального макроса
    static function getGlobalVar($name){
        if (isset(self::$page_mas[$name]))
            return self::$page_mas[$name];
    }

    /**
     * @return string обработанный шаблон
     * @param string $data - Шаблон для обработки
     * @param integer $type - Способ обработки необъявленных макросов (0 - не трогать, 1 - затирать)
     * @desc Обрабатывает указанный шаблон и возвращает результат в виде строки
     */
    static function parse($data = '', $type = 0) {
        if (is_array($data)) $data = implode("", $data);

        reset(self::$assign);
        while (list($key, $val) = each (self::$assign))
            $data = preg_replace("/".self::$delimiters[0]."\s*".preg_quote($key)."\s*".self::$delimiters[1]."/", "$val", $data);

        if ($type == 1){
            preg_match_all("/".self::$delimiters[0]."([a-z]*[0-9]*[_]*[.]*)+".self::$delimiters[1]."/", $data, $mass);
            while (list($key, $val) = each ($mass[0])) {
                $parr = substr($val, 1, strlen($val)-2);
                $data = preg_replace("/".self::$delimiters[0]."".str_replace('/', '\/', preg_quote($parr))."".self::$delimiters[1]."/", '', $data);
            }
        }

        if (self::$macros == 1){
			preg_match_all("/".self::$delimiters[0]."([A-Z]*[a-z]*[0-9]*[_]*)+[.]([A-Z]*[a-z]*[0-9]*[_]*)+[(]([A-Z]*[А-я]*[a-z]*[0-9]*[%@':\-.|<,>*#\/_ ]*)+([%]..+[%])*[)]".self::$delimiters[1]."/u", $data, $mass);
            while (list($key, $val) = each ($mass[0])) {

                $pos = strpos($val, '(');
                $macros = substr($val, 1, $pos - 1);

              /*  if ($macros == 'structure.filter' && )
                    self::$isCachePage = false;*/

                if (!CACHE_ENABLE || $type == 2 || (CACHE_ENABLE && !in_array($macros, self::$cacheTabuMacroses))) {

                    $parr = substr($val, 1, strlen($val)-2);
                    $data = preg_replace("/".self::$delimiters[0]."".str_replace('/', '\/', preg_quote($parr))."".self::$delimiters[1]."/", self::regFunction($val), $data);

                }
            }
        }
        return $data;
    }

    /**
     * @return null
     * @param string $key - Макрос который будет содержать результаты обработки
     * @param string $data - Шаблон для обработки
     * @desc Обрабатывает шаблон и связывает результаты с макросом
     */
    static function fParse($key, $data) {
        self::assign($key, self::parse($data));
    }

    /**
     * @return array
     * @param string $find - Начало макроса, например "obj."
     * @param string $templ - Шаблон или массив шаблонов в которых ведется поиск
     * @param array $names - Список блоков в которых нужно искать. Если не указан, ищет везде.
     * @param array $list_name - Префикс для блока оформления списка. Например для блока "frame_category_list" укажите "category".
     * @desc Находит в указанном шаблоне макросы соответствующие условиям
     */
    static function getFields($find, $templ, $names = '', $list_name = '') {
        $ret = array();
        $tmp_funct = array();

        if (empty($names) && !is_array($templ)) {

            // Поиск макросов в указаном шаблоне
            preg_match_all("/".self::$delimiters[0]."[$find]([a-z]*[0-9]*[_]*[.]*)+".self::$delimiters[1]."/", $templ, $mass);
            foreach($mass[0] as $val)
                $ret[$find][] = str_replace(array('%', $find.'.'), '', $val);

            preg_match_all("/".self::$delimiters[0]."([a-z]*[0-9]*[_]*)+".self::$delimiters[1]."/", $templ, $mass);
            foreach($mass[0] as $val)
                $ret['mono'][] = str_replace('%', '', $val);

            $f = "/".self::$delimiters[0]."([A-Z]*[a-z]*[0-9]*[_]*)+[.]([A-Z]*[a-z]*[0-9]*[_]*)+[(]/";
            preg_match_all($f, $templ, $mass);
            foreach($mass[0] as $val)
                $tmp_funct[str_replace(array('%', '('), '', $val)] = 1;

            // Макрос structure.getProperty
            $f = "/".self::$delimiters[0]."structure.getProperty[(]([A-Z]*[a-z]*[0-9]*[_ ]*)+[,]+/";
            preg_match_all($f, $templ, $mass);
            foreach($mass[0] as $val)
                $ret[$find][] = trim(str_replace(array('%structure.getProperty(', ','), '', $val));

        } else if (!empty($names)) {

            // Поиск макросов в шаблонах списков в зависимости от указанных классов объектов
            $tmp = $tmp_mono = array();
            while (list($num, $templ_name) = each ($names)) {
                $tmp_mas = array();

                if (isset($templ['list_'.$templ_name]))
                    $template = $templ['list_'.$templ_name];
                else if (isset($templ['list']))
                    $template = $templ['list'];

                // Поиск локальных макросов
                if (!empty($template)) {

                    // Составные макросы вида %obj.id%
                    preg_match_all("/".self::$delimiters[0]."[$find]([a-z]*[0-9]*[_]*[.]*)+".self::$delimiters[1]."/", $template, $mass);
                    foreach($mass[0] as $val) {
                        $zn = str_replace(array('%', $find.'.'), '', $val);
                        if (!isset($tmp_mas[$zn])) {
                            $tmp_mas[$zn] = 1;
                            if (isset($tmp[$zn])) $tmp[$zn]++; else $tmp[$zn] = 1;
                        }
                    }

                    // Макрос structure.getProperty
                    $f = "/".self::$delimiters[0]."structure.getProperty[(]([A-Z]*[a-z]*[0-9]*[_ ]*)+[,]+/";
                    preg_match_all($f, $template, $mass);
                    foreach($mass[0] as $val){
                        $zn = trim(str_replace(array('%structure.getProperty(', ','), '', $val));
                        if (!isset($tmp_mas[$zn])) {
                            $tmp_mas[$zn] = 1;
                            if (isset($tmp[$zn])) $tmp[$zn]++; else $tmp[$zn] = 1;
                        }
                    }

                    // Одинарные макросы вида %sub_menu%
                    preg_match_all("/".self::$delimiters[0]."([a-z]*[0-9]*[_]*)+".self::$delimiters[1]."/", $template, $mass);
                    foreach($mass[0] as $val)
                        $tmp_mono[str_replace(array('%', $find.'.'), '', $val)] = 1;
                }

                // Поиск макросов-функций вида %structure.navigataion()%
                $f = "/".self::$delimiters[0]."([A-Z]*[a-z]*[0-9]*[_]*)+[.]([A-Z]*[a-z]*[0-9]*[_]*)+[(]/";

                if (!empty($list_name) && isset($templ['frame_'.$list_name.'_list']))
                    $ctempl = $templ['frame_'.$list_name.'_list'];
                else if (isset($templ['frame_list']))
                    $ctempl = $templ['frame_list'];
                else
                    $ctempl = '';

                if (!empty($ctempl)) {
                    preg_match_all($f, $ctempl, $mass);
                    if (isset($mass[0]))
                        foreach($mass[0] as $val)
                            $tmp_funct[str_replace(array('%', '('), '', $val)] = 1;
                }
            }

            while (list($macros, $count) = each($tmp))  {

                //   echo $count.' == '.count($names).' = '.$macros.'<br /><br />';
                if ($count == count($names))
                    $ret[$find][] = $macros;
                $ret[$find.'_all'][] = $macros;
            }

            while (list($macros, $count) = each($tmp_mono))
                $ret['mono'][] = $macros;

            while (list($macros, $count) = each($tmp_funct))
                $ret['funct'][] = $macros;

        }

        return $ret;
    }

    // Регистрация функции и ее выполнение
    static private function regFunction($par) {

        $par = str_replace(', ', ',', $par);
        $pos = strpos($par, '.');

        // выделяем имя модуля
        $mod_name = substr($par, 1, $pos-1);
        $funct = substr($par, ($pos+1), strlen($par)-$pos-2);

        // выделяем имя функции
        $pos = strpos($funct, '(');
        $funct_name = substr($funct, 0, $pos);
        $ff = substr($funct, $pos, strlen($par)-$pos-1);
        preg_match_all("/".self::$delimiters[0]."([A-Z]*[a-z]*[0-9]*[_]*)+[.]([A-Z]*[a-z]*[0-9]*[_]*)+[(]([A-Z]*[a-z]*[0-9]*[%':\-.,\/_ ]*)+([%]..+[%])*[)]".self::$delimiters[1]."/", $ff, $mass);
        while (list($key, $val) = each ($mass[0])) {
            if ($val != '') {
                $parr = substr($val, 1, strlen($val)-2);
                $ff = preg_replace("/".self::$delimiters[0]."".str_replace('/', '\/', preg_quote($parr))."".self::$delimiters[1]."/", self::$reg_function($val), $ff);
            }
        }

        //получаем параметры функции в строку, разбиваем строку и заносим в массив
        $funct_par = str_replace(')', '', str_replace('(', '', $ff));
        $funct_parram = ARRAY();
        $tmp = strtok(str_replace(',', ' ,', $funct_par), ',');
        while ($tmp != '') {
            $funct_parram[] = trim($tmp);
            $tmp = strtok(',');
        }

        // Смотрим, есть ли объект с макросами в КЕШЕ. Если нет, загружаем.
        if (!isset(self::$macro[$mod_name])) {

            $macros_file = MODUL_DIR.'/'.$mod_name.'/macros.php';

            if (file_exists($macros_file)) {

                include_once($macros_file);
                $class_name = $mod_name.'Macros';

                if (class_exists($class_name))
                    eval('self::$macro[$mod_name] = new '.$class_name.'();');
            }
        }

        $macros = self::macros($mod_name);

        // Выполняем макрос
        if (isset($macros)) {

            if (method_exists($macros, $funct_name)) {

                $str = call_user_func_array(array($macros, $funct_name), $funct_parram);

            } else $str = 'Указанный макрос не существует "'.$mod_name.'.'.$funct_name.'()"<br>';

        } else $str = 'Указанный модуль не имеет макросов "'.$mod_name.'.'.$funct_name.'()"<br>';

        return $str;
    }

    /**
     * @return object
     * @param string $mod_name - Системное имя модуля
     * @desc Вернет объект содержащий класс макросов определенного модуля
     */
    static function macros($mod_name) {

        // Смотрим, есть ли объект с макросами в КЕШЕ. Если нет, загружаем.
        if (!isset(self::$macro[$mod_name])) {

            $macros_file = MODUL_DIR.'/'.$mod_name.'/macros.php';

            if (file_exists($macros_file)) {

                include_once($macros_file);
                $class_name = $mod_name.'Macros';

                if (class_exists($class_name))
                    eval('self::$macro[$mod_name] = new '.$class_name.'();');
            }
        }

        if (isset(self::$macro[$mod_name]))
            return self::$macro[$mod_name];
    }

    // Проверяет был ли вызов макроса через Ajax. Если был, выводит результат работы макроса.
    static function callAjaxMacros() {

        if (system::url(0) == 'ajax' && system::issetUrl(1) && system::issetUrl(2)) {

            $key = 'ajax-macros'.system::getCurrentUrlPN().md5(serialize($_POST));
            $macros_name = system::url(1).'.'.system::url(2);
            
            if (empty(self::$tabuMacroses) || in_array($macros_name, self::$tabuMacroses))

                $html = lang::get('STOP_AJAX_MACROSES');

            else if (in_array($macros_name, self::$cacheTabuMacroses))

                $html = self::getMacrosContent($_POST['parram']);

            else if (!($html = cache::get($key))) {

                $parram = (empty($_POST['parram'])) ? 0 : $_POST['parram'];
                $html = self::getMacrosContent($parram);

                // Записываем в кэш
                cache::set($key, $html);
            }

            echo $html;
			system::stop();
        }
    }

    private static function getMacrosContent($post_parram = 0) {

        $macros = self::macros(system::url(1));
        $parram = array();

        if (!empty($post_parram) && is_array($post_parram)) {

            while(list($key, $val) = each($post_parram))
                $parram[] = system::checkVar($val, isString);

        } else {

            $num = 3;
            while(system::issetUrl($num)) {
                $parram[] = system::url($num);
                $num++;
            }
        }

        if (method_exists($macros, system::url(2))) {

            $html = call_user_func_array(array($macros, system::url(2)), $parram);

        } else $html = 'Указанный макрос не существует "'.system::url(1).'.'.system::url(2).'()"<br>';

        return $html;
    }

    // Очищает все данные: массивы и переменные
    static function freshAll() {
        self::$assign = array();
        self::$macro = array();
        self::$arr = '';
    }

    static function disableCacheForThisPage(){
        self::$isCachePage = false;
    }

    // Определяет, можно ли кешировать текущую страницу
    static function isCashePage(){

        if (!CACHE_ENABLE)
            return false;

        if (self::$isCachePage === null) {

            self::$isCachePage = true;

            // Проверяем исключения
            if (in_array(system::getCurrentUrl(), self::$cacheExcludePages))

                // На точное совпадение
                return self::$isCachePage;

            else {

                // На вхождение части урла
                reset(self::$cacheExcludePages);

                foreach(self::$cacheExcludePages as $url)
                    if (preg_match($url, system::getCurrentUrl()))
                        return self::$isCachePage;

            }

            // Проверяем запрещенные для кэширования страницы
            reset(self::$cacheTabuPages);
            foreach(self::$cacheTabuPages as $url) {
                $pos = substr(system::getCurrentUrl(), 0, strlen($url));
                if ($pos == $url) {
                    self::$isCachePage = false;
                    break;
                }
            }
        }

        return self::$isCachePage;
    }

    static function checkCache() {

        if (self::isCashePage()) {

            if (($data = cache::get(system::getCurrentUrlPN()))) {

                if (!strpos($data['html'], 'structure.filter')) {

                    page::assign('page_id', $data['page_id']);

                    $pages = $data['active_pages'];
                    while (list($num, $id) = each($pages))
                        page::assign('page_id'.$num, $id);

                    while(list($key, $val) = each(self::$page_mas))
                        self::assign($key, $val);

                    self::$arr = self::parse(self::parse($data['html'], 2), 2);

                    // Выводит обработанный шаблон на печать
                    //echo str_replace('`%`', '%', self::$arr);
                    echo self::$arr;
                    self::freshAll();

                    // Завершение работы
                    system::stop();
                }
            }
        }

    }

    /**
     * @return null
     * @param string $filename - Путь к файлу шаблона
     * @param boolean $absolut - Если true - путь считается абсолютным и вычисляется от корневой папки, в которой находится движок
     * @desc Обрабатывает указанный файл шаблона и выводит его на страницу
     */
    static function parseIt($filename, $absolut = false, $withoutCache = false) {

        if (!system::$isAdmin && !$absolut) {
            $site_prefix = (domains::curId() == 1 && languages::curId() == 1) ? '' : '/__'.str_replace('.', '_', domains::curDomain()->getName()).'_'.languages::curPrefix();

            if (file_exists(TEMPL_DIR.$site_prefix.$filename))
                $filename = TEMPL_DIR.$site_prefix.$filename;
            else if (file_exists(TEMPL_DIR.$site_prefix.'/structure/default.tpl'))
                $filename = TEMPL_DIR.$site_prefix.'/structure/default.tpl';
            else {
                echo lang::get('ERROR_TEMPL2');
                system::stop();
            }
        }

        while(list($key, $val) = each(self::$page_mas))
            self::assign($key, $val);

        self::$arr = self::parse(file($filename));

        // Сохраняем страницу в кэш
        if (!$withoutCache && self::isCashePage()) {

            $page = array(
                'html' => self::$arr,
                'page_id' => ormPages::getCurPageId(),
                'page_url' => system::getCurrentUrlPN(),
                'active_pages' => ormPages::getActiveId(),
                'host' => $_SERVER['HTTP_HOST']
            );
            
            cache::set(system::getCurrentUrlPN(), $page);
        }

        // Вторично обрабатывает шаблон
        self::$arr = self::parse(self::parse(self::$arr, 2), 2);

        // Выводит обработанный шаблон на печать
        //echo str_replace('`%`', '%', self::$arr);
        echo self::$arr;
        self::freshAll();
    }

    /**
     * @return string
     * @param string $macros - Имя макроса
     * @param string $templ_file - Относительный путь к файлу
     * @desc Генерирует текст ошибки: "Шаблон не найден!"
     */
    static function errorNotFound($macros, $templ_file) {
        return lang::get('ERROR_TEMPL').'<i>'.$macros.'()</i><br />
        	'.lang::get('ERROR_NOTFOUND_TEMPL').'"/template'.$templ_file.'"';
    }

    /**
     * @return string
     * @param string $macros - Имя макроса
     * @param string $param - Параметры макроса
     * @param string $text - Текст ошибки
     * @desc Генерирует текст общей ошибки
     */
    static function error($macros, $param = '', $text = '') {
        $tmp = lang::get('ERROR_TEMPL').'<i>'.$macros.'('.$param.')</i>';

        if (!empty($text))
            $tmp .= '<br />'.$text;

        return $tmp;
    }

    /**
     * @return string
     * @param string $macros - Имя макроса
     * @param string $file - Относительный путь к файлу шаблона
     * @param string $block - Название блока
     * @desc Генерирует текст ошибки: "Не найден обязательный блок шаблона!"
     */
    static function errorBlock($macros, $file, $block) {

        $tmp = lang::get('ERROR_TEMPL').'<i>'.$macros.'()</i> ';

        $tmp .= str_replace(
            array('%file%', '%block%'),
            array('/template'.$file, $block),
            lang::get('ERROR_NOTFOUND_BLOCK')
        );

        return $tmp;
    }

}

?>