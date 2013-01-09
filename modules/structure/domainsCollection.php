<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Статический класс коллекция для работы с сайтами (доменами) системы.
	Позволяет:
		-	Быстро получить информацию о сайтах системы
		-	Определить текущий сайт системы
*/

class domains {

    private static $domains = array();
    private static $domains_obj = array();
    private static $domains_rel = array();
    private static $cur_domain;
    private static $cur_site_domain;

    private static $isAdmin = false;

    // Иницилизация работы класса
    private static function init(){

        if (!self::$isAdmin && system::$isAdmin){

            // Определяем домен для панели управления

            // Определяем домен по УРЛу
            if (system::issetUrl(0)) {
	        	$tmp_domain = self::get(str_replace('_', '.', system::url(0)));
                if ($tmp_domain instanceof domain) {
					self::$cur_domain = $tmp_domain;
					system::clipUrl();
					languages::setCurLang(self::$cur_domain->getDefLang());
				}
            }

            // Проверяем, имеет ли пользователь доступ к данному домену
	        if (!user::issetRight(languages::curId().' '.self::$cur_domain->id(), 'structure')) {
				//echo languages::curId().' '.self::$cur_domain->id();
				$langs = languages::getAll();
	            $domains = domains::getAll();
	            if (count($langs) > 1 || count($domains) > 1)
		            while (list($num2, $domain) = each ($domains)) {
		                reset($langs);
		            	while (list($num1, $lang) = each ($langs))
		            	    if (user::issetRight($lang['l_id'].' '.$domain['d_id'], 'structure')) {

                                $link = ADMIN_URL;

		            	        if ($domain['d_id'] != 1)
		            	        	$link .= '/'.str_replace('.', '_', $domain['d_name']);

		            	        if ($lang['l_id'] != 1)
			                		$link = '/'.$lang['l_prefix'].$link;

		                        system::redirect($link, true);
		                    }
		            }
	        }

			self::$isAdmin = true;

        } else if (empty(self::$cur_domain)){

            // Опеделяем домен для сайта
            $domain_id = reg::getKey('/core/cur_domain/id');

            if (!empty($domain_id)) {

                // Если домен в системе один, загружаем его данные из реестра
                $domain_name = reg::getKey('/core/cur_domain/name');

            	self::$domains[$domain_id] = array(
            		'd_id' => $domain_id,
            		'd_name' => $domain_name,
            		'd_domain_id' => '',
            		'd_def_lang' => reg::getKey('/core/cur_domain/def_lang'),
            		'd_sitename' => reg::getKey('/core/cur_domain/sitename'),
            		'd_email' => reg::getKey('/core/cur_domain/email'),
            		'd_online' => reg::getKey('/core/cur_domain/online'),
            		'd_offline_msg' => reg::getKey('/core/cur_domain/offline_msg'),
            		'd_error_msg' => reg::getKey('/core/cur_domain/error_msg'),
            		'id' => $domain_id
            	);

				self::$domains_rel[$domain_name] = $domain_id;

	        } else {

                $serv_name = $_SERVER['HTTP_HOST'];
                if (substr($serv_name, 0, 4) == 'www.')
                	$serv_name = substr($serv_name, 4, strlen($serv_name) - 4);

				self::$cur_domain = self::get($serv_name);

            }

            if (!(self::$cur_domain instanceof domain))
            	self::$cur_domain = self::get(1);

			if (!(self::$cur_domain instanceof domain))
				die('не могу определить домен');

			self::$cur_site_domain = self::$cur_domain;
		}
	}

    /**
	* @return array
	* @param boolean $prinud - Если true, данные будут взяты не из кэша, а из БД.
	* @desc Вернет список всех доменов системы
	*/
	static function getAll($prinud = false){

		if (empty(self::$domains) || $prinud) {
			$mas = db::q('SELECT *, d_id id FROM <<domains>>;', records);
            if (db::issetError()) die;
			self::$domains = array();
			while(list($key, $domain) = each($mas)) {

			    if (empty($domain['d_domain_id']))
					self::$domains[$domain['d_id']] = $domain;

				if (empty($domain['d_domain_id']))
					self::$domains_rel[$domain['d_name']] = $domain['d_id'];
				else
					self::$domains_rel[$domain['d_name']] = $domain['d_domain_id'];
			}
		}

		return self::$domains;
	}


    /**
	* @return object
	* @param string $val - ID или имя домена
	* @param boolean $prinud - Если true, объект будет пересоздан,
								вне зависимости, есть ли он в кеше или нет.
	* @desc Вернет указанный домен, как объект класса domain
	*/
	static function get($val, $prinud = false){

		self::getAll();

		if (!is_numeric($val) && isset(self::$domains_rel[$val]))
        	$val = self::$domains_rel[$val];

        if (isset(self::$domains[$val])) {

		    if (!isset(self::$domains_obj[$val]) || $prinud)
             	self::$domains_obj[$val] = new domain(self::$domains[$val]);

			return self::$domains_obj[$val];
		}
	}

    /*
    Вернет текущий домен (экземпляр класса domain).
    Нужно учитывать, что для режима администрирования, текущим считается домен
    выбранный админом, а не тот, что написан адресной строке.
    */
	static function curDomain(){
		self::init();
		return self::$cur_domain;
	}

	// Вернет ID текущего сайта (домена)
	static function curId(){
		self::init();
		return self::$cur_domain->id();
	}

    // Вернет префикс для формирования ссылок, исходя из текущего домена
	static function pre(){
		self::init();
		if (self::$cur_domain->id() != self::$cur_site_domain->id())
			return '/'.str_replace('.', '_', self::$cur_domain->getName());
		else
			return '';
	}

    /*
    Вернет "настоящий" текущий домен
    Вне зависимости от того, в каком режиме находится система, этот метод вернет текущий домен.
    */
	static function curSiteDomain(){
		self::init();
		return self::$cur_site_domain;
	}

}

?>