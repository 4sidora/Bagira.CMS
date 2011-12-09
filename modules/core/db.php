<?php

define('records', 999);
define('record', 888);
define('values', 777);
define('value', 666);

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Статический класс для доступа к базе данных. В своей работе использует класс PHP Data Objects (PDO)
*/

class db{

	static private $sqlList = array();
	static private $pdo;
	static private $driver;

    // Создание соединения с БД
	private static function init(){

        if (self::$pdo == null) {

            try {

                self::$pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                if ('mysql'==substr(DB_DSN,0,5)){
                    self::$driver = 'mysql';
                    self::$pdo->exec("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");
                }

            } catch (PDOException $e) {

                $msg = '<h3>Невозможно установить соединение с базой данных!</h3><br/>';

                if ($e->getCode() == 2001)
                   $msg .= 'Невозможно создать socket-соединение из за ограничений сервера! ';
                else if ($e->getCode() == 2002)
                    $msg .= 'Превышено время ожидания сервера БД! Скорей всего сервер либо временно не доступен, либо не существует. Проверьте настройки подключения к БД.';
                else if ($e->getCode() == 1049)
                    $msg .= 'Указанная БД не существует! Перепроверьте настройки подключения к базе данных.';
                else
                    $msg .= 'Запрещен доступ для указанного логина и пароля. Перепроверьте настройки подключения к базе данных.';


                //$msg .= '<br/><br/><a href="">'.substr($e->getMessage(), 0, 22).'</a>';
                $msg .= '<br/><br/>'.substr($e->getMessage(), 0, 22).'';

                page::globalVar('title', 'Bagira.CMS');
                page::globalVar('content', $msg);
                page::parseIt(TEMPL_DIR.'/offline.tpl', true);
            }
		}
	}

    // Закрываем соединение с БД.
	static function close(){
		self::$pdo = null;
		self::$sqlList = null;
	}

    // Отрывает транзакцию
	static function beginTransaction(){
	    self::init();
		return self::$pdo->beginTransaction();
	}

    // Выполняет транзакцию
	static function commit(){
		self::init();
		return self::$pdo->commit();
	}

	// Отменить выполнение транзакции
	static function rollBack(){
		self::init();
		return self::$pdo->rollBack();
	}

    // Возвращает код ошибки SQL
	static function errorCode(){
		self::init();
		return self::$pdo->errorCode();
	}

    static function issetError() {
        return (self::errorCode() != '00000');
    }

    // Возвращает развернутую информацию об ошибке SQL
	static function errorInfo(){
		self::init();
		return self::$pdo->errorInfo();
	}

	// Подготавливает запрос с параметрами
	static function prepare($sql, $driver_options = array()){
		self::init();
		return self::$pdo->prepare(self::addPrefix($sql), $driver_options);
	}

    // Выполняет запрос, возвращает результат для обработки
	static function query($sql){
		self::init();
		return self::$pdo->query(self::addPrefix($sql));
	}

	/**
	* @return Результат выполнения запроса
	* @param string $sql - Код SQL-запроса
	* @param const $method - Метод обработки результатов выполнения запроса
			Используйте одну из констант:
				records	-	Вернет результат в виде двумерного ассоциативного массива
			    record	-	Вернет результат в виде одномерного ассоциативного массива
			    values	-	Вернет результат в виде одномерного массива
			    value   -	Вернет результат в виде значения
	* @param boolean $show_info - Если true, выведет информацию для отладки
	* @desc Выполняет запрос, возвращает результат для обработки
	*/
	public static function q($sql, $method = 0, $show_info = false){

        self::init();

        system::setTimeLabel(999);
        $sql = self::addPrefix($sql);
        //system::log($sql);
      //  system::log(str_replace(array(Chr(9), Chr(10), Chr(13)), '', $sql), error);

		if ($show_info) echo '<b>SQL-запрос:</b><br />'.$sql.'<br /><br />';

        try {

	        if (strtolower(substr($sql, 0, 6)) == 'insert') {

	            // Обработка запроса INSERT
	            self::$pdo->exec($sql);
	            $value = self::$pdo->lastInsertId();

	        } else if (empty($method)){

	            // Просто выполнение запроса, без возврата результата.
	            $value = self::$pdo->exec($sql);

	        } else {

	            // Обработка запроса SELECT
				$res = self::$pdo->query($sql);

		        if ($method == records){

		            // Получаем много записей
		            $value = $res->fetchAll(PDO::FETCH_ASSOC);

		        } else  if ($method == record){

		            // Получаем одну запись
		            $value = $res->fetch(PDO::FETCH_ASSOC);

		        } else  if ($method == values){

		            // Получаем одну запись, нумерованный массив
		            $value = $res->fetch(PDO::FETCH_NUM);

		        } else if ($method == value){

	                // Получаем одно значение
		            $mas = $res->fetch(PDO::FETCH_NUM);
		            $value = (isset($mas[0])) ? $mas[0] : '';

		        }
	        }

        } catch (Exception $e) {

			// Ошибка при выполнении запроса
            $value = false;

			// Пишем в журнал
            system::log($e->getMessage(), error);
            system::log(str_replace(array(Chr(9), Chr(10), Chr(13)), '', $sql), error);

            // Вывод сообщения об ошибке
            if (SHOW_SQL_ERRORS != -1 && ($_SERVER['SERVER_ADDR'] == '127.0.0.1' || SHOW_SQL_ERRORS)) {

                if ($show_info){
	    			echo '<b>ОШИБКИ:</b><br />'.$e->getMessage().'<br /><br />';
	      		} else {
                    header('Content-Type: text/html; charset=utf-8');
	        		echo '<b>SQL-запрос:</b><br />'.$sql.'<br /><br />';
	          		echo '<b>ОШИБКИ:</b><br />'.$e->getMessage().'<br /><br />';
	        	}

            } else if ($e->getCode() == '42S02') {

                // Отсутсвие таблиц. Выводим всегда, если не на локалхосте.
                $msg = '<h3>Ошибка при работе с БД!</h3><br/>';
                $msg .= 'Соединение установлено, но необходимые таблицы отсутствуют. <br/><br/>Скорей всего БД пустая или  в ней не хватает некоторых таблиц. Попробуйте перезалить дамп БД, а так же перепроверьте префикс для таблиц в настройках подключения к БД.';

                page::globalVar('title', 'Bagira.CMS');
                page::globalVar('content', $msg);
                page::parseIt(TEMPL_DIR.'/offline.tpl', true);
                die;
            }
		}

        if ($show_info) {
            echo '<b>Результат:</b><br />';
            print_r($value);
            echo '<br /><br />';
        }

        $time = system::getTimeLabel(999, false);
        self::toList($sql.'<br />'.$time);

        return $value;
	}

    // Выполняет запрос на "изменение" (INSERT, DELETE и др.)
    // возвращает количество затронутых записей
	static function exec($sql){
		self::init();
		return self::$pdo->exec(self::addPrefix($sql));
	}

	// Возвращает ID последнего добавленного элемента
	static function lastInsertId($name = null){
		self::init();
		return self::$pdo->lastInsertId($name);
	}

     // Закавычивает текст для использования в SQL запросе
	static function quote($string, $parameter_type = null){
		self::init();
		return self::$pdo->quote($string, $parameter_type);
	}

	static function specQuote($string){
		return str_replace('"', '&quot;', $string);
	}

    // Добавляет запрос в список запросов
	private static function toList($sql){
		self::$sqlList[] = $sql;
	}

	// Возвращает массив со всеми выполненными запросами
	static function getQueryList(){
		return self::$sqlList;
	}

    // Возвращает количество выполненных запросов
	static function getQueryCount(){
		return sizeof(self::$sqlList);
	}

	// Добавление префиксов
	private static function addPrefix($sql){
		return strtr($sql, array('<<' => '`'.DB_PR, '>>' => '`'));
	}

}

?>