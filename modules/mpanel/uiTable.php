<?php

define('table', 'table');
define('list', 'list');
define('gallery', 'gallery');

define('single', 'single');
define('multi', 'multi');
define('only_multi', 'only_multi');

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс позволяет создавать таблицы вывода.
	Таблицы вывода - специальные элементы интерфейса для вывода в различном виде списков объектов/записей.
*/

class uiTable extends uiTableFunctions {

	protected $style = table;
    private $auto_format = false;
    private $show_styles = false;
    private $show_search = false;
    private $hide_empty_columns = false;
    private $fast_edit = false;
    private $print = false;
    private $empty_text = '';
    private $def_right = '';
    private $del_title, $del_text, $del_title_multi, $del_text_multi;

    private $count = 0;
    private $all_count = 0;
    private $rights = array();
    private $columns = array();
    private $del_columns = array();
    private $moreParam = array();

    private $filters = false;
    private $disable_filters = false;

    private $select, $filter;
    private $prefix;

    private $isSelection = false;

    public $inSearch = false;


    /**
    * @return NULL
    * @param Const $style - Возможно одно из значений:
         		table 	- данные выводятся в виде классической таблицы
         		list 	- данные выводятся в виде списка
         		gallery - представляет собой классическую фотогаллерею с изображениями объектов
    * @desc Определяет стиль отображения таблицы
    */
    function style($style) {
    	$this->style = $style;
    }

    // Показывает \ прячет панель выбора стиля таблицы
    function showStyles($bool) {
    	$this->show_styles = $bool;
    }

    // Показывает \ прячет поиск по таблице
    function showSearch($bool) {
    	$this->show_search = $bool;
    }

    // Показывает \ прячет пустые столбцы таблицы
    function hideEmptyColumns($bool) {
    	$this->hide_empty_columns = $bool;
    }


    // Включает \ выключает быстрое редактирование текстовых полей в таблице
    // Работает только в режимах table и list
    function fastEdit($bool) {
    	$this->fast_edit = $bool;
    }

    // Включает \ выключает автоформатирование значений полей
    function formatValues($bool) {
    	$this->auto_format = $bool;
    }

    // Устанавливает текст который выведется, если таблица пустая
    function emptyText($text) {
    	$this->empty_text = $text;
    }

    /**
    * @return NULL
    * @param string $r_name - Системное название права
    * @param string $r_class - CSS-Класс с картинкой
    * @param Const $r_view - Способ применения. Возможные варианты:
         			single 		- Право можно применять только для одного элемента таблицы
         			multi 		- Право применяется для одного или для нескольких элементов таблицы
         			only_multi 	- Право можно применять только для нескольких элементов
    * @param string $r_java - Имя JavaScript-функции, которая будет вызвана при событии onClick()
    * @param string $r_text - Текст подсказки который должен выводиться, если у права стоит стиль multi или only_multi
    * @desc Добавляет новое право для элементов таблицы
    */
    function addRight($r_name, $r_class, $r_view = single, $r_java = '', $r_text = '') {
    	$this->rights[] = array(
    		'name' => $r_name,
     		'class' => $r_class,
            'view' => $r_view,
            'java' => $r_java,
            'text' => $r_text
       	);
    }

    // Добавляет отступ между правами
    function addEmptyRight() {
    	$this->rights[] = 'empty';
    }

    /**
    * @return NULL
    * @param string $title - Заголовок столбца
    * @param string $field - Поле объекта, значение которого должно выводиться в данном столбце
    * @param Int $size - Ширина поля, учитывается только в режиме table
    * @param string $isSortable - Если TRUE, то по данному столбцу можно будет сортировать
    * @param string $click - Если TRUE, то при клике по столбцу будет вызывается стандартное действие для строчки
    * @param string $funct_name - Имя PHP-функции которое будет вызвано обработке значений данного стобца
	* @param string $first_order - Сортировка при первом клике 0 - asc, 1 - desc
    * @desc Добавляет столбец в таблицу
    */
    function addColumn($field, $title = 0, $size = 0, $isSortable = false, $click = true, $funct_name = '', $first_order = 0) {
    	$this->columns[] = array(
    		'title' => $title,
      		'field' => $field,
        	'size' => $size,
         	'sortable' => $isSortable,
          	'funct_name' => $funct_name,
           	'click' => $click,
			'first_order' => $first_order
        );
    }

    // Устанавливает право по умолчанию
    function defaultRight($right) {
    	$this->def_right = $right;
    }

    // Устанавливает дополнительные параметры передаваемые по ссылке и через POST
    function moreParam() {
    	$this->moreParam = func_get_args();
    }



	/**
    * @return NULL
    * @param string $title - Заголовок сообщения
    * @param string $text - Текст сообщения
    * @desc Устанавливает текст для сообщения удаления записи
    */
 	function setDelMessage($title, $text) {
  		$this->del_title = $title;
    	$this->del_text = $text;
  	}

  	/**
    * @return NULL
    * @param string $title - Заголовок сообщения
    * @param string $text - Текст сообщения
    * @desc Устанавливает текст для сообщения удаления нескольких записи
    */
 	function setMultiDelMessage($title, $text) {
  		$this->del_title_multi = $title;
    	$this->del_text_multi = $text;
  	}

    /*
    	Конструктор класса. Устанавливает источник выборки объектов.
    	Источником может быть либо объект класса ormSelect, либо ассоциативный массив.
    	При этом ключи массива должны совпадать с названием столбцов таблицы.
    */
	function __construct($data, $count = 0) {
	    if ($data instanceof ormSelect) {
	 		$this->select = $data;
	   		$this->filter = new ormFilterForm($this->select);
	   		$this->isSelection = true;
   		} else if (is_array($data)) {
   			$this->data = $data;
            $this->all_count = $count;
        } else
   			trigger_error ('new uiTable("'.$data.'") - Немогу с этим  работать!!', E_USER_ERROR);
 	}

    // Устанавливаем настройки по умолчанию
    static private function startSession(){

        $prefix = md5(system::url(0).system::url(1));

        if (!isset($_SESSION['table_'.$prefix])) {
		    $_SESSION['table_'.$prefix]['max_count'] = 20;
		   	$_SESSION['table_'.$prefix]['page_num'] = 1;
		   	$_SESSION['table_'.$prefix]['table_search'] = '';
			$_SESSION['table_'.$prefix]['page_num_temp'] = 1;
		}
        //$_SESSION['table_'.$prefix]['max_count'] = 1;
        // Количество строк в таблице
        if (isset($_POST['max_count'])) {
		    $_SESSION['table_'.$prefix]['max_count'] = $_POST['max_count'];
			$_SESSION['table_'.$prefix]['page_num'] = 1;
	    }

        // Выбор нужной страницы
	    if (isset($_POST['page_num']))
		    $_SESSION['table_'.$prefix]['page_num'] = $_POST['page_num'];

        return $prefix;
    }

    static function getMaxCount(){
        $prefix = self::startSession();
        return $_SESSION['table_'.$prefix]['max_count'];
    }

    static function getCurPage(){
        $prefix = self::startSession();
        return $_SESSION['table_'.$prefix]['page_num'];
    }

	// Формирует таблицу
 	function getHTML($templ_name = 'table') {

  		$templ_name = $templ_name.'_'.$this->style;

    	if (file_exists(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl')){
     		include(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl');

            // Устанавливаем настройки по умолчанию
         	$this->prefix = md5(system::url(0).system::url(1).((system::url(2) != '') ? system::url(2) : ''));

            if (!isset($_SESSION['table_'.$this->prefix])) {
                $_SESSION['table_'.$this->prefix]['max_count'] = 20;
                $_SESSION['table_'.$this->prefix]['page_num'] = 1;
                $_SESSION['table_'.$this->prefix]['table_search'] = '';
				$_SESSION['table_'.$this->prefix]['page_num_temp'] = 1;
            }

    		// Смотрим пришедшие данные из POST
        	if (!isset($_POST['filter'])) {

	        	// Сортировка по столбцу
	            if (isset($_POST['field']) && isset($_POST['parram'])) {
		        	$_SESSION['table_'.$this->prefix]['order_field'] = system::POST('field');
		            $_SESSION['table_'.$this->prefix]['order_parram'] = system::POST('parram');
	            }

                // Количество строк в таблице
                if (isset($_POST['max_count'])) {
			        $_SESSION['table_'.$this->prefix]['max_count'] = $_POST['max_count'];
			        $_SESSION['table_'.$this->prefix]['page_num'] = 1;
	            }

             	// Выбор нужной страницы
	            if (isset($_POST['page_num']))
			        $_SESSION['table_'.$this->prefix]['page_num'] = $_POST['page_num'];

			    // Поиск по таблице
	            if (isset($_POST['table_search'])){
					$_SESSION['table_'.$this->prefix]['page_num_temp'] = $_SESSION['table_'.$this->prefix]['page_num'];
					$_SESSION['table_'.$this->prefix]['page_num'] = 1;
					if ($_POST['table_search'] == '')
						$_SESSION['table_'.$this->prefix]['page_num'] = $_SESSION['table_'.$this->prefix]['page_num_temp'];
			        $_SESSION['table_'.$this->prefix]['table_search'] = $_POST['table_search'];
				}

                // Очистка результатов поиска
                if (isset($_POST['clear_search'])) {
					$_SESSION['table_'.$this->prefix]['page_num'] = $_SESSION['table_'.$this->prefix]['page_num_temp'];
			        $_SESSION['table_'.$this->prefix]['table_search'] = '';
			        if ($this->isSelection)
			        	$this->filter->clear();
					//$_SESSION['table_'.$this->prefix]['max_count'] = 3;
				}

				// Нажали ссылку "обычный поиск" (скрыть / показать фильтры)
				if ($this->isSelection && isset($_POST['showfilter'])){
					if ($_POST['showfilter'] == 0)
            			$_SESSION['table_'.$this->prefix]['filters'] = '';
            		$_SESSION['table_'.$this->prefix]['showfilter'] = $_POST['showfilter'];
            		system::stop();
            	}
	        }

	        // Сортировка по столбцу
	        if ($this->isSelection && isset($_SESSION['table_'.$this->prefix]['order_field'])) {
                $this->select->orderBy(
                	$_SESSION['table_'.$this->prefix]['order_field'],
                	$_SESSION['table_'.$this->prefix]['order_parram']
                );
      		}

            $up_line = '';

            // Устанавливаем список полей для выборки
            if ($this->isSelection && !$this->select->fieldsIsDefined()) {
                        /*
                $class_name = $this->select->getObjectsClass();
                if (!empty($class_name))
                	$select = (ormClasses::get($class_name)->issetField('active')) ? 'active' : '';
                else    */
                $select = 'active';

                while (list($key, $column) = each($this->columns)) {

                    $zpt = (!empty($select)) ? ', ' : '';
                	if (strpos($column['field'], ' ')) {
						$vals = explode(' ', $column['field']);
					    while (list($k, $val) = each($vals))
					    	$select .= $zpt.$val;
					} else
	                		$select .= $zpt.$column['field'];
                }
            	$this->select->fields($select);
            }

            // Вывод формы ПОИСКа и установка параметров
            if ($this->show_search){

	      		if ($this->isSelection && !empty($_SESSION['table_'.$this->prefix]['table_search'])) {

	                $query = $_SESSION['table_'.$this->prefix]['table_search'];
	                $this->inSearch = true;
	                $where = array();
	                reset($this->columns);
	                while (list($key, $column) = each($this->columns))
	                    if ($column['field'] != 'children' && $column['field'] != 'parent') {
		                    if (strpos($column['field'], ' ')) {
					       		$vals = explode(' ', $column['field']);
					         	while (list($k, $val) = each($vals))
					          		$where[] = $this->select->val($val, 'LIKE', '%'.$query.'%');
					    	} else
	                			$where[] = $this->select->val($column['field'], 'LIKE', '%'.$query.'%');
	                	}
	                $this->select->where( $this->select->logOr($where) );
	      		}

	      		page::assign('table_search', $_SESSION['table_'.$this->prefix]['table_search']);
	      		$up_line .= page::parse($TEMPLATE['search']);
            }

            // +  +  +	Все что касается вывода ФИЛЬТРОВ  +	+	+	+	+	+	+	+
            if ($this->isSelection && !$this->disable_filters) {

				//$_SESSION['table_'.$this->prefix]['page_num'] = 1;
				
                if (!isset($_SESSION['table_'.$this->prefix]['showfilter']))
                	$_SESSION['table_'.$this->prefix]['showfilter'] = $this->filters;

                // Определяем нужно или нет показывать список фильтров
                $show_filter = $this->filters;
                if ($_SESSION['table_'.$this->prefix]['showfilter'])
	            	$show_filter = true;

     			page::assign('mores', (($show_filter) ? 'mores' : ''));

                $this->filter->setFilters();
			    page::assign('filters', $this->filter->getHTML());

                // Кнопка "Показать / спрятать фильтры"
                if ($this->show_search && $this->filter->getCount() > 0) {

	            	if ($show_filter) {
		            	page::assign('moresearch', 1);
		            	page::assign('sh_text', lang::get('TABLE_SEARCH_MIN'));
		            } else {
		            	page::assign('moresearch', 0);
		            	page::assign('sh_text', lang::get('TABLE_SEARCH_MAX'));
		            }
		            page::fParse('filters_link', $TEMPLATE['filters_link']);
	            } else page::assign('filters_link', '');

              	page::fParse('filters', $TEMPLATE['filters']);

                if ($show_filter && $this->filter->getCount() > 0 && !$this->show_search) {
                    page::assign('filters_link', '');
                    $up_line .= page::parse($TEMPLATE['without_search']);
                }

	        } else page::assign('filters', '');

            page::assign('up_line', $up_line);


            // Кнопка "версия для печати"
            if ($this->print)
            	$up_line .= page::parse($TEMPLATE['print_link']);

      		// Дополнительные параметры запросов
            $str_param = '';
            while (list($key, $param) = each($this->moreParam))
               	$str_param .= '/'.$param;
            page::assign('parram', $str_param);


            // Вывод сообщения "Таблица пустая"
            $count = ($this->isSelection) ? $this->select->getCount() : count($this->data);
          	if ($count <= 0) {

                if ($this->inSearch || ($this->isSelection && $this->filter->isActive()))
                    $message = lang::get('TABLE_NOT_FOUND').'<br /><span onClick="stopSearch()">'.lang::get('TABLE_NOT_FOUND2').'</span>';
                else
           			$message = (empty($this->empty_text)) ? lang::get('TABLE_EMPTY') : $this->empty_text;
                page::assign('message', $message);

                if (system::isAjax()) {
                	echo page::parse($TEMPLATE['empty_frame']);
                 	system::stop();
                } else {
                	page::fParse('content', $TEMPLATE['empty_frame']);
            		return page::parse($TEMPLATE['main']);
            	}
            }

    		// Постраничная навигация
            $this->navigation(5, $TEMPLATE);

            // Вывод прав
            $this->rights($TEMPLATE);

            // Определяем модуль для права по умолчанию
            $pos = strpos($this->def_right, '.');
            if (!empty($pos)) {
         		$module = substr($this->def_right, 0, $pos);
             	$this->def_right = substr($this->def_right, $pos + 1, strlen($this->def_right));
            } else $module = system::url(0);

            if (user::issetRight($this->def_right, $module)) {
            	$this->def_right = system::au().'/'.$module.'/'.$this->def_right.'/';
            } else $this->def_right = '';

            $this->count = ($this->isSelection) ? $this->select->getObjectCount() : count($this->data);

			if ($this->count == 0 && $_SESSION['table_'.$this->prefix]['page_num'] != 1)
				$_SESSION['table_'.$this->prefix]['page_num'] -= 1;

            page::assign('table_parent_id', 0);
            
            // Вывод строчек
            if ($this->isSelection)
            	$this->selItems($TEMPLATE);
            else
            	$this->masItems($TEMPLATE);

      		// Вывод столбцов
      		$this->columns($TEMPLATE);




            // Текст сообщения об удалении элементов
            if (empty($this->del_title) || (empty($this->del_text))) {
            	$this->del_title = lang::get('TABLE_DROP_TITLE');
            	$this->del_text = lang::get('TABLE_DROP_TEXT');
            }
            if (empty($this->del_title_multi) || (empty($this->del_text_multi))) {
            	$this->del_title_multi = lang::get('TABLE_DROP_TITLE_MULTI');
            	$this->del_text_multi = lang::get('TABLE_DROP_TEXT_MULTI');
            }
            page::assign('del_title', $this->del_title);
            page::assign('del_text', $this->del_text);
            page::assign('del_title_multi', $this->del_title_multi);
            page::assign('del_text_multi', $this->del_text_multi);
            page::assign('select_checkbox', lang::get('TABLE_SEL_CHECKBOX'));

            


            // Версия для печати (НУЖНО ДОДЕЛАТЬ)
            if (system::getCurrentNavVal() == 'print') {
            	echo page::parse($TEMPLATE['frame']);
                system::stop();
            }

            if (system::isAjax()) {
                echo page::parse($TEMPLATE['frame']);
                system::stop();
            } else {
                page::fParse('content', $TEMPLATE['frame']);
                return page::parse($TEMPLATE['main']);
            }
        }
    }

	// Вывод прав
    private function rights($TEMPLATE){

    	$rights = $rights_multi = $rights_act = '';
        while (list($key, $right) = each($this->rights)){

            // Определяем модуль для указанного права
            $pos = strpos($right['name'], '.');
            if (!empty($pos)) {
	        	$module = substr($right['name'], 0, $pos);
	            $right['name'] = substr($right['name'], $pos + 1, strlen($right['name']));
            } else $module = system::url(0);

            if (user::issetRight($right['name'], $module)) {

                $url = system::au().'/'.$module.'/'.$right['name'].'/';
                page::assign('url', $url);
          		page::assign('class', $right['class']);
          		page::assign('hint', lang::right($right['name']));
                page::assign('del_button', ($right['class'] == 'drop') ? 'id="del_button"' : '');

                $java = (!empty($right['java'])) ? ' onClick="return '.$right['java'].'"' : '';
          		page::assign('java', $java);

	             if ($right['class'] != 'active' && $right['view'] != only_multi) {

	              	// Формируем список прав с одинарным выбором
	              	$rights .= page::parse($TEMPLATE['right']);

	             } else if ($right['class'] == 'active') {

	             	// Активность объекта
	              	$rights_act .= page::parse($TEMPLATE['right_active']);
	              	//$active_right = $url;
	             }

	             // Формируем список прав с множественным выбором
	             if ($right['view'] == multi || $right['view'] == only_multi) {

		          	if ($right['class'] == 'active')
		          		$text = lang::get('TABLE_ACTIVE_RIGHT');
		      		else if ($right['class'] == 'drop')
		          		$text = lang::get('TABLE_DROP_RIGHT');
		      		else $text = $right['text'];
		          	page::assign('hint', $text);

	              	$rights_multi .= page::parse($TEMPLATE['right_multi']);
	             }

            } else if ($right['class'] == 'active') {

            	// Активность объекта - не кликабельно
          		$rights_act .= page::parse($TEMPLATE['right_active_noclick']);

         	}
        }

        page::assign('rights', $rights);
        page::fParse('rights', $TEMPLATE['rights']);
        page::assign('active', $rights_act);
        page::assign('rights_multi', $rights_multi);

        // Смотрим, нужно ли выводить чекбоксы
        if (!empty($rights_multi)) {
        	page::fParse('checkbox', $TEMPLATE['checkbox']);
         	page::fParse('checkbox_multi', $TEMPLATE['checkbox_multi']);
        } else {
        	page::assign('checkbox', '');
         	page::assign('checkbox_multi', '');
        }

        // Смотрим, выводить или нет первый столбец-заглушку
        if (!empty($rights_act) || !empty($rights_multi)){

        	$width = (!empty($rights_act) && !empty($rights_multi)) ? 60 : 30;
        	page::assign('width', $width);
        	page::fParse('first_column', $TEMPLATE['first_column']);
        	page::fParse('item_check', $TEMPLATE['item_check']);

        } else {

        	page::assign('first_column', '');
        	page::assign('item_check', '');
        }

        page::fParse('shapka', $TEMPLATE['shapka']);
    }

    // вывод шапки таблицы
    private function columns($TEMPLATE) {

    	$columns = '';
        reset($this->columns);
        while (list($key, $column) = each($this->columns)){

            if (!isset($this->del_columns[$column['field']]) || $this->del_columns[$column['field']] != $this->count){

	            if ($column['title'] === 0)
	        		$column['title'] = ormClasses::get($this->select->getObjectsClass())->getFieldName($column['field']);

	        	page::assign('title', $column['title']);

	            $size = (!empty($column['size'])) ? 'width="'.$column['size'].'"' : '';
	            page::assign('width', $size);

	            if ($column['sortable'] && !strpos($column['field'], ' ') && ($column['field'] != 'children' && $column['field'] != 'parents')) {

	                if ($this->isSelection && $this->select->orderField() == $column['field']) {
	                    $parram = $this->select->orderParram();
	                    page::assign('sort', $parram);

	                    if ($parram == 'asc')
	                    	page::assign('sort2', 'desc');
	                    else if ($parram == 'desc')
	                    	page::assign('sort2', 'asc');

	                } else {
						page::assign('sort', 'none');
						if ($column['first_order'] == 0) {
							page::assign('sort2', 'asc');
						} else {
							page::assign('sort2', 'desc');
						}
	                }

	                page::assign('field', $column['field']);
	            	$sortable = page::parse($TEMPLATE['column_order']);

	            } else $sortable = '';
	            page::assign('column_order', $sortable);

	            $columns .= page::parse($TEMPLATE['column']);
         	}
        }
        page::assign('columns', $columns);
    }

    // Построение постраничной навигации
     private function navigation($smeshenie = 5, $TEMPLATE) {

		$max_count = $_SESSION['table_'.$this->prefix]['max_count'];
		$current_num = $_SESSION['table_'.$this->prefix]['page_num'];
		
		if ($this->isSelection)
			$count_page = ceil($this->select->getCount() / $max_count);
		else
			$count_page = ceil(((!empty($this->all_count)) ? $this->all_count : count($this->data)) / $max_count);;
		
		if ($current_num > $count_page) {
			$current_num = $_SESSION['table_'.$this->prefix]['page_num'] = $count_page;
		}
		
		$start = $current_num * $max_count - $max_count;
		
		if ($count_page > 1) {
		
			// Просчитывает какие страницы показывать
			$niz = $current_num - $smeshenie;
			
			if ($niz < 1) $niz = 1;
			$verx = $current_num + $smeshenie;
			if ($verx > $count_page) $verx = $count_page;
			
			// Определяемся с левым блоком
			page::assign('num_l', $current_num - 1);
			page::assign('first_num', 1);
			page::assign('num_r', $current_num + 1);
			page::assign('last_num', $count_page);
			
			if ($current_num == 1 && isset($TEMPLATE['left_block'])) {
				page::fParse('left_block',$TEMPLATE['noact_left_block']);
				page::fParse('first_block', $TEMPLATE['noact_first_block']);
				page::fParse('right_block', $TEMPLATE['right_block']);
				page::fParse('last_block', $TEMPLATE['last_block']);
			} else if ($current_num == $count_page && isset($TEMPLATE['right_block'])) {
				page::fParse('left_block',$TEMPLATE['left_block']);
				page::fParse('first_block', $TEMPLATE['first_block']);
				page::fParse('right_block', $TEMPLATE['noact_right_block']);
				page::fParse('last_block', $TEMPLATE['noact_last_block']);
			} else {
				page::fParse('left_block',$TEMPLATE['left_block']);
				page::fParse('first_block', $TEMPLATE['first_block']);
				page::fParse('right_block', $TEMPLATE['right_block']);
				page::fParse('last_block', $TEMPLATE['last_block']);
			}
			
			// Вывод списка страниц
			$pages = '';
			
			for ($i = $niz; $i < $verx+1; $i++){
				page::assign('page_num', $i);
				$tmpl = ($i == $current_num) ? 'pages_a' : 'pages_na';
				$pages .= page::parse($TEMPLATE[$tmpl]);
			}
			
			page::assign('pages', $pages);
			$navbar = page::parse($TEMPLATE['navigation']);
		
		} else $navbar = '';
		
		if (!empty($navbar)) {
		
			$counts = array(1 => 1, 5 => 5, 10 => 10, 20 => 20, 30 => 30, 50 => 50, 100 => 100);
			ui::SelectBox('max_count', $counts, $max_count, 50);
			
			page::assign('navbar', $navbar);
			page::assign('count_page', $count_page);
			page::fParse('navbar', $TEMPLATE['navibar']);
		
		} else page::assign('navbar', '');
		
		// Устанавливает лимит для выборки объектов
		if ($this->isSelection && $start > -1 && $max_count > 0)
			$this->select->limit($start, $max_count);
	}

    // Вывод строчек - РЕЖИМ МАССИВА
	private function masItems($TEMPLATE) {

    	$items = '';
        while (list($obj_num, $obj) = each($this->data)){

            if (!isset($obj['id']))
        		$obj['id'] = $obj_num;

            page::assign('id', $obj['id']);
            page::assign('url', $this->def_right.$obj['id']);

            $item_vals = '';
            reset($this->columns);
        	while (list($key, $column) = each($this->columns)){

                // Обработка значений
                $value = (isset($obj[$column['field']])) ? $obj[$column['field']] : '';
		    	if (!empty($column['funct_name']))
		        	$value = $this->exeFunction($value, $obj, $column['funct_name']);

                page::assign('value', $value);

                $first = (empty($key)) ? ' first' : '';
            //    $first .= ($column['field'] == 'name') ? ' td_name' : '';
        		page::assign('first', $first);

                $click = ($column['click'] && !empty($this->def_right)) ? '' : '_no_click';
            	$item_vals .= page::parse($TEMPLATE['item_val'.$click]);
            }
            page::assign('item_vals', $item_vals);
            page::assign('act', ((isset($obj['active']) && $obj['active']) ? 1 : 0));

            $items .= page::parse($TEMPLATE['items']);
        }
        page::assign('items', $items);

	}

	// Вывод строчек - РЕЖИМ ВЫБОРКИ
	private function selItems($TEMPLATE) {

        if ($this->hide_empty_columns) {

        	while ($obj = $this->select->getObject()){

	            reset($this->columns);
	        	while (list($key, $column) = each($this->columns)){

                    $value = $this->getProcValue($obj, $column['field'], $column['funct_name']);
	        		if (empty($value))
	        			if (isset($this->del_columns[$column['field']]))
	        				$this->del_columns[$column['field']] ++;
	        			else
	        				$this->del_columns[$column['field']] = 1;
	            }
	        }

	        $this->select->reset();
        }

        $items = '';
        while ($obj = $this->select->getObject()){

            page::assign('id', $obj->id);
            page::assign('table_parent_id', $obj->getParentId());
            page::assign('url', $this->def_right.$obj->id);

            $item_vals = '';
            reset($this->columns);
        	while (list($key, $column) = each($this->columns)){

                if (!isset($this->del_columns[$column['field']]) || $this->del_columns[$column['field']] != $this->count){
	                // Обработка значений
	                $value = $this->getProcValue($obj, $column['field'], $column['funct_name']);
	        		page::assign('value', $value);

	        		$first = (empty($key)) ? ' first' : '';
	             //   $first .= ($column['field'] == 'name') ? ' td_name' : '';
	        		page::assign('first', $first);

	                $click = ($column['click'] && !empty($this->def_right)) ? '' : '_no_click';
	            	$item_vals .= page::parse($TEMPLATE['item_val'.$click]);
            	}
            }
            page::assign('item_vals', $item_vals);
            page::assign('act', (($obj->__get('active')) ? 1 : 0));

            $items .= page::parse($TEMPLATE['items']);
        }
        page::assign('items', $items);
	}

    // Обрабатываем значение
    private function getProcValue($obj, $field, $function) {

        $value = '';
    	$field_type = 0;

        // Получаем значение
    	if (strpos($field, ' ')) {

       		$vals = explode(' ', $field);
         	while (list($k, $val) = each($vals)){
          		$pre = (empty($k)) ? '' : ' ';
            	$value .= $pre.$obj->__get($val);
          	}

    	} else {
     		$value = $obj->__get($field);
            if ($this->auto_format)
            	$field_type = $obj->getClass()->getFieldType($field);
        }

        // Обрабатываем значение
        return $this->processValue($obj, $field, $field_type, $value, $function);
    }



    // -	-	-	-	-	Методы для работы с фильтрами 	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-


    //
    function clearFilter() {
    	$this->filter->clear();
    }

    // 
    function setFilterActive($bool) {
    	$this->filter->setActive($bool);
    }

    // Включает \ выключает фильтры
    function disableFilters($bool) {
    	$this->disable_filters = $bool;
    }

    // Показывает \ прячет фильтры
    function showFilters($bool) {
    	$this->filters = $bool;
    }

    // Список полей фильтры для которых показывать не нужно
	public function hideFilters(){
		$this->filter->hideFilters(func_get_args());
	}

    /**
 	* @return NULL
  	* @param String $field - Системное название поля по которому будет создан фильтр
  	* @param String $title - Название фильтра, если пусто используется стандартное название поля
  	* @param Bool $period - Если true, для фильтра (по числам или датам) можно указать область вхождения
  	* @desc Отображает фильтр для указанного поля
 	*/
	public function showFilter($field, $title = '', $period = ''){
        $this->filter->showFilter($field, $title, $period);
	}

	/**
 	* @return NULL
  	* @param Int $position - Позиция фильтра в списке
  	* @param string $title - Название фильтра
  	* @param TEXT $html - Сам фильтр (HTML-код)
  	* @param Bool $in_frame - Если true, фильтр выводиться без стандартного обрамления
  	* @desc Добавляет новый фильтр в указанную позицию
 	*/
	public function newFilter($position, $title, $html, $in_frame = true){
		$this->filter->newFilter($position, $title, $html, $in_frame);
	}



}

?>