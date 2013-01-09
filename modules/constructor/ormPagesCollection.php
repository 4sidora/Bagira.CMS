<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Статический класс коллекция для работы с ORM-страницами.
*/

class ormPages {

    private static $pages = array();            // Массив с данными страниц
    private static $pages_obj = array();        // КЕШ созданных объектов страниц
    private static $pages_rel = array();        // Связи объектов, для быстрого поиска родителей объекта
    private static $pages_rel2 = array();       // Связи объектов, для быстрого перебора подразделов
    private static $pages_url = array();        // Для быстрого поиска объекта по псевдоадресу
    private static $pages_count = array();      // КЕШ для количества страниц по разделам
    private static $cycle = array();        	// Для хранения текущей страницы при переборе

    private static $issetList = array();     //

    private static $pages_active = array();     // Список номеров активных страниц

    private static $homepage_id;
    private static $all_count;

    private static $where = '';

    static function getSqlForRights() {

    	if (empty(self::$where)) {

	    	$state = (system::$isAdmin && reg::getKey(self::getPrefix().'/no_view_no_edit')) ? '= 2' : '> 0';

	     	$groups = '';
	      	$m = user::getGroups();
	       	while(list($key, $val) = each($m))
	        	$groups .= ' or r_group_id = "'.$key.'"';

	        self::$where = ' and r_obj_id = o_id and r_state '.$state.' and
	        		  (r_group_id is NULL or r_group_id = "'.user::get('id').'"'.$groups.') GROUP BY o_id';
        }

        return self::$where;
    }

	// Инициализация работы с классом
	static function init($openPages = array()){

   		if (empty(self::$pages)) {

            $act = (system::$isAdmin) ? '' : ' and active = 1';

            if (true) {
                $where = self::getSqlForRights();
                $table = ', <<rights>>';
                $select = ', MAX(r_state) r_state';
            } else $where = $table = $select = '';

            // Добавляем фильтр по классам (получаем данные только для классов образующих структуру сайта)
            $in_menu = reg::getList(ormPages::getPrefix().'/no_view');
            $cfilter = '';
            while(list($num, $val) = each($in_menu)){
            	$or = (empty($cfilter)) ? '' : ' or ';
            	$cfilter .= $or.' o_class_id = "'.$val.'" ';
            }
            if (!empty($cfilter)) $cfilter = ' and ('.$cfilter.')';

            if (system::$isAdmin)
            	$select = 'o_id, o_name, o_class_id, o_create_date, o_change_date, pseudo_url, other_link, view_in_menu, active, is_home_page, template_id, template2_id, lang_id, domain_id'.$select;
            else
                $select = ' * '.$select;

			// Получаем список страниц
   			$sql = 'SELECT '.$select.'
        	    	FROM <<objects>>, <<pages>>'.$table.'
        			WHERE lang_id = "'.languages::curId().'" and
        				  domain_id = "'.domains::curId().'" and
        				  o_to_trash = 0 and
        				  p_obj_id = o_id'.$cfilter.$act.$where.'';

        	if (system::$isAdmin) {

        	    if (!empty($openPages)) {

	                $open_pages = '';
		            while(list($page_id, $val) = each($openPages))
		            	$open_pages .= ' or r_parent_id = "'.$page_id.'" ';

	             	$open_pages = ' and (r_parent_id is NULL '.$open_pages.')';

	            } else $open_pages = ' and r_parent_id is NULL';

        		$sql = '('.$sql.') UNION (
        			SELECT '.$select.'
        	    	FROM <<objects>>, <<pages>>, <<rels>>'.$table.'
        			WHERE lang_id = "'.languages::curId().'" and
        				  domain_id = "'.domains::curId().'" and
        				  r_children_id = o_id '.$open_pages.' and
        				  r_field_id is NULL and
        				  o_to_trash = 0 and
        				  p_obj_id = o_id'.$act.$where.'
        		)';
        	}

        	$tmp = db::q($sql, records);

        	while (list($key, $value) = each($tmp)) {
        		self::$pages[$value['o_id']] = $value;
        		self::$pages_url[$value['pseudo_url']][] = $value['o_id'];

        		if (self::$pages[$value['o_id']]['is_home_page'])
        			self::$homepage_id = $value['o_id'];
        	}

            // Получаем связи между страницами
        	$sql = 'SELECT r_parent_id, r_children_id, r_position
        	    	FROM <<objects>>, <<pages>>, <<rels>>'.$table.'
        			WHERE lang_id = "'.languages::curId().'" and
        				  domain_id = "'.domains::curId().'" and
        				  o_to_trash = 0 and
        				  p_obj_id = o_id and
        				  r_children_id = o_id and
        				  r_field_id is NULL'.$cfilter.$act.$where.'
        			ORDER BY r_position ASC';


        	if (system::$isAdmin) {
        		$sql = '('.$sql.') UNION (
        			SELECT child.r_parent_id, child.r_children_id, child.r_position
        	    	FROM <<objects>>, <<pages>>, <<rels>> child'.$table.'
        			WHERE lang_id = "'.languages::curId().'" and
        				  domain_id = "'.domains::curId().'" and
        				  o_to_trash = 0 and
        				  p_obj_id = o_id and
        				  r_children_id = o_id '.$open_pages.' and
        				  r_field_id is NULL'.$act.$where.'
        			ORDER BY child.r_position ASC
        		)
        		ORDER BY r_position ASC
        		';
        	}

        	$tmp = db::q(str_replace('GROUP BY o_id', '', $sql), records);

        	while (list($key, $value) = each($tmp)){
        		if (empty($value['r_parent_id'])) $value['r_parent_id'] = 0;
        		self::$pages_rel[$value['r_children_id']] = $value['r_parent_id'];
        		self::$pages_rel2[$value['r_parent_id']][] = $value['r_children_id'];
        	}

        	//print_r(self::$pages_rel2);
        	//getPageOfSection
   		}
	}

	// Вернет ID родителя для указанной страницы
	private static function getParent($page_id){
    	if (isset(self::$pages_rel[$page_id]))
    		return self::$pages_rel[$page_id];
    	else
    		return 0;
	}

    // -	-	-	-	Методы для перебора элементов иерархии 	-	-	-	-	-


    /*
    	Получение списка страниц у которых есть наследники. Используется только в режиме
    	администрирования для построения дерева объектов.
    */
    static function initIssetList(){
    	if (empty(self::$issetList)) {

         	$sql = 'SELECT obj.r_parent_id id
					FROM <<pages>>,
						(
						 	SELECT obj.r_parent_id, obj.o_id
							FROM <<rights>>,
							 	(
								 	SELECT r_parent_id, o_id
								 	FROM <<objects>>, <<rels>>, <<classes>>
								 	WHERE r_field_id IS NULL
										AND r_children_id = o_id
										AND o_to_trash = 0 and
										  c_id = o_class_id and
										  c_is_page = 1
									GROUP BY r_parent_id
							 	) obj
							WHERE o_id = o_id '.str_replace('GROUP BY o_id', '', self::getSqlForRights()).'
							GROUP BY obj.r_parent_id
					 	) obj
					WHERE lang_id = "'.languages::curId().'"
        				AND domain_id = "'.domains::curId().'"
						AND p_obj_id = obj.o_id
					GROUP BY obj.r_parent_id;';


        	$tmp = db::q($sql, records);
            self::$issetList = array();
        	while (list($key, $val) = each($tmp))
        		self::$issetList[] = (empty($val['id'])) ? 0 : $val['id'];
    	}
    }

    // Вернет true, если у раздела есть подразделы
    static function issetChildren($section_id){

    	//if (system::$isAdmin) {
	    	self::initIssetList();
	    	return (in_array($section_id, self::$issetList)) ? true : false;
	 /* 	} else {
	  		self::init();
	    	return (isset(self::$pages_rel2[$section_id])) ? true : false;
	  	}  */
    }

    // Скидывает счетчик перебора страниц для метода getPageOfSection()
    static function reset(){
    	self::$cycle = array();
    }

    // Скидывает счетчик перебора страниц для указанного раздела
    static function resetForSection($section_id){
    	self::$cycle[$section_id] = 0;
    }

    // Вернет количество страниц в указанном разделе
    static function getCountOfSection($section_id){

    	if (!isset(self::$pages_count[$section_id])) {

            $act = (system::$isAdmin) ? '' : ' and active = 1';

            $act .= (empty($section_id)) ? ' and r_parent_id is NULL' : ' and r_parent_id = "'.$section_id.'"';

            $sql = 'SELECT o_id
        	    	FROM <<objects>>, <<pages>>, <<rels>>, <<rights>>
        			WHERE lang_id = "'.languages::curId().'" and
        				  domain_id = "'.domains::curId().'" and
        				  o_to_trash = 0 and
        				  o_id = p_obj_id and
        				  o_id = r_children_id and
        				  r_field_id is NULL'.$act.self::getSqlForRights().'';

        	self::$pages_count[$section_id] = count(db::q($sql, records));
    	}

    	if (isset(self::$pages_count[$section_id]))
    		return self::$pages_count[$section_id];
    	else
    		return 0;
    }

    /*
    	Вернет количество активных страниц в указанном разделе, которые резрешенно отображать в меню.
    	Используется системой в макросе %structure.menu%.
    */
    /*
    	Внимание! Методом учитываются только те страницы, которые образуют структуру сайта.
    	По умолчанию, это страницы имеющие классы: section, page, category.
    */
    static function getViewOfSection($section_id){
    	self::init();
    	if (isset(self::$pages_rel2[$section_id])) {

            $c = 0;
            reset(self::$pages_rel2[$section_id]);
            while(list($key, $val) = each(self::$pages_rel2[$section_id])) {
            	if (!empty(self::$pages[$val]['view_in_menu']))
            		$c++;
            }

    		return $c;
    	} else
    		return 0;
    }

	/**
	* @return ormPage
	* @param integer $section_id - ID раздела, подразделы которого нужно перебрать.
	* @param array $only_this - Фильтр по классам страниц. Если не указано, не учитывается.
	* @desc Вернет следующий по списку подраздел. Используется для перебора страниц в цикле.
	*/
	/*
    	Внимание! Методом учитываются только те страницы, которые образуют структуру сайта.
    	По умолчанию, это страницы имеющие классы: section, page, category.
    */
	static function getPageOfSection($section_id, $only_this = array()){

    	self::init();

        if (isset(self::$pages_rel2[$section_id])) {

        	if (!isset(self::$cycle[$section_id]))
        		self::$cycle[$section_id] = 0;

            if (isset(self::$pages_rel2[$section_id][self::$cycle[$section_id]])) {

	        	$page_id = self::$pages_rel2[$section_id][self::$cycle[$section_id]];

	            self::$cycle[$section_id] ++;

                $cl = ormClasses::get(self::$pages[$page_id]['o_class_id']);

	            if (empty($only_this) || in_array($cl->id(), $only_this) || isset($only_this[$cl->getSName()])) {
	            	return self::get($page_id);
                }else
                    return self::getPageOfSection($section_id, $only_this);

            } else return false;
        }

    	return false;
	}

	// Проверяем загружался ли ранее, указанный объект страницы
	static function issetPage($page_id){
		self::init();
		return (bool) (is_numeric($page_id) && array_key_exists($page_id, self::$pages));
	}


	// Возвращает страницу как объект ormPage по указанному ID
	static function get($page_id, $filter_class = ''){

        self::init();
       	if (is_numeric($page_id) && !empty($page_id) && !is_array($page_id)) {

       	    $page_id = system::checkVar($page_id, isInt);

            if (!isset(self::$pages_obj[$page_id])) {

            	if (self::issetPage($page_id))
		    		self::$pages_obj[$page_id] = new ormPage(self::$pages[$page_id]);
				else
					self::$pages_obj[$page_id] = new ormPage($page_id);
			}

			$obj = self::$pages_obj[$page_id];

            if (isset($obj) && ($obj instanceof ormPage) && !$obj->issetErrors() && $obj->id == $page_id)
            	if (empty($filter_class) || $obj->isInheritor($filter_class))
            		return $obj;

	    } else if (is_array($page_id) && isset($page_id['o_id'])) {

     		if (!isset(self::$pages_obj[$page_id['o_id']])) {

                $obj = new ormPage($page_id);

	        	self::$pages_obj[$obj->id] = $obj;

	        	if (!isset(self::$pages[$obj->id])) {
	        		self::$pages[$obj->id] = $page_id;
		        	if (isset($page_id['r_parent_id'])) {
		        		self::$pages_rel[$page_id['o_id']] = $page_id['r_parent_id'];
	        			self::$pages_rel2[$page_id['r_parent_id']][] = $page_id['o_id'];
		        	}
	        	}

            } else {
            	$obj = self::$pages_obj[$page_id['o_id']];
            	$obj->supplementData($page_id);
            }

            if (empty($filter_class) || $obj->isInheritor($filter_class))
        		return $obj;
        }

		return false;
	}

	// Вернет все страницы для текущей языковой версии и домена
	static function getAll(){
    	self::init();
    	return self::$pages;
	}

    // Вернет экземпляр страницы которая является домашней
	static function getHomePage(){
		self::init();

        if (empty(self::$pages_active))
        	self::$pages_active[] = self::$homepage_id;

    	return self::get(self::$homepage_id);
	}

	/**
	* @return string URL страницы
	* @param integer $page_id - ID страницы
	* @param boolean $recursi - Используется для организации рекурсии
	* @desc Вернет URL страницы по указанному ID
	*/
	// ПРОВЕРИТЬ НА УРОВНЕ МАКРОСА!!!
	static function getPageUrlById($page_id, $recursi = false){

    	if ($page = ormPages::get($page_id)) {

            if ($lang = languages::get($page->lang_id)) {

                if (!$recursi && $page->is_home_page)
            	    return $lang->pre().'/';

                $url = (!$recursi) ? $lang->pre() : '';
            } else
                $url = '';

	        if (isset(self::$pages_rel[$page_id])) {

	            $url .= self::getPageUrlById(self::$pages_rel[$page_id], true);

                if (isset($pages[$page_id]['pseudo_url']))
	                return $url.'/'.self::$pages[$page_id]['pseudo_url'];
                else
                    return $url.'/'.self::get($page_id)->pseudo_url;

	        } else {

	        	if ($page->issetParents())
	        		$url .= self::getPageUrlById($page->getParentId(), true);

	       		return $url.'/'.$page->pseudo_url;
	        }
    	}
	}

	// Вернет ID страницы по указанному URL`y
	static function getPageIdByUrl($page_url){

    	self::init();

        $parent_id = 0;
    	$url = explode('/', $page_url);
    	$pages_active = array();

    	while (list($key, $val) = each($url))

    		if (!empty($val)) {

	    		if (isset(self::$pages_url[$val])){

                    // Ищем по элементам структуры сайта
	         	    while (list($num, $obj_id) = each(self::$pages_url[$val])) {

	                    if ($parent_id == self::getParent($obj_id)) {
	                    	$parent_id = $obj_id;
	                    	$pages_active[] = $obj_id;
	                    	break;
	                    }
	         	    }

	    		} else {

                    // Проверяем есть ли контентная страница
                    $sql = 'SELECT *
                    		FROM <<objects>>, <<pages>>, <<rels>>
                    		WHERE pseudo_url = "'.$val.'" and
                    			r_parent_id = "'.$parent_id.'" and
                    			o_id = r_children_id and
                    			o_id = p_obj_id and
                    			o_to_trash = 0
                    		LIMIT 1;';

                    $mas = db::q($sql, record);

                    if (!empty($mas)) {
                        // Если существует, загужаем ее данные в коллекцию
                    	$parent_id = $mas['o_id'];
                    	$pages_active[] = $parent_id;
                    	self::get($mas);
                    } else $parent_id = 0;

	    		}
    		}

    	if (empty(self::$pages_active))
        	self::$pages_active = $pages_active;

    	return $parent_id;
	}

    // Вернет список всех активных страниц
    static function getActiveId(){
    	return self::$pages_active;
    }

    static function setActivePath(){
    	self::$pages_active = func_get_args();

        // Сразу парсим новые ID
        while (list($num, $id) = each(self::$pages_active))
            page::assign('page_id'.$num, $id);

        reset(self::$pages_active);
    }

    static function getCurPageId(){

		$page_num = count(self::$pages_active) - 1;

        if ($page_num >= 0)
		    return self::$pages_active[$page_num];
        else
            return 0;
	}

    /**
	* @return ormPage
	* @param integer $page_id - ID страницы, относительно которой вычисляется следующая
	* @desc Вернет экземпляр страницы следующей за указанной, с учетом правил
			сортировки и прав доступа текущего пользователя.
	*/
	static function getNext($page_id = 0) {
   		return self::getPrevNext($page_id, 1);
	}

 	/**
	* @return ormPage
	* @param integer $page_id - ID страницы, относительно которой вычисляется предыдущая
	* @desc Вернет экземпляр страницы следующей перед указанной, с учетом правил
			сортировки и прав доступа текущего пользователя.
	*/
	static function getPrevious($page_id = 0) {
   		return self::getPrevNext($page_id, -1);
	}

 	// Вычисление следующей и предыдущей страниц
	private static function getPrevNext($page_id = 0, $vektor = 1) {

   		if (empty($page_id))
   			$page_id = self::getCurPageId();

   		if ($page = self::get($page_id)) {

            // Определяем способ сортировки заданный для текущего списка
            $parram = 'asc';
        	$order_by = 'position';
        	if ($page->issetParents()) {

        		$section_id = $page->getParent()->id;
            	$order_by_sql = $page->getParent()->order_by;

            	if (!empty($order_by_sql)) {
		            $pos = strpos($order_by_sql, ' ');
		            if ($pos) {
		            	$parram = substr($order_by_sql, $pos + 1);
		            	$order_by = substr($order_by_sql, 0, $pos);
		            }
		        }

        	} else $section_id = 0;

            // Определяем как сортировать выборку и какой знак сравнения использовать
            if ($vektor < 0)
                // Если, ищем предыдущий элемент списка
                if ($parram == 'desc') {
                	$znak = '>';
                	$parram = 'asc';
                } else {
                    $znak = '<';
                	$parram = 'desc';
                }
            else
            	// Если, ищем следующий элемент списка
            	$znak = ($parram == 'desc') ? '<' : '>';

            // Формируем выборку, для поиска объекта
            $sel = new ormSelect();
            $sel->fields('id');
            $sel->findInPages();
            $sel->where('parents', '=', $section_id);
            $sel->where($order_by, $znak, $page->getPosition());
            $sel->orderBy($order_by, $parram);
            $sel->limit(1);

            return $sel->getObject();
   		}
	}

    /**
	* @return boolean Вернет TRUE, если все страницы были удалены, FALSE, если хотя бы одна не удалена
	* @param integer $domain_id - ID домена
	* @param integer $lang_id - ID языковой версии
	* @desc Удаляет все страницы с указанного домена и указанной языковой версии
	*/
	static function delAllFor($domain_id, $lang_id){

	    if ($domain_id != 1 || $lang_id != 1) {

            $ret = true;
                /*
            $sql = 'SELECT *, MAX(r_state) r_state
        	    	FROM <<objects>>, <<classes>>, <<pages>>, <<rels>>, <<rights>>
        			WHERE lang_id = "'.$lang_id.'" and
        				  domain_id = "'.$domain_id.'" and
        				  o_class_id = c_id and
        				  p_obj_id = o_id and
        				  o_id = r_children_id and
        				  r_field_id is NULL and
        				  r_parent_id is NULL
        				  '.self::getSqlForRights().';';

        	$objs = db::q($sql, records);

        	while(list($key, $obj_data) = each($objs)) {
            	$obj = new ormPage($obj_data);
            	$tmp = $obj->delete();
                if (!$tmp) $ret = $tmp;
        	}
        	     */
        	$sql = 'DELETE FROM <<objects>> WHERE o_id IN (
	        			SELECT p_obj_id
	        			FROM <<pages>>
	        			WHERE lang_id = "'.$lang_id.'" and
	        				  domain_id = "'.$domain_id.'"
	        		);';

        	db::q($sql);


        	return $ret;

	    } else return false;
	}

    // Вернет контент для страницы "Страница не найдена"
    static function get404(){
        page::disableCacheForThisPage();
		page::globalVar('h1', lang::get('ERROR_404_TITLE'));
        page::globalVar('title', lang::get('ERROR_404_TITLE'));

        if (!($data = cache::get('error404'))) {
            $data = lang::get('ERROR_404_TEXT').page::macros('structure')->menu('map');
            cache::set('error404', $data);
        }
 
		return $data;
	}

    // Возвращает ключ настроек для любого модуля в зависимости от текущего домена и языка
	static function getPrefix($modul = 'structure'){
		return '/'.$modul.'/'.domains::curId().'/'.languages::curId();
	}


	// Вернет системное имя популярного (часто использующегося) ORM-класса для подразделов указанного раздела.
	static function getPopularClass($section_id){

        if ($page = self::get($section_id)) {

            if ($page->issetChildren()) {

				$max_class = array();
		       	while($obj = $page->getChild()){
		        	$class = $obj->getClass()->getSName();
		        	if (isset($max_class[$class]))
		        		$max_class[$class] ++;
		        	else
		        		$max_class[$class] = 1;
		       	}

		        $max_count = 0;
		        $class_name = 'page';
		        while(list($class, $count) = each($max_class)){
		        	if ($count > $max_count) {
		        		$max_count = $count;
		        		$class_name = $class;
		        	}
		        }

	        	return $class_name;

			} else {

			    $class = $page->getClass();
			    if ($class->getBaseClass() != 0)
			    	return ormClasses::get($class->getBaseClass())->getSName();
				else
					return $class->getSName();
			}

		} else return 'page';
	}

	// Генерируем файл Robots.txt
	/* ВОЗМОЖНО НУЖНО ПЕРЕПИСАТЬ!!! */
    static function getFileRobots(){

    	self::init();

        $file = ROOT_DIR.'/robots_part_'.domains::curDomain()->getName().'.txt';
        $addit_text = (file_exists($file)) ? file_get_contents($file) : '';

        $text = 'User-Agent: *'.Chr(13);
        if (!empty($addit_text))
        	$text .= $addit_text.Chr(13);

        reset(self::$pages);
        foreach (self::$pages as $page)
            if (!$page['in_index'])
            	$text .= 'Disallow: '.self::getPageUrlById($page['o_id']).'/'.Chr(13);

		$text .= 'Disallow: /booking'.Chr(13);
		$text .= 'Allow: /booking/schedule'.Chr(13);

        $text .= 'Host: www.'.domains::curDomain()->getName().Chr(13);
        $text .= 'Sitemap: http://'.domains::curDomain()->getName().'/sitemap.xml'.Chr(13);

        return $text;
	}

    // Вывод на экран файла robots.txt
    static function getContentFileRobots() {

        // Генерируем файл robots.txt
        if (system::url(0) == 'robots.txt') {

            if (!($content = cache::get('robots.txt'))) {

                $content = ormPages::getFileRobots();

                // Записываем в кэш
                cache::set('robots.txt', $content);
            }

        	header('Content-type: text/plain');
        	echo $content;
        	system::stop();
        }
    }

    // Генерируем файл SiteMap.xml
    /* ВОЗМОЖНО НУЖНО ПЕРЕПИСАТЬ!!! */
	static function getFileSiteMap(){

    	self::init();
        $cur_date = reg::getKey('/structure/'.domains::curId().'/cur_date');
        $text = '';
        reset(self::$pages);
        foreach (self::$pages as $page)
            if ($page['active'] && $page['in_index'] && empty($page['other_link'])) {

                $date = date('Y-m-d', strtotime($page['o_change_date']));
                if ($cur_date) {
                	$left = (time() - strtotime($page['o_change_date'])) / 86400;
                    if ($left > 30) $date = date('Y-m-d');
                }

                $url = self::getPageUrlById($page['o_id']);
                $priority = ($page['is_home_page']) ? 1 : (1 - ((count(explode('/', $url)) - 1 ) * 0.1));

                $tmp = '';
            	$tmp .= '<loc>http://'.domains::curDomain()->getName().$url.'</loc>';
            	$tmp .= '<lastmod>'.$date.'</lastmod>';
            	$tmp .= '<priority>'.$priority.'</priority>';
            	$text .= '<url>'.$tmp.'</url>';
            }

        return '<?xml version="1.0" encoding="UTF-8"?> <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'.$text.'</urlset>';
	}

    // Вывод на экран файла sitemap.xml
    static function getContentFileSiteMap() {

        if (system::url(0) == 'sitemap.xml') {

            if (!($content = cache::get('sitemap.xml'))) {

                $content = ormPages::getFileSiteMap();

                // Записываем в кэш
                cache::set('sitemap.xml', $content);
            }

        	header('Content-type: text/xml; charset=UTF-8');
        	echo $content;
        	system::stop();
        }
    }


    public static function getAllSubSections($section_id, $sections = array()) {

        self::resetForSection($section_id);
        while ($obj = self::getPageOfSection($section_id)) {
            if (self::issetChildren($obj->id)) {
                $sections = self::getAllSubSections($obj->id, $sections);
                $sections[] = $obj->id;
            }
        }
        return $sections;
    }

    // Определяем раздел и класс выводимых элементов в зависимости от пути
    public static function getSectionByPath($path){

        $section = '';

        if (is_numeric($path)) {

            if ($path == 0) {
                $section = 'root';
                $class_name = '';
            } else {
                $section = ormPages::get($path);
                $class_name = '';
            }

        } else {

            if (strpos($path, '..')) {

                // Выборка всех подразделов раздела, например "454 .. pages"

                $str = str_replace(' ', '', $path);

                // Определяем класс
                $section_id = 0;
                $pos = strpos($str, '..');
                if ($pos) {
                    $class_name = substr($str, $pos + 2);
                    $section_id = substr($str, 0, $pos);
                } else $class_name = '';

                $section = self::getAllSubSections($section_id);

            } else if (strpos($path, '>')) {

                // Обычная цепочка, вида "455 > #5 > category goods"

                $str = str_replace(array('> ', ' >'), '>', $path);

                // Определяем класс
                $pos = strpos($str, ' ');
                if ($pos) {
                    $class_name = substr($str, $pos + 1);
                    $str = substr($str, 0, $pos);
                } else $class_name = '';

                // Получаем путь
                $list = array();
                $mas = explode('>', $str);
                while(list($key, $val) = each($mas)) {
                    $val = trim($val);
                    if (!empty($val))
                        $list[] = $val;
                }

                // Определяем объект
                $parent_id = 0;
                reset($list);
                while(list($key, $val) = each($list)) {

                    if (is_numeric($val)) {

                        // Указан ID
                        if (empty($key))
                            $parent_id = $val;

                    } else if ($val[0] == '#') {

                        // Указан порядковый номер в списке
                        $num = str_replace('#', '', $val) - 1;
                        $sel = new ormSelect();
                        $sel->findInPages();
                        $sel->fields('id');
                        $sel->where('active', '=', 1);

                        if (!empty($parent_id))
                            $sel->where('parents', '=', $parent_id);
                        else
                            $sel->where('parents', '=', 0);

                        $sel->limit($num, 1);
                        $sel->orderBy(position, asc);

                        if ($section = $sel->getObject()) {
                            $parent_id = $section->id;
                        } else {
                            $section = false;
                            break;
                        }

                    } else {

                        // Указан класс
                        $sel = new ormSelect($val);
                        $sel->findInPages();
                        $sel->fields('id');
                        $sel->where('active', '=', 1);

                        if (!empty($parent_id))
                            $sel->where('parents', '=', $parent_id);
                        else
                            $sel->where('parents', '=', 0);

                        $sel->limit(1);
                        $sel->orderBy(position, asc);


                        if ($section = $sel->getObject()) {
                            $parent_id = $section->id;
                        } else {
                            $section = false;
                            $section;
                        };
                    }
                }

            } else {

                $pos = strpos($path, ' ');
                if ($pos) {

                    $obj_id = substr($path, 0, $pos);

                    if (is_numeric($obj_id)) {

                        $class_name = substr($path, $pos + 1);

                        if ($obj_id == 0)
                            $section = 'root';
                        else
                            $section = ormPages::get($obj_id);

                    } else
                        $class_name = $path;                    

                } else $class_name = $path;
            }
        }

        return array('section' => $section, 'class' => $class_name);
    }

    static function clearCache() {
        if (CACHE_DEFAULT_TTL === 0)
            cache::flush();
    }



}

?>