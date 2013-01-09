<?php

class structureMacros {


    /**
	* @return HTML
	* @param string $templ_name - шаблон оформления по которому будет строится меню
	* @param int $max_level - Максимальное количество уровней вложенности
	* @param int $section_id - ID раздела сайта, с которого будет строится меню
    * @param int $max_count - Максимальное количество элементов в меню. Если указано 0 - без ограничений
	* @desc МАКРОС: Выполняет построение меню сайта в виде дерева
	*/
	public function menu($templ_name = 'default', $max_level = 0, $section_id = 0, $max_count = 0) {

        $templ_file = '/structure/menu/'.$templ_name.'.tpl';
        $TEMPLATE = page::getTemplate($templ_file);

	    if (!is_array($TEMPLATE))
	    	return page::errorNotFound('structure.menu', $templ_file);
	    else {

            ormPages::reset();
                  

            $info = ormPages::getSectionByPath($section_id);
            if ($info['section'] instanceof ormPage)
                $section_id = $info['section']->id;

            $no_view = reg::getList(ormPages::getPrefix().'/no_view');
	    	return $this->getMenuListFor($section_id, 1, $max_level, $max_count, $TEMPLATE, $templ_file, $no_view);
	    }

 	}

    // Рекурсивный метод, формирует меню сайта. Вспомогательный метод для макроса %structure.menu%
 	private function getMenuListFor($section_id, $level, $max_level, $max_count, $TEMPLATE, $templ_file, $no_view) {

 		$pages = '';
        $num = 0;
        $cur_level = (isset($TEMPLATE[$level])) ? $level : $level - 1;
        $all_count = ormPages::getViewOfSection($section_id);

    	while($page = ormPages::getPageOfSection($section_id, $no_view)) {

    		if ($page->view_in_menu && (empty($max_count) || $max_count > $num)) {

                $act = (in_array($page->id, ormPages::getActiveId())) ? '_active' : '';

                if (isset($TEMPLATE[$cur_level]['list'.$act]))
                	$fi = page::getFields('obj', $TEMPLATE[$cur_level]['list'.$act]);
                else return page::errorBlock('structure.menu', $templ_file, 'list'.$act);

                $sub_menu = '';
                if ($page->view_submenu && isset($fi['mono']) && in_array('sub_menu', $fi['mono']) && $page->issetChildren())
                	$sub_menu = $this->getMenuListFor($page->id, $cur_level + 1, $max_level, $max_count, $TEMPLATE, $templ_file, $no_view);
	            page::assign('sub_menu', $sub_menu);

	            $num ++;
	            $target = ($page->in_new_window) ? ' target="_blank"' : '';

	            page::assign('obj.num', $num);
	            page::assign('obj.target', $target);
	            page::assign('class-first', ($num == 1) ? 'first' : '');
	            page::assign('class-last', ($num == $all_count) ? 'last' : '');
	            page::assign('class-odd', ($num % 2 == 0) ? 'odd' : '');
	            page::assign('class-even', ($num % 2 != 0) ? 'even' : '');
                page::assign('class-third', ($num % 3 == 0) ? 'third' : '');

	            page::assign('obj.id', $page->id);
	            page::assign('obj.name', $page->name);
	            page::assign('obj.url', $page->_url);
	            page::assign('obj.h1', $page->h1);
	    		page::assign('obj.title', $page->title);
	    		page::assign('obj.keywords', $page->keywords);
                page::assign('obj.description', $page->description);

	            page::assign('obj.img_h1', $page->img_h1);
	            page::assign('obj.img_act', $page->img_act);
	    		page::assign('obj.img_no_act', $page->img_no_act);

	    		if ($num > 1 && isset($TEMPLATE[$cur_level]['separator']))
	    			$pages .= page::parse($TEMPLATE[$cur_level]['separator']);

                $class = $page->getClass()->getSName();
                $tname = (isset($TEMPLATE[$cur_level]['list_'.$class.$act])) ? 'list_'.$class.$act : 'list'.$act;

	            if (!isset($TEMPLATE[$cur_level][$tname]))
	            	$tname = 'list';

                if (isset($TEMPLATE[$cur_level][$tname]))
	    			$pages .= page::parse($TEMPLATE[$cur_level][$tname]);
    		}
    	}

    	if (!empty($pages)) {
    		page::assign('list', $pages);
    		return page::parse($TEMPLATE[$cur_level]['frame']);
    	}
 	}

	/**
	 * @return HTML
	 * @param int $page_id - ID страницы, из которой будет браться ссылка на видео
	 * @param string $field - Системное имя поля в котором лежит ссылка на видео
	 * @param string $templ_name - шаблон оформления в котором лежит оформление для встраиваемого видео
	 * @desc МАКРОС: Вернет IFrame с видео, взависимости от видеохостинга
	 */
	
	function video($page_id, $field = 'video', $templ_name = '_video_iframes') {
		$templ_file = '/structure/objects/'.$templ_name.'.tpl';
		$TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
			return page::errorNotFound(__CLASS__.'.'.__FUNCTION__, $templ_file);
		
		if ($obj = ormPages::get($page_id)) {
			if ($obj->$field != '') {
				$domen = parse_url( $obj->$field, PHP_URL_HOST );
				
				if (strpos($domen, 'youtube')) {
					parse_str( parse_url( $obj->$field, PHP_URL_QUERY ), $my_array_of_vars );
					if (isset($my_array_of_vars['v'])) {
						$hash = parse_url( $obj->$field, PHP_URL_FRAGMENT );
						$hash = ($hash != '') ? '#'.$hash : '';
						page::assign('video.hash', $hash);
						page::assign('video.id', $my_array_of_vars['v']);
						return page::parse($TEMPLATE['youtube']);
					}
				} else if (strpos($domen, 'vimeo')) {
					$path = explode('/',parse_url( $obj->$field, PHP_URL_PATH ));
					page::assign('video.id', $path[1]);
					return page::parse($TEMPLATE['vimeo']);
				}
			}
		}
		return page::parse($TEMPLATE['empty']);
	}

	/**
	 * @return int $num
	 * @param int $id - id элемента
	 * @desc МАКРОС: Вернет какой по счету элемент с учетом удаленных и неактивных элементов
	 */

	public function position($id) {
		if ($obj = ormPages::get($id)) {
			$sel = new ormSelect();
			$sel->findInPages();
			$sel->where('active', '=', 1);
			$sel->where('parents', '=', $obj->parent_id);
			$sel->where('position', '<', $obj->getPosition());
			$sel->orderBy('position', asc);
			return $sel->getCount() + 1;
		} else {
			return 0;
		}
	}

	/**
	 * @return HTML список значений справочника
	 * @param int $list_id - ID справочника.
	 * @param string $templ_name - шаблон
	 * @desc МАКРОС: Возвращает список значений справочника
	 */
	public function getList($list_id, $templ_name = 'default') {
		$templ_file = '/structure/objects/'.$templ_name.'.tpl';
		$TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
			return page::errorNotFound('structure.getList', $templ_file);

		$list = '';

		$sel = new ormSelect($list_id);
		while ($obj = $sel->getObject()) {
			page::assign('obj.id', $obj->id);
			page::assign('obj.name', $obj->name);
			$list .= page::parse($TEMPLATE['list']);
		}

		page::assign('list', $list);
		if ($list != '') {
			$ret = page::parse($TEMPLATE['frame_list']);
		} else {
			$ret = page::parse($TEMPLATE['empty']);
		}
		return $ret;
	}
	
    /**
	* @return HTML
	* @param int $section_id - ID страницы сайта, подразделы которой будут выводиться в списке
	* @param string $templ_name - Шаблон оформления по которому будет строится список подразделов
	* @param int $max_count - Максимальное количество элементов в списке
	* @param string $order_by - Способ сортировки элементов списка. SQL-подобный синтаксис, например: "name DESC".
	* @param int $start_pos - Номер элемента по порядку с которого будет выводиться список.
	* @desc МАКРОС: Выводит содержимое конкретного раздела сайта, при необходимости формирует список подразделов.
	*/
 	public function objView($section_id, $templ_name = 0, $max_count = 0, $order_by = 0, $start_pos = 0) {

        if ($page = ormPages::get($section_id)) {

            if (empty($templ_name)) {
            	$templ_obj = templates::get($page->template2_id);
            	$templ_name = ($templ_obj instanceof template) ? $templ_obj->getFile() : 'default';
	        }

	        $templ_file = '/structure/objects/'.$templ_name.'.tpl';
	        $TEMPLATE = page::getTemplate($templ_file);

		    if (!is_array($TEMPLATE))
		    	return page::errorNotFound('structure.objView', $templ_file);

	        $class = $page->getClass()->getSName();
	        $templ = 'frame_'.$class;
	        if (!isset($TEMPLATE[$templ]) && isset($TEMPLATE['frame']))
	            $templ = 'frame';

            $pre = (system::getCurrentNavVal() == 'print' && isset($TEMPLATE['print_'.$templ])) ? 'print_' : '';

	        if (isset($TEMPLATE[$pre.$templ])) {

		            $fields = page::getFields('obj', $TEMPLATE[$pre.$templ]);

		            // Выводим список подразделов (если нужно)
		          	if (isset($fields['mono']) && in_array('list', $fields['mono'])) {

		          	    $list = $this->objList($page, $TEMPLATE, $max_count, $order_by, $start_pos);

		          	    if (!empty($list) && isset($TEMPLATE[$templ.'_list'])) {

		          	    	// Выводим список в обрамлении
		          	    	page::assign('list', $list);
		          	    	$list = page::parse($TEMPLATE[$templ.'_list']);

		          	    } else if (empty($list)) {

	                        // Выводим сообщение "Список пуст!"
	                        if (isset($TEMPLATE['list_empty_'.$class]))
		          	    		$list = page::parse($TEMPLATE['list_empty_'.$class]);
		          	    }

		          		page::assign('list', $list);
		          	}

	                // Парсим поля страницы
		            if (isset($fields['obj']))
		            	while(list($num, $name) = each($fields['obj']))
		            		page::assign('obj.'.$name, $page->__get($name));

	                page::assign('back_url', system::preUrl($page->url));
                    page::assign('obj.class', $class);


		    		page::assign('print_url', $page->url.'=print');

		    		return page::parse($TEMPLATE[$pre.$templ]);
	        }
        }
 	}


    public function filterABC($section, $templ_name = 'filter_abc', $alphabet = 'ru', $field = 'name') {

        $templ_file = '/structure/objects/'.$templ_name.'.tpl';
	    $TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
		    return page::errorNotFound('structure.filterABC', $templ_file);

        // Определяем текущий раздел
        $info = ormPages::getSectionByPath($section);

        $section_id = (is_a($info['section'], 'ormPage')) ? $info['section']->id : 'NULL';
        $class_name = (!empty($info['class'])) ? $info['class'] : '';
        $ind = (is_array($info['section'])) ? $info['section'] : $section_id;
        $prefix = md5($class_name.$ind);

        page::assign('section_id', $section_id);
        page::assign('class_name', $class_name);
        page::assign('target', $prefix);

        // Вывод Азбуки

        $this->checkFilterPost($field, 0, $prefix);
        $cur_symbol = (isset($_SESSION['filters'][$prefix][$field])) ? $_SESSION['filters'][$prefix][$field]['val'] : '';
        
        $list = '';
        $mas = lang::get('ALPHABET_'.$alphabet);
        foreach ($mas as $val) {
            page::assign('symbol', $val);
            $act = ($cur_symbol == $val) ? '_active' : '';
            $list .= page::parse($TEMPLATE['list'.$act]);
        }
        page::assign('list', $list);

        return page::parse($TEMPLATE['frame']);

    }

    public function filter($section, $templ_name = 'filter_default') {

        $templ_file = '/structure/objects/'.$templ_name.'.tpl';
	    $TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
		    return page::errorNotFound('structure.filter', $templ_file);

        // Определяем текущий раздел
        $info = ormPages::getSectionByPath($section);

        $section_id = ($info['section'] instanceof ormPage) ? $info['section']->id : 'NULL';
        $class_name = (!empty($info['class'])) ? $info['class'] : '';

        // Определяем общий ORM-класс для всех объектов данного раздела.
        if (empty($class_name) && ($section_id != 'NULL' || is_array($info['section']))) {
            $sel = new ormSelect();
            $sel->findInPages();
            
            if (is_array($info['section']))
                $sel->where('parents', '=', $info['section']);
            else
                $sel->where('parents', '=', $section_id);

            $class_name = $sel->getObjectsClass();
        }

        // Получаем список полей
        $fields = (!empty($class_name)) ? ormClasses::get($class_name)->loadFields() : array();

        $ind = (is_array($info['section'])) ? $info['section'] : $section_id;
        $prefix = md5($class_name.$ind);
        
        page::assign('section_id', $section_id);
        page::assign('class_name', $class_name);
        page::assign('target', $prefix);

        // Выводим список фильтров
	    $num = 0;
        $list = '';
        $all_count = count($fields);
	    while (list($fname, $field) = each($fields))
	        if ($field['f_filter'] && $field['f_type'] < 100){

                $this->checkFilterPost($fname, $field, $prefix);

                $num ++;

                page::assign('field.num', $num);
	            page::assign('class-first', ($num == 1) ? 'first' : '');
	            page::assign('class-last', ($num == $all_count) ? 'last' : '');
	            page::assign('class-odd', ($num % 2 == 0) ? 'odd' : '');
	            page::assign('class-even', ($num % 2 != 0) ? 'even' : '');
                page::assign('class-third', ($num % 3 == 0) ? 'third' : '');

                page::assign('field.id', $field['f_id']);
                page::assign('field.name', $field['f_name']);
                page::assign('field.sname', $field['f_sname']);

                $value = (isset($_SESSION['filters'][$prefix][$fname])) ? $_SESSION['filters'][$prefix][$fname] : '';
                $value2 = (isset($_SESSION['filters'][$prefix][$fname.'_2'])) ? $_SESSION['filters'][$prefix][$fname.'_2'] : '';
                page::assign('field.value', $value);

                if ($field['f_type'] == 90 || $field['f_type'] == 95) {

                    // Справочники
                    $data = ormObjects::getObjectsByClass($field['f_list_id']);
                    $list_vals = '';

                    if (isset($TEMPLATE['filter_relation_list_null'])) {
                        page::assign('obj.selected', ($value == 0) ? ' selected' : '');
                        $list_vals .= page::parse($TEMPLATE['filter_relation_list_null']);
                    }

                    while (list($k, $val) = each($data)) {

                        page::assign('obj.id', $val['id']);
                        page::assign('obj.name', $val['name']);
                        page::assign('obj.selected', ($value == $val['id']) ? ' selected' : '');

                        if (!empty($list_vals) && isset($TEMPLATE['filter_relation_separator']))
                            $list_vals .= page::parse($TEMPLATE['filter_relation_separator']);    

                        $list_vals .= page::parse($TEMPLATE['filter_relation_list']);
                    }

                    page::assign('list', $list_vals);
                    $filter = page::parse($TEMPLATE['filter_relation']);

                } else if ($field['f_type'] == 10 || $field['f_type'] == 15 || $field['f_type'] == 20 || $field['f_type'] == 30) {

                    // Текстовые поля
                    $filter = page::parse($TEMPLATE['filter_text']);

                } else if ($field['f_type'] == 25 || $field['f_type'] == 35) {

                    // Дата
                    page::assign('field.value1', $value);
                    page::assign('field.value2', $value2);
                    $filter = page::parse($TEMPLATE['filter_beetwen_date']);

                } else if ($field['f_type'] == 40 || $field['f_type'] == 45 || $field['f_type'] == 47) {

                    // Числа и цены
                    page::assign('field.value1', $value);
                    page::assign('field.value2', $value2);
                    $filter = page::parse($TEMPLATE['filter_beetwen_int']);

                } else {

                    // Все остальные, как логические Есть \ нет
                    page::assign('field.checked', (system::checkVar($value, isBool)) ? 'checked' : '');
                    $filter = page::parse($TEMPLATE['filter_boolean']);
                }

                page::assign('filter', $filter);

                if ($num > 1 && isset($TEMPLATE['separator']))
                    $list .= page::parse($TEMPLATE['separator']);

                $list .= page::parse($TEMPLATE['list']);
            }

        if (!empty($list)) {
            page::assign('list', $list);
            return page::parse($TEMPLATE['frame']);
        } else
            return page::parse($TEMPLATE['empty']);
    }

    // Запоминаем данные пришедшие через POST
    private function checkFilterPost($fname, $field, $prefix) {

        if (system::POST('target') == $prefix)

            if (isset($_POST[$fname])) {

                if (isset($_POST[$fname.'_FILTER_ABC'])) {

                    $value = array(
                        'val' => system::checkVar($_POST[$fname], isString),
                        'ABC' => true
                    );

                } else if ($_POST[$fname] == '' || (empty($_POST[$fname]) && ($field['f_type'] == 90 || $field['f_type'] == 95)))
                    $value = '';
                else if ($field['f_type'] == 50 || ($field['f_type'] > 69 && $field['f_type'] < 86))
                    $value = system::checkVar($_POST[$fname], isInt);
                else if ($field['f_type'] == 90 || $field['f_type'] == 95)
                    $value = system::checkVar($_POST[$fname], isInt);
                else
                    $value = system::checkVar($_POST[$fname], isString);

                $_SESSION['filters'][$prefix][$fname] = $value;

                if (isset($_POST[$fname.'2'])) {
                    if (empty($_POST[$fname.'2']))
                        $value2 = '';
                    else if ($field['f_type'] > 24 && $field['f_type'] < 33)
                        $value2 = system::checkVar($_POST[$fname.'2'], isString);
                    else
                        $value2 = system::checkVar($_POST[$fname.'2'], isInt);

                    $_SESSION['filters'][$prefix][$fname.'_2'] = $value2;
                }

            } else $_SESSION['filters'][$prefix][$fname] = '';

    }

    private function setFilters(ormSelect $sel, $section_id, $class_name) {

        $is_filtered = false;

        if (empty($class_name) && $section_id != 'NULL') {
            $sel_tmp = new ormSelect();
            $sel_tmp->findInPages();
            $sel_tmp->where('parents', '=', $section_id);
            $class_name = $sel_tmp->getObjectsClass();
        }
        $fields = (!empty($class_name) && ($class = ormClasses::get($class_name))) ? $class->loadFields() : array();

        $prefix = md5($class_name.$section_id);

        if (isset($_SESSION['filters'][$prefix]))

            while (list($fname, $field) = each($fields))

                if ($field['f_filter'] && $field['f_type'] < 100){

                    $this->checkFilterPost($fname, $field, $prefix);

                    // Устанавливаем уловия выборки в соотвествии с тем, что у нас лежит в сессии.
                    if (isset($_SESSION['filters'][$prefix][$fname])) {

                        if (isset($_SESSION['filters'][$prefix][$fname.'_2']))
                            $value2 = $_SESSION['filters'][$prefix][$fname.'_2'];
                        else if (isset($value2)) unset($value2);

                        $value = $_SESSION['filters'][$prefix][$fname];


                        // Устанавливаем на основе сохраненных данных фильтры
                        if ($value !== '' || (isset($value2) && $value2 !== '')) {


                            if (is_array($value) && isset($value['val'])) {

                                // Фильтр по первым буквам
                                $sel->where($fname, 'LIKE', $value['val'].'%');
                                $is_filtered = true;

                            } else if ($field['f_type'] == 50) {

                                // Галочка
                                if (!empty($value)){
                                    $value = ($value === 1) ? true : false;
                                    $sel->where($fname, '=', $value);
                                    $is_filtered = true;
                                }

                            } else if ($field['f_type'] > 69 && $field['f_type'] < 86) {

                                // Файлы
                                if ($value === 1) {
                                    $sel->where($fname, '<>', '');
                                    $is_filtered = true;
                                } else if ($value === 2) {
                                    $sel->where($fname, '=', '');
                                    $is_filtered = true;
                                }

                            } else if ($field['f_type'] == 90 || $field['f_type'] == 95) {

                                // Справочники
                                if (!empty($value)) {
                                    $sel->where($fname, '=', $value);
                                    $is_filtered = true;
                                }

                            } else if ($field['f_type'] == 25 || $field['f_type'] == 30 || $field['f_type'] == 32) {

                                // Фильтры по временным отрезкам

                                if ($field['f_type'] == 25) {

                                    // Дата
                                    if (!empty($value))
                                        $value = date('Y-m-d', strtotime($value));
                                    if (!empty($value2))
                                        $value2 = date('Y-m-d', strtotime($value2));

                                } else if ($field['f_type'] == 30) {

                                    // Время
                                    if (!empty($value))
                                        $value = date('H:i:s', strtotime($value));
                                    if (!empty($value2))
                                        $value2 = date('H:i:s', strtotime($value2));

                                } else if ($field['f_type'] == 32) {

                                    // Дата и Время
                                    if (!empty($value))
                                        $value = date('Y-m-d H:i:s', strtotime($value));
                                    if (!empty($value2))
                                        $value2 = date('Y-m-d H:i:s', strtotime($value2));
                                }

                                if (isset($value2)) {
                                    if ($value !== '' && $value2 === '')
                                        $sel->where($fname, '>=', $value);
                                    else if ($value === '' && $value2 !== '')
                                        $sel->where($fname, '<=', $value2);
                                    else if ($value !== '' && $value2 !== '')
                                        $sel->where($fname, 'BETWEEN', $value, $value2);
                                } else $sel->where($fname, '=', $value);
                                $is_filtered = true;

                            } else if (is_numeric($value) || (isset($value2) && is_numeric($value2))) {

                                // Числовые поля
                                if (isset($value2)) {
                                    if ($value !== '' && $value2 === '')
                                        $sel->where($fname, '>=', $value);
                                    else if ($value === '' && $value2 !== '')
                                        $sel->where($fname, '<=', $value2);
                                    else if ($value !== '' && $value2 !== '')
                                        $sel->where($fname, 'BETWEEN', $value, $value2);
                                } else $sel->where($fname, '=', $value);

                                $is_filtered = true;

                            } else if (!empty($value)){

                                // Текстовые поля
                                $sel->where($fname, 'LIKE', '%'.$value.'%');
                                $is_filtered = true;
                            }
                        }
                    }
                }

      //  print_R($_SESSION);

        return $is_filtered;
    }


	/**
	* @return HTML
	* @param int(string) $section - ID страницы сайта, подразделы которой будут выводиться в списке
						 или системное имя класса, объекты которого нужно вывести
	* @param string $templ_name - Шаблон оформления по которому будет строится список подразделов
	* @param int $max_count - Максимальное количество элементов в списке
	* @param string $order_by - Способ сортировки элементов списка. SQL-подобный синтаксис, например: "name DESC".
	* @param int $start_pos - Номер элемента по порядку с которого будет выводиться список.
    * @param string $field - Поле по которому будет установлено дополнительное условие для выборку.
    * @param mixed $value - Необходимое значение указанного поля. Проверяется на равенство.
	* @desc МАКРОС: Выводит список страниц из любого раздела сайта.
	*/
 	public function objList($section, $TEMPLATE = 'default', $max_count = 0, $order_by = 0, $start_pos = 0, $field = 0, $value = 0) {

        $list = '';

        // Определяем источник данных: ID, имя класса, путь, объект ormPage
        $independent = ($section instanceof ormPage) ? false : true;
        $class_name = $class_frame = '';

        if ($independent) {

            $info = ormPages::getSectionByPath($section);
            //print_r($info);
            
            if ($info['section'] === false)
                return '';

            if (!empty($info['class']))
                $class_name = $info['class'];

            if ($info['section'] instanceof ormPage) {
                $section = $info['section'];
                                
                if ($TEMPLATE == 'default' && $section->template2_id > 0)
        		    $TEMPLATE = templates::get($section->template2_id)->getFile();
                
            } else $section = $info['section'];
        }

        // Если нужно, подгружаем файл шаблона
        if (!is_array($TEMPLATE)) {
	        $templ_file = '/structure/objects/'.$TEMPLATE.'.tpl';
	        $TEMPLATE = page::getTemplate($templ_file);

		    if (!is_array($TEMPLATE))
		    	return page::errorNotFound('structure.objList', $templ_file);
        }

        // Формируем выборку объектов
	    $sel = new ormSelect($class_name);
	    $sel->findInPages();

	    if ($section instanceof ormPage) {

            page::assign('parent_id', $section->id);
            $section_id = $section->id;
            
            $sel->where('parents', '=', $section->id);
	        if (!$independent)
	        	$class_frame = $section->getClass()->getSName();
            
	 	} else {
            if ($section == 'root')
                $sel->where('parents', '=', 0);
            else if (is_array($section))
                $sel->where('parents', '=', $section, 'OR');

            page::assign('parent_id', 0);
            $section_id = 'NULL';
        }

        $sel->where('active', '=', 1);
        //$sel->where('view_in_menu', '=', 1);

		// Дополнительное пользовательское условие
		if (!empty($field)) {
			$field = explode(' ', trim($field));
			$value = explode(' ', trim($value));
			
			foreach ($field as $key => $val) {
				$sel->where($field[$key], '=', $value[$key]);
			}
		}

        $ind = (is_array($section)) ? $section : $section_id;

        $this->setFilters($sel, $ind, $class_name);

        // Сортировка списка
        $order_by = trim((empty($order_by) && ($section instanceof ormPage)) ? $section->order_by : $order_by);
        if (!empty($order_by)) {
            $pos = strpos($order_by, ' ');
            if ($pos) {
            	$parram = substr($order_by, $pos + 1);
            	$order_by = substr($order_by, 0, $pos);
            } else $parram = '';
        	$sel->orderBy($order_by, $parram);
        } else if ($section instanceof ormPage) $sel->orderBy(position, asc);

        $class_list = $sel->getClassesList();

        if (!empty($class_list)) {

	        // Узнаем какие поля объектов будут участвовать в выборке
	        $fields_str = '';
	        $fields = page::getFields('obj', $TEMPLATE, $class_list, $class_frame);
	        if (isset($fields['obj']))
		        while(list($key, $val) = each($fields['obj']))
		        	if ($val != 'url' && $val != 'class' && $val != 'num')
		        		$fields_str .= (empty($fields_str)) ? $val : ', '.$val;
	        $sel->fields($fields_str);

            // Количество элементов и постраничная навигация
            $max_count = (empty($max_count) && ($section instanceof ormPage)) ? $section->number_of_items : $max_count;
            if (!empty($max_count))
		        if (isset($fields['funct']) && in_array('structure.navigation', $fields['funct'])) {

			        $count_page = ceil($sel->getCount() / $max_count);
			        page::assign('count_page', $count_page);
			        if(system::getCurrentNavNum() != 0) {
			        	$niz = (empty($start_pos)) ? system::getCurrentNavNum() * $max_count - $max_count : $start_pos;
			        	$sel->limit($niz, $max_count);
			       	} else $sel->limit($max_count);

	            } else if (!empty($start_pos)) {
                	$sel->limit($start_pos, $max_count);
	            } else $sel->limit($max_count);

            // Формируем список
	        while($obj = $sel->getObject()) {

	            // Парсим поля страницы
		        if (isset($fields['obj_all'])) {
		            reset($fields['obj_all']);
		        	while(list($num, $name) = each($fields['obj_all']))
		            	page::assign('obj.'.$name, $obj->__get($name));
	            }

	            $class = $obj->getClass()->getSName();
	            $num = $sel->getObjectNum() + 1;
		       // $link = ($obj->other_link != '') ? $obj->other_link : $obj->url;
		        $target = ($obj->in_new_window) ? ' target="_blank"' : '';

                page::assign('obj.num', $num);
                page::assign('obj.target', $target);
                page::assign('obj.url', $obj->_url);
                page::assign('obj.class', $class);
	            page::assign('class-first', ($num == 1) ? 'first' : '');
	            page::assign('class-last', ($num == $sel->getObjectCount()) ? 'last' : '');
	            page::assign('class-odd', ($num % 2 == 0) ? 'odd' : '');
	            page::assign('class-even', ($num % 2 != 0) ? 'even' : '');
                page::assign('class-third', ($num % 3 == 0) ? 'third' : '');

                if ($num === 1)
                	page::assign('first_children_id', $obj->id);
                page::assign('last_children_id', $obj->id);


	            if (isset($TEMPLATE['list_'.$class]))
	            	$templ = 'list_'.$class;
	            else if (isset($TEMPLATE['list']))
            		$templ = 'list';
                else $templ = '';

                $act = (in_array($obj->id, ormPages::getActiveId()) && isset($TEMPLATE[$templ.'_active'])) ? '_active' : '';

	            if (isset($TEMPLATE[$templ.$act])) {
					if ($num > 1 && isset($TEMPLATE['separator']))
						$list .= $TEMPLATE['separator'];
				    $list .= page::parse($TEMPLATE[$templ.$act]);
	            }
	    	}
    	}

        if ($independent) {

            if (!empty($list)) {

                page::assign('list', $list);

                if (isset($TEMPLATE['frame_list']))
                    $list = page::parse($TEMPLATE['frame_list']);
                else
                    $list = page::errorBlock('structure.objList', $templ_file, 'frame_list');

            } else if(isset($TEMPLATE['empty']))
                $list = page::parse($TEMPLATE['empty']);
        }

    	return $list;
 	}

    /**
	* @return HTML
	* @param int(string) $section - ID страницы сайта, подразделы которой будут выводиться в списке
						 или системное имя класса, объекты которого нужно вывести
	* @param string $templ_name - Шаблон оформления по которому будет строится список подразделов
	* @param int $max_count - Максимальное количество элементов в списке
	* @param string $order_by - Способ сортировки элементов списка. SQL-подобный синтаксис, например: "name DESC".
	* @param int $start_pos - Номер элемента по порядку с которого будет выводиться список.
	* @desc МАКРОС: Выводит список страниц из любого раздела сайта.
	*/
 	public function objListByTags($section, $TEMPLATE = 'default', $max_count = 0, $order_by = 0, $start_pos = 0) {

        $list = '';

        // Определяем источник данных: ID, имя класса, путь, объект ormPage
        $independent = ($section instanceof ormPage) ? false : true;
        $class_name = $class_frame = '';

        if ($independent) {

            $info = ormPages::getSectionByPath($section);

            if ($info['section'] === false)
                return '';

            if (!empty($info['class']))
                $class_name = $info['class'];

            if ($info['section'] instanceof ormPage) {
                $section = $info['section'];

                if ($TEMPLATE == 'default' && $section->template2_id > 0)
        		    $TEMPLATE = templates::get($section->template2_id)->getFile();
            }
        }

        // Если нужно, подгружаем файл шаблона
        if (!is_array($TEMPLATE)) {
	        $templ_file = '/structure/objects/'.$TEMPLATE.'.tpl';
	        $TEMPLATE = page::getTemplate($templ_file);

		    if (!is_array($TEMPLATE))
		    	return page::errorNotFound('structure.objListByTags', $templ_file);
        }

        // Формируем выборку объектов
	    $sel = new ormSelect($class_name);
	    $sel->findInPages();
        $sel->where('active', '=', 1);
        $sel->where('id', '<>', $section->id);
        $sel->where('tags', '=', $section->tags, 'OR');

        // Сортировка списка
        $order_by = trim((empty($order_by) && ($section instanceof ormPage)) ? $section->order_by : $order_by);
        if (!empty($order_by)) {
            $pos = strpos($order_by, ' ');
            if ($pos) {
            	$parram = substr($order_by, $pos + 1);
            	$order_by = substr($order_by, 0, $pos);
            } else $parram = '';
        	$sel->orderBy($order_by, $parram);
        } else if ($section instanceof ormPage) $sel->orderBy(position, asc);

        $class_list = $sel->getClassesList();

        if (!empty($class_list)) {

	        // Узнаем какие поля объектов будут участвовать в выборке
	        $fields_str = '';
	        $fields = page::getFields('obj', $TEMPLATE, $class_list, $class_frame);
	        if (isset($fields['obj']))
		        while(list($key, $val) = each($fields['obj']))
		        	if ($val != 'url' && $val != 'class' && $val != 'num')
		        		$fields_str .= (empty($fields_str)) ? $val : ', '.$val;
	        $sel->fields($fields_str);

            // Количество элементов и постраничная навигация
            $max_count = (empty($max_count) && ($section instanceof ormPage)) ? $section->number_of_items : $max_count;
            if (!empty($max_count))
		        if (isset($fields['funct']) && in_array('structure.navigation', $fields['funct'])) {

			        $count_page = ceil($sel->getCount() / $max_count);
			        page::assign('count_page', $count_page);
			        if(system::getCurrentNavNum() != 0) {
			        	$niz = (empty($start_pos)) ? system::getCurrentNavNum() * $max_count - $max_count : $start_pos;
			        	$sel->limit($niz, $max_count);
			       	} else $sel->limit($max_count);

	            } else if (!empty($start_pos)) {
                	$sel->limit($start_pos, $max_count);
	            } else $sel->limit($max_count);

            // Формируем список
	        while($obj = $sel->getObject()) {

	            // Парсим поля страницы
		        if (isset($fields['obj_all'])) {
		            reset($fields['obj_all']);
		        	while(list($num, $name) = each($fields['obj_all']))
		            	page::assign('obj.'.$name, $obj->__get($name));
	            }

	            $class = $obj->getClass()->getSName();
	            $num = $sel->getObjectNum() + 1;
		       // $link = ($obj->other_link != '') ? $obj->other_link : $obj->url;
		        $target = ($obj->in_new_window) ? ' target="_blank"' : '';

                page::assign('obj.num', $num);
                page::assign('obj.target', $target);
                page::assign('obj.url', $obj->_url);
                page::assign('obj.class', $class);
	            page::assign('class-first', ($num == 1) ? 'first' : '');
	            page::assign('class-last', ($num == $sel->getObjectCount()) ? 'last' : '');
	            page::assign('class-odd', ($num % 2 == 0) ? 'odd' : '');
	            page::assign('class-even', ($num % 2 != 0) ? 'even' : '');
                page::assign('class-third', ($num % 3 == 0) ? 'third' : '');

                if ($obj->super)
                	page::assign('super', ' mainnews corners');
                else
                	page::assign('super', '');

                if ($num === 1)
                	page::assign('first_children_id', $obj->id);
                page::assign('last_children_id', $obj->id);


	            if (isset($TEMPLATE['list_'.$class]))
	            	$templ = 'list_'.$class;
	            else if (isset($TEMPLATE['list']))
            		$templ = 'list';
                else $templ = '';

                $act = (in_array($obj->id, ormPages::getActiveId()) && isset($TEMPLATE[$templ.'_active'])) ? '_active' : '';

	            if (isset($TEMPLATE[$templ.$act])) {
					if ($num > 1 && isset($TEMPLATE['separator']))
						$list .= $TEMPLATE['separator'];
				    $list .= page::parse($TEMPLATE[$templ.$act]);
	            }
	    	}
    	}

        if ($independent) {

            if (!empty($list)) {

                page::assign('list', $list);

                if (isset($TEMPLATE['frame_list']))
                    $list = page::parse($TEMPLATE['frame_list']);
                else
                    $list = page::errorBlock('structure.objListByTags', $templ_file, 'frame_list');

            } else if(isset($TEMPLATE['empty']))
                $list = page::parse($TEMPLATE['empty']);
        }

    	return $list;
 	}


    /**
	* @return HTML
	* @param string $field_name - Системное имя поля
	* @param int $obj_id - ID объекта
	* @param string $templ_block - Имя используемого блока в шаблоне оформления
	* @param string $templ_name - Имя файла шаблона оформления
	* @desc МАКРОС: Выводит значение поля в указанном оформлении
	*/
 	function getProperty($field_name, $obj_id, $templ_block = 0, $templ_name = '_properties') {

        $templ_file = '/structure/objects/'.$templ_name.'.tpl';
		$TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
			return page::errorNotFound('structure.getProperty', $templ_file);

        if (empty($templ_block) && isset($TEMPLATE[$field_name]))
        	$templ_block = $field_name;
        else if (!isset($TEMPLATE[$templ_block]))
        	$templ_block = 'default';

	    if (isset($TEMPLATE[$templ_block]) && $obj = ormPages::get($obj_id)) {

            $value2 = '';
            $value = $obj->__get($field_name);
            $field = $obj->getClass()->getField($field_name);

            if ($obj->getClass()->issetField($field_name)){

		    	if ($field->getType() < 91 && $field->getType() != 73) {

		    		if ($field->getType() == 90)
	                    // Тип выпадающий список
	                    $value2 = $obj->__get('_'.$field_name);
					else if ($field->getType() == 75 && !file_exists(ROOT_DIR.$value))
		    			// Тип изображение
		    			$value = '';

	            	page::assign('obj.id', $obj->id);
		            page::assign('obj.name', $obj->name);
		            page::assign('title', $obj->getClass()->getFieldName($field_name));

			    	if (!empty($value)) {

		                page::assign('value', $value);
		                page::assign('value_name', $value2);
		                page::assign('obj.'.$field_name, $value);
	                    page::assign('obj._'.$field_name, $value2);

			    	 	return page::parse($TEMPLATE[$templ_block]);

			    	} else if (isset($TEMPLATE[$templ_block.'_empty']))
			    		return page::parse($TEMPLATE[$templ_block.'_empty']);

                } else return page::error('structure.getProperty', $field_name, lang::get('ERROR_BAD_TYPE'));

            } else return page::error('structure.getProperty', $field_name, lang::get('ERROR_NOTFOUND_FIELD'));

	    }
	}


	/**
	* @return HTML
	* @param string $field_name - Системное имя поля для которого будет выводится список значений справочника
	* @param int $obj_id - ID объекта для которого формируется список
	* @param string $templ_name - Шаблон оформления списка, структура шаблона аналогична структуре макроса %structure.objList()%
	* @desc МАКРОС: Выводит список значений справочника, соотвествующих указанному объекту
	*/
 	public function getPropertyList($field_name, $obj_id, $templ_name = 'default') {

        $list = '';

        // подгружаем файл шаблона
	    $templ_file = '/structure/objects/'.$templ_name.'.tpl';
	    $TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
		    return page::errorNotFound('structure.getPropertyList', $templ_file);

        if ($page = ormPages::get($obj_id)) {

            if ($field = $page->getClass()->getField($field_name)) {

	       		if ($field->getType() == 95 || $field->getType() == 100) {

	                if ($curClass = ormClasses::get($field->getListId()))
	                    $class_name = $curClass->getSName();
                    else
                        $class_name = '';

		            $sel = new ormSelect($class_name);

		       		if ($field->getType() == 100 || $curClass->isPage())
					    $sel->findInPages();

					$sel->depends($obj_id, $field->id());
	                $class_list = $sel->getClassesList();
                    if (empty($class_list))
                        $class_list[] = '1';

			        //if (!empty($class_list)) {

				        // Узнаем какие поля объектов будут участвовать в выборке
				        $uri = false;
				        $fields_str = '';
				        $fields = page::getFields('obj', $TEMPLATE, $class_list);
				        if (isset($fields['obj']))
					        while(list($key, $val) = each($fields['obj']))
					        	if ($val != 'url' && $val != 'class' && $val != 'num')
					        		$fields_str .= (empty($fields_str)) ? $val : ', '.$val;
				        $sel->fields($fields_str);

                      // echo $fields_str;

	                    // Перебираем объекты
				        while($obj = $sel->getObject()) {

				            // Парсим поля страницы
					        if (isset($fields['obj_all'])) {
					            reset($fields['obj_all']);
					        	while(list($num, $name) = each($fields['obj_all']))
					            	page::assign('obj.'.$name, $obj->__get($name));
				            }

				            $num = $sel->getObjectNum() + 1;

                            if ($field->getType() == 100 || $curClass->isPage()) {
					            $target = ($obj->in_new_window) ? ' target="_blank"' : '';
                                page::assign('obj.target', $target);
			                    page::assign('obj.url', $obj->_url);
              				}

			                page::assign('obj.num', $num);
			                page::assign('obj.class', $class_name);
				            page::assign('class-first', ($num == 1) ? 'first' : '');
				            page::assign('class-last', ($num == $sel->getObjectCount()) ? 'last' : '');
				            page::assign('class-odd', ($num % 2 == 0) ? 'odd' : '');
				            page::assign('class-even', ($num % 2 != 0) ? 'even' : '');
                            page::assign('class-third', ($num % 3 == 0) ? 'third' : '');

			                if ($num === 1)
			                	page::assign('first_children_id', $obj->id);
			                page::assign('last_children_id', $obj->id);


				            if (isset($TEMPLATE['list_'.$class_name]))
				            	$templ = 'list_'.$class_name;
				            else if (isset($TEMPLATE['list']))
			            		$templ = 'list';
			                else $templ = '';

			                $act = '';//(isset($TEMPLATE[$templ.'_active']) && $obj->id == $act) ? '_active' : '';

			                if (isset($TEMPLATE[$templ.$act])) {
				    			if ($num > 1 && isset($TEMPLATE['separator']))
									$list .= $TEMPLATE['separator'];
				    			$list .= page::parse($TEMPLATE[$templ.$act]);
	                        }

				    	}
			    	//}

			    	if (!empty($list)) {

			            page::assign('list', $list);
			        	$list = page::parse($TEMPLATE['frame_list']);

			    	} else if (isset($TEMPLATE['empty']))
                        $list = page::parse($TEMPLATE['empty']);

				} else return page::error('structure.getPropertyList', $field_name, lang::get('ERROR_BAD_TYPE'));

			} else return page::error('structure.getPropertyList', $field_name, lang::get('ERROR_NOTFOUND_FIELD'));
    	}

    	return $list;
 	}



    /**
	* @return HTML
	* @param string $templ_name - Шаблон оформления.
	* @param int $start - Номер элемента от начала цепочки, с которого необходимо начать вывод.
	* @param int $stop - Номер элемента с конца цепочки, которым необходимо завершить вывод.
	* @desc МАКРОС: Вывод "хлебных крошек".
	*/
 	public function navibar($templ_name = 'default', $start = 0, $stop = 0) {

        $templ_file = '/structure/navibar/'.$templ_name.'.tpl';
        $TEMPLATE = page::getTemplate($templ_file);

	    if (!is_array($TEMPLATE))
	    	return page::errorNotFound('structure.navibar', $templ_file);
	    else {

            $pages = ormPages::getActiveId();
            $last = count($pages) - $stop;

            $list = '';
            for ($i = $start; $i < $last; $i++){

            	$page = ormPages::get($pages[$i]);

                $link = ($page->other_link != '') ? $page->other_link : $page->url;

                page::assign('id', $page->id);
                page::assign('name', $page->name);
                page::assign('url', $link);

                $act = ($page->id == ormPages::getCurPageId()) ? '' : '_active';
            	$list .= page::parse($TEMPLATE['list'.$act]);
            	$list .= ($last - 1 != $i) ? $TEMPLATE['separator'] : '';
            }

			if (!empty($list)) {
	            page::assign('list', $list);
	            $navbar = page::parse($TEMPLATE['frame']);
            } else $navbar = '';

        }

        return $navbar;
 	}


 	/**
	* @return HTML
	* @param int $count_page - Количество страниц которое необходимо отобразить. Если = 0, список не выведется.
	* @param int $smeshenie - Количество страниц выводящихся справа и слева от текущей.
	* @param string $templ_name - Шаблон оформления.
	* @desc МАКРОС: Вывод постраничной навигации.
	*/
    public function navigation($count_page = 0, $smeshenie = 4, $templ_name = 'default') {

        $navbar = '';
        $templ_file = '/structure/navigation/'.$templ_name.'.tpl';
        $TEMPLATE = page::getTemplate($templ_file);

	    if (!is_array($TEMPLATE))
	    	return page::errorNotFound('structure.navigation', $templ_file);
	    else {

	        $current_num = system::getCurrentNavNum();
            $current_url = system::getCurrentUrl();

			if ($count_page > 1) {

		        // Просчитывает какие страницы показывать
		        $raznica1 = $current_num - $smeshenie;
		        $raznica1 = ($raznica1 < 0) ? -$raznica1 : 0;

		        $raznica2 = $count_page - $current_num - $smeshenie;
		        $raznica2 = ($raznica2 < 0) ? -$raznica2 : 0;
		        $niz = $current_num - $smeshenie - $raznica2;

		        if ($niz < 1) $niz = 1;
		        $verx = $current_num + $smeshenie + $raznica1;
		        if ($verx > $count_page) $verx = $count_page;

		        page::assign('current_num', $current_num);
		        page::assign('count_page', $count_page);
		        page::assign('first_page', $current_url.'=1');
		        page::assign('previous_page', $current_url.'='.($current_num - 1));
		        page::assign('next_page', $current_url.'='.($current_num + 1));
		        page::assign('last_page', $current_url.'='.$count_page);

		        if (!empty($smeshenie)) {

                    // Определяемся с левым блоком
                    page::assign('num', $current_num - 1);
                    if ($niz !== 1 && isset($TEMPLATE['left_block']))
                        $left_block = page::parse($TEMPLATE['left_block']);
                    else
                        $left_block = '';
                    page::assign('left_block', $left_block);

                    // Определяемся с правым блоком
                    page::assign('num', $current_num + 1);
                    if ($verx != $count_page && isset($TEMPLATE['right_block']))
                        $right_block = page::parse($TEMPLATE['right_block']);
                    else
                        $right_block = '';
                    page::assign('right_block', $right_block);

                    // Вывод списка страниц
                    $pages = '';
                    for ($i = $niz; $i < $verx+1; $i++){

                         page::assign('page_num', $i);
                         page::assign('page_url', $current_url.'='.$i);
                         $tmpl = ($i == $current_num) ? 'list_active' : 'list';
                         $pages .= page::parse($TEMPLATE[$tmpl]);
                    }
                    page::assign('list', $pages);

                } else {

                    // Определяемся с левым блоком
                    page::assign('num', $current_num - 1);
                    if ($current_num - 1 > 0 && isset($TEMPLATE['left_block']))
                        $left_block = page::parse($TEMPLATE['left_block']);
                    else
                        $left_block = '';
                    page::assign('left_block', $left_block);

                    // Определяемся с правым блоком
                    page::assign('num', $current_num + 1);
                    if ($current_num + 1 <= $count_page && isset($TEMPLATE['right_block']))
                        $right_block = page::parse($TEMPLATE['right_block']);
                    else
                        $right_block = '';
                    page::assign('right_block', $right_block);


                    page::assign('page_num', $current_num);
                    page::assign('page_url', $current_url.'='.$current_num);
                    page::fParse('list', $TEMPLATE['list_active']);
                }

		        $navbar = page::parse($TEMPLATE['frame']);
	    	}
        }

        return $navbar;
	}


	/**
	* @return HTML
	* @param int $page_id - ID страницы относительно которой выводится
							следующая страница. Если 0, берется текущая страница.
	* @param string $templ_name - Имя шаблона оформления
	* @desc МАКРОС: Вернет ссылку для перехода к следующей странице.
	*/
	public function getNext($page_id = 0, $templ_name = 'default') {

    	$templ_file = '/structure/here_and_there/'.$templ_name.'.tpl';
        $TEMPLATE = page::getTemplate($templ_file);

	    if (!is_array($TEMPLATE))
	    	return page::errorNotFound('structure.navigation', $templ_file);

        if ($next = ormPages::getNext($page_id)) {

            page::assign('obj.id', $next->id);
            page::assign('obj.url', $next->_url);
            page::assign('obj.name', $next->name);

            return page::parse($TEMPLATE['next']);

        } else if (isset($TEMPLATE['next_empty']))
        	return page::parse($TEMPLATE['next_empty']);

 	}


 	/**
	* @return HTML
	* @param int $page_id - ID страницы относительно которой выводится
							предыдущая страница. Если 0, берется текущая страница.
	* @param string $templ_name - Имя шаблона оформления
	* @desc МАКРОС: Вернет ссылку для перехода к предыдущей странице.
	*/
	public function getPrevious($page_id = 0, $templ_name = 'default') {

    	$templ_file = '/structure/here_and_there/'.$templ_name.'.tpl';
        $TEMPLATE = page::getTemplate($templ_file);

	    if (!is_array($TEMPLATE))
	    	return page::errorNotFound('structure.navigation', $templ_file);

        if ($next = ormPages::getPrevious($page_id)) {

            page::assign('obj.id', $next->id);
            page::assign('obj.url', $next->_url);
            page::assign('obj.name', $next->name);

            return page::parse($TEMPLATE['previous']);

        } else if (isset($TEMPLATE['previous_empty']))
        	return page::parse($TEMPLATE['previous_empty']);

 	}



    /**
	* @return int - количество подразделов
	* @param int $section_id - ID раздела сайта.
    * @param string $class - Системное имя ORM-класса, страницы которого необходимо посчитать.
    * @param boolean $active - Если 0, считает все страницы в разделе. Если 1, только активные.
	* @desc МАКРОС: Возвращает количество подразделов у указаного раздела (страницы)
	*/
	public function objCount($section_id, $class = 0, $active = 0) {

        if ($active || !empty($class)) {
                        
            $sel = new ormSelect($class);
            $sel->where('parents', '=', $section_id);

            if ($active)
                $sel->where('active', '=', 1);
            
            return $sel->getCount();
            
        } else
            return ormPages::getCountOfSection($section_id);
 	}


    /**
	* @return string - URL указанного раздела сайта
	* @param int $section_id - ID раздела сайта.
	* @desc МАКРОС: Возвращает URL указанного раздела сайта, либо пустоту.
	*/
 	public function getObjURL($obj_id) {

        if (!is_numeric($obj_id)) {
            echo $obj_id;
            $info = ormPages::getSectionByPath($obj_id);

            if ($info['section'] === false)
                return '';

            if ($info['section'] instanceof ormObject)
                $obj_id = $info['section']->id;
        }

		$page = new ormPage($obj_id);
		return $page->__get('_url');
 	}


    /**
	* @return int - ID раздела сайта.
	* @param string $obj_url - URL раздела сайта.
	* @desc МАКРОС: Определяет ID раздела сайта, на основе указаного URL`a.
	*/
 	public function getObjID($obj_url) {
		return ormPages::getPageIdByUrl($obj_url);
 	}


    /**
	* @return HTML
	* @param int $obj_id_or_class - ID раздела сайта или системное название ORM-класса.
    * @param string $group_name - Название группы, поля которой необходимо вывести. Если 0, выводятся все видимые поля.
    * @param boolean $spec - Если 0, выводятся все видимые поля. Если 1, только поля помеченные галочкой "специальное".
    * @param string $templ_name - Шаблон оформления, по которому будет строится список полей
	* @desc МАКРОС: Вернет список полей (значений) раздела сайта в соответствующем оформлении
	*/
    public function fieldList($obj_id_or_class, $group_name = 0, $spec = 0, $templ_name = '_fields_list') {

        $templ_file = '/structure/objects/'.$templ_name.'.tpl';
	    $TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
		    return page::errorNotFound('structure.fieldList', $templ_file);

        $fields = $obj = '';
        
        // Определяем класс
        if (is_numeric($obj_id_or_class) && ($obj = ormPages::get($obj_id_or_class)))
            $class = $obj->getClass();
        else
            $class = ormClasses::get($obj_id_or_class);

        // Получаем список полей
        if ($class instanceof ormClass)
            $spec = (!empty($spec)) ? $class->loadSpecFields() : $class->loadFields();

        while(list($sname, $field) = each($spec))

            if (empty($group_name) || $group_name == $field['fg_sname']) {

                if (empty($field['f_type'])) {

                    // Разделители полей
                    page::assign('size', $field['f_max_size']);
                    page::assign('title', $field['f_name']);

                    if (!empty($field['f_name']) && isset($TEMPLATE['separator_text']))
                        $fields .= page::parse($TEMPLATE['separator_text']);
                    else
                        $fields .= page::parse($TEMPLATE['separator']);

                } else {

                    // Парсим поля страницы
                    page::assign('field.id', $field['f_id']);
                    page::assign('field.name', $field['f_name']);
                    page::assign('field.sname', $sname);

                    if ($obj instanceof ormObject) {
                        page::assign('field.value', $obj->__get($sname));
                        page::assign('field._value', $obj->__get('_'.$sname));
                    }

                    if (isset($TEMPLATE['field_'.$sname]))
                        $fields .= page::parse($TEMPLATE['field_'.$sname]);
                    else if ($field['f_type'] > 89 && $field['f_type'] < 101 && isset($TEMPLATE['field_list']))
                        $fields .= page::parse($TEMPLATE['field_list']);
                    else
                        $fields .= page::parse($TEMPLATE['field']);
                }
            }

        if (!empty($fields)) {
            page::assign('fields', $fields);
            return page::parse($TEMPLATE['frame']);
        }
    }


    /**
	* @return HTML
	* @param string $field_name - Системное имя поля
	* @param int $obj_id - ID объекта
	* @param string $templ_block - Имя используемого блока в шаблоне оформления
	* @param string $templ_name - Шаблон оформления
	* @desc МАКРОС: Выводит ссылку на файл, с подсчетом количества скачиваний
	*/
    public function getLinkCounter($field_name, $obj_id, $templ_block = 0, $templ_name = '_properties') {

        $templ_file = '/structure/objects/'.$templ_name.'.tpl';
		$TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
			return page::errorNotFound('structure.getLinkCounter', $templ_file);

        if (empty($templ_block) && isset($TEMPLATE[$field_name]))
        	$templ_block = $field_name;
        else if (!isset($TEMPLATE[$templ_block]))
        	$templ_block = 'default';

	    if (isset($TEMPLATE[$templ_block]) && ($obj = ormPages::get($obj_id))) {

            $value = $obj->__get($field_name);

            page::assign('obj.id', $obj->id);
		    page::assign('obj.name', $obj->name);

			if (!empty($value) && file_exists(ROOT_DIR.$value)) {

                $link = '/structure/link-counter/'.$obj->id.'/'.$field_name;
		        page::assign('value', $link);
		        page::assign('obj.'.$field_name, $link);

			    return page::parse($TEMPLATE[$templ_block]);

			} else if (isset($TEMPLATE[$templ_block.'_empty']))
			    return page::parse($TEMPLATE[$templ_block.'_empty']);
	    }
    }


    /**
     * @return HTML
     * @param string $templ_name - Шаблон оформления, по которому будет строится список RSS-лент
     * @desc МАКРОС: Выводит список RSS-лент.
     */
    public function rssList($templ_name = 'list') {

        $templ_file = '/structure/rss/'.$templ_name.'.tpl';
        $TEMPLATE = page::getTemplate($templ_file);

        if (!is_array($TEMPLATE))
            return page::errorNotFound('structure.rssList', $templ_file);

        // Формируем выборку объектов
        $sel = new ormSelect('news_feed');
        $sel->findInPages();
        $sel->where('rss_export', '=', 1);
        $sel->where('active', '=', 1);
        $sel->orderBy('name', asc);

        // Узнаем какие поля объектов будут участвовать в выборке
        $fields_str = '';
        $fields = page::getFields('obj', $TEMPLATE['list']);
        if (isset($fields['obj']))
            while(list($key, $val) = each($fields['obj']))
                if ($val != 'url' && $val != 'class' && $val != 'num' && $val != 'rss_url')
                    $fields_str .= (empty($fields_str)) ? $val : ', '.$val;
        $sel->fields($fields_str);

        // Формируем список
        $list = '';
        while($obj = $sel->getObject()) {

            // Парсим поля страницы
            if (isset($fields['obj'])) {
                reset($fields['obj']);
                while(list($num, $name) = each($fields['obj']))
                    page::assign('obj.'.$name, $obj->__get($name));
            }

            $class = $obj->getClass()->getSName();
            $num = $sel->getObjectNum() + 1;
            $target = ($obj->in_new_window) ? ' target="_blank"' : '';

            page::assign('obj.num', $num);
            page::assign('obj.target', $target);
            page::assign('obj.url', $obj->_url);
            page::assign('obj.rss_url', '/structure/rss/'.$obj->id);
            page::assign('obj.class', $class);
            page::assign('class-first', ($num == 1) ? 'first' : '');
            page::assign('class-last', ($num == $sel->getObjectCount()) ? 'last' : '');
            page::assign('class-odd', ($num % 2 == 0) ? 'odd' : '');
            page::assign('class-even', ($num % 2 != 0) ? 'even' : '');
            page::assign('class-third', ($num % 3 == 0) ? 'third' : '');

            if (isset($TEMPLATE['list_'.$class]))
                $templ = 'list_'.$class;
            else if (isset($TEMPLATE['list']))
                $templ = 'list';
            else $templ = '';

            if (isset($TEMPLATE[$templ])) {
                if ($num > 1 && isset($TEMPLATE['separator']))
                    $list .= $TEMPLATE['separator'];
                $list .= page::parse($TEMPLATE[$templ]);
            }
        }

        if (!empty($list)) {

            page::assign('list', $list);
            if (isset($TEMPLATE['frame_list']))
                $list = page::parse($TEMPLATE['frame_list']);
            else
                $list = page::errorBlock('structure.rssList', $templ_file, 'frame_list');

        } else if(isset($TEMPLATE['empty']))
            $list = page::parse($TEMPLATE['empty']);

        return $list;
    }


    /**
     * @return HTML
     * @param int $section_id - ID новостной ленты, для которой необходимо построить RSS-ленту
     * @param string $templ_name - Шаблон оформления, по которому будет строится RSS-лента
     * @desc МАКРОС: Выводит содержимое RSS-ленты.
     */
    public function rss($section_id, $templ_name = 'rss') {

        $templ_file = '/structure/rss/'.$templ_name.'.tpl';
        $TEMPLATE = page::getTemplate($templ_file);

        if (!is_array($TEMPLATE))
            return page::errorNotFound('structure.rss', $templ_file);


        $cur_domain = 'http://'.domains::curDomain()->getName();
        page::assign('channel.url', $cur_domain);
        page::assign('channel.date', date('r'));
        page::assign('channel.notice', '');

        // Определяем из каких категорий выводить ленту новостей
        $sections = array();
        if ($section_id == 'all') {

            // Из нескольких
            $sel = new ormSelect('news_feed');
            $sel->findInPages();
            $sel->fields('id');
            $sel->where('rss_export', '=', 1);
            $sel->where('active', '=', 1);

            while($obj = $sel->getObject())
                $sections[] = $obj->id;

            page::assign('channel.title', domains::curDomain()->getSiteName());

        } else {

            // Из одной
            $section_id = system::checkVar($section_id, isInt);
            if (empty($section_id)) return '';
            $sections[] = $section_id;

            if ($sect = ormPages::get($section_id))
                page::assign('channel.title', $sect->name);
        }

        // Формируем выборку объектов
        $sel = new ormSelect();
        $sel->findInPages();
        $sel->fields('name, notice, publ_date');
        $sel->where('parents', '=', $sections, 'OR');
        $sel->where('active', '=', 1);
        $sel->orderBy('publ_date', desc);
        $sel->limit(3);


        // Формируем список
        $list = '';
        while($obj = $sel->getObject()) {

            $num = $sel->getObjectNum() + 1;

            page::assign('obj.num', $num);
            page::assign('obj.id', $obj->id);
            page::assign('obj.url', $cur_domain.$obj->_url);
            page::assign('obj.name', $obj->name);

            $notice = str_replace('&nbsp;', '', strip_tags($obj->notice));
            page::assign('obj.notice', $notice);

            $date = date('r', strtotime($obj->publ_date));
            page::assign('obj.date', $date);

            /*
            if ($obj->podkast != '') {
                page::assign('obj.media_url', $cur_domain.$obj->podkast);
                page::fParse('media', $TEMPLATE['media']);
            } else
                page::assign('media', '');
            */

            if (isset($TEMPLATE['list']))
                $list .= page::parse($TEMPLATE['list']);
            else
                return page::errorBlock('structure.rss', $templ_file, 'list');
        }

        if (!empty($list)) {
            page::assign('list', $list);
            if (isset($TEMPLATE['frame_list']))
                $list = page::parse($TEMPLATE['frame_list']);
            else
                return page::errorBlock('structure.rss', $templ_file, 'frame_list');
        }

        return $list;
    }




}

?>