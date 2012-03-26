<?php

    Error_Reporting(E_ALL);
    session_start();

    ini_set('default_socket_timeout', 5);

    // Выберите часовой пояс, поже вынесим в настройки
   	//date_default_timezone_set('Europe/Moscow');
   	//date_default_timezone_set('Asia/Yekaterinburg');
   	//date_default_timezone_set('Asia/Omsk');
   	date_default_timezone_set('Asia/Novosibirsk');
   	//date_default_timezone_set('Asia/Krasnoyarsk');
   	//date_default_timezone_set('Asia/Irkutsk');
   	//date_default_timezone_set('Asia/Vladivostok');

    // Для тестирования сайта с мультидоменностью на localhost, раскомментируйте и впишите нужный домен.
    //$_SERVER['HTTP_HOST'] = 'mahog.ru';

    /*
    Если SHOW_SPEED == true, на странице (кроме ajax-запросов) будет выводится отладочная информация:
    	- время генерации страницы в сек.
    	- количество SQL-запросов
    	- текст SQL-запросов
    */
    define('SHOW_SPEED', false);

    /*
    Если true, на страницу будут выводится сообщения об SQL-ошибках. Если false, сообщения не выводятся.
    Для localhost сообщения выводятся всегда, что бы запретить их вывод совсем, укажите -1.
    */
    define('SHOW_SQL_ERRORS', false);


    // Засекаем время для определения скорости генерации страницы
    list($msec, $sec) = explode(chr(32), microtime());
    define('START_TIME', $sec + $msec);

    // Пути к основным папкам системы
	define('ROOT_DIR', getcwd());
	define('MODUL_DIR', ROOT_DIR.'/modules');
	define('TEMPL_DIR', ROOT_DIR.'/template');
    define('CACHE_DIR', ROOT_DIR.'/cache');

	// URL панели администрирования
    define('ADMIN_URL', '/mpanel');

    /*
    Данный префикс используется при хешировании пароля.
    Этот параметр стоит поменять только один раз, перед началом создания сайта.
    Изменение префикса пароля на уже работающем сайте приведет к невозможности
    использовать созданные до этого учетные записи.
    */
    define('PASS_PREFIX', 'TBjXLky8UE5qFdqvHf5T');

    // Параметры подключения к БД
	require(ROOT_DIR.'/config-db.php');

    // Общие настройки кэша
	require(ROOT_DIR.'/config-cache.php');


    require(MODUL_DIR.'/core/system.php');


    // Добавляем основные классы в список автозагрузки

    // Базовые классы системы
    system::addClass('page', MODUL_DIR.'/core/page.php');
    system::addClass('db', MODUL_DIR.'/core/db.php');
    system::addClass('reg', MODUL_DIR.'/core/reg.php');
    system::addClass('user', MODUL_DIR.'/core/user.php');
    system::addClass('lang', MODUL_DIR.'/core/lang.php');
    system::addClass('ruNumbers', MODUL_DIR.'/core/ruNumbers.php');
    system::addClass('innerErrorList', MODUL_DIR.'/core/innerErrorList.php');
    system::addClass('elFinder', MODUL_DIR.'/core/elFinder.php');

    // Авторизация через соц. сети
    system::addClass('TwitterOAuth', MODUL_DIR.'/users/social/twitter/twitteroauth/twitteroauth.php');
    system::addClass('LightOpenID', MODUL_DIR.'/users/social/openid.php');

    // Подсистема кэширования
    system::addClass('defCache', MODUL_DIR.'/core/cache/defCache.php');
    system::addClass('fileCache', MODUL_DIR.'/core/cache/fileCache.php');
    system::addClass('mcCache', MODUL_DIR.'/core/cache/mcCache.php');
    system::addClass('cache', MODUL_DIR.'/core/cache/cache.php');

    // Работа со структурой сайта
    system::addClass('domain', MODUL_DIR.'/structure/domain.php');
    system::addClass('domains', MODUL_DIR.'/structure/domainsCollection.php');
    system::addClass('language', MODUL_DIR.'/structure/language.php');
    system::addClass('languages', MODUL_DIR.'/structure/languagesCollection.php');
    system::addClass('template', MODUL_DIR.'/structure/template.php');
    system::addClass('templates', MODUL_DIR.'/structure/templatesCollection.php');

    // Работа с интерфейсом. Вспомогательные классы для панели администрирования
    system::addClass('ui', MODUL_DIR.'/mpanel/ui.php');
    system::addClass('uiTable', MODUL_DIR.'/mpanel/uiTable.php');
    system::addClass('uiMultiForm', MODUL_DIR.'/mpanel/uiMultiForm.php');
    system::addClass('uiTableFunctions', MODUL_DIR.'/mpanel/uiTableFunctions.php');

    system::addClass('ormTree', MODUL_DIR.'/mpanel/ormTree.php');
    system::addClass('ormEditForm', MODUL_DIR.'/mpanel/ormEditForm.php');
    system::addClass('ormMultiForm', MODUL_DIR.'/mpanel/ormMultiForm.php');
    system::addClass('ormFilterForm', MODUL_DIR.'/mpanel/ormFilterForm.php');
    system::addClass('ormFieldsTree', MODUL_DIR.'/mpanel/ormFieldsTree.php');

    // Классы для работы с объектами и классами данных (подсистема ORM)
    system::addClass('ormClasses', MODUL_DIR.'/constructor/ormClassesCollection.php');
    system::addClass('ormClass', MODUL_DIR.'/constructor/ormClass.php');
    system::addClass('ormFieldsGroup', MODUL_DIR.'/constructor/ormFieldsGroup.php');
    system::addClass('ormField', MODUL_DIR.'/constructor/ormField.php');
    system::addClass('ormObjects', MODUL_DIR.'/constructor/ormObjectsCollection.php');
    system::addClass('ormObject', MODUL_DIR.'/constructor/ormObject.php');
    system::addClass('ormPages', MODUL_DIR.'/constructor/ormPagesCollection.php');
    system::addClass('ormPage', MODUL_DIR.'/constructor/ormPage.php');
    system::addClass('ormSelect', MODUL_DIR.'/constructor/ormSelect.php');

    // Работа с правами доступа для модулей и страниц
    system::addClass('rights', MODUL_DIR.'/users/rights.php');

    // Модуль "Поиск"
    system::addClass('searchIndex', MODUL_DIR.'/search/searchIndex.php');
    system::addClass('searchRanking', MODUL_DIR.'/search/searchRanking.php');
    system::addClass('tags', MODUL_DIR.'/search/tags.php');


    // Модуль "Рассылка"
    system::addClass('mailingProcess', MODUL_DIR.'/subscription/mailingProcess.php');

    // Модуль "Комментарии"
    system::addClass('comment', MODUL_DIR.'/comments/comment.php');
    system::addClass('comments', MODUL_DIR.'/comments/commentsCollection.php');

    // Модуль "Интернет-магазин"
    system::addClass('basket', MODUL_DIR.'/eshop/basket.php');
    system::addClass('eShopOrder', MODUL_DIR.'/eshop/eShopOrder.php');

    // Разное
    system::addClass('phpmailer', MODUL_DIR.'/core/phpmailer/class.phpmailer.php');
    system::addClass('SMTP', MODUL_DIR.'/core/phpmailer/class.smtp.php');
    system::addClass('resizer', MODUL_DIR.'/core/resizer.php');
    system::addClass('Jevix', MODUL_DIR.'/core/jevix/jevix.class.php');






    // Запускаем систему
 	system::start();

?>