<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс позволяет автоматически создавать фильтры для выборки ORM-объектов.
*/

class ormFilterForm {

  	private $hide_filters = array();
   	private $show_filters = array();
    private $new_filters = array();
    private $filters = array();

    private $is_filtered = false;

    private $class;
    private $prefix = '';


    /**
 	* @return NULL
  	* @param ormSelect $select - Выборка данных для которой нужно построить фильтры
  	* @desc Конструктор.
 	*/
	public function __construct($select){
		if (is_a($select, 'ormSelect'))
			$this->select = $select;
	}

    // Вернет true, если хотябы один из фильтров применяется в данный момент
	public function isActive(){
		return $this->is_filtered;
	}

    public function setActive($bool){
		$this->is_filtered = $bool;
	}



	// Сброс всех фильтров
	public function clear(){
		$this->getFiltersList();
		$_SESSION['filters_'.$this->prefix] = '';
	}

    /*
    	Список полей фильтры для которых показывать не нужно. Список полей
    	указывается в качестве параметров через запятую.
    */
	public function hideFilters(){
	    $mas = func_get_args();
	    if (isset($mas[0]) && is_array($mas))
	    	$this->hide_filters = $mas[0];
	    else
			$this->hide_filters = $mas;
	}

    /**
 	* @return NULL
  	* @param String $field - Системное название поля по которому будет создан фильтр
  	* @param String $title - Название фильтра, если пусто используется стандартное название поля
  	* @param Bool $period - Если true, для фильтра (по числам или датам) можно указать область вхождения
  	* @desc Отображает фильтр для указанного поля
 	*/
	public function showFilter($field, $title = '', $period = false){
        $this->show_filters[$field] = array($title, $period);
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
		$this->new_filters[$position] = array($title, $html, $in_frame);
	}


    // Получаем список фильтров, для создания формы фильтрации
    private function getFiltersList() {

        if (is_a($this->select, 'ormSelect') && empty($this->filters)) {

            $class_name = $this->select->getObjectsClass();
            if (!empty($class_name))
	    		$fields = ormClasses::get($class_name)->loadFields();
            else
            	$fields = array();

			// Добавляем виртуальные поля
		    $bf['id'] = array('f_sname' => 'id', 'f_name' => lang::get('CONSTR_BASE_FIELD1'), 'f_filter' => false, 'f_type' => 40);
		    $bf['change_date'] = array('f_sname' => 'change_date', 'f_name' => lang::get('CONSTR_BASE_FIELD2'), 'f_filter' => false, 'f_type' => 32);
		    $bf['create_date'] = array('f_sname' => 'create_date', 'f_name' => lang::get('CONSTR_BASE_FIELD3'), 'f_filter' => false, 'f_type' => 32);
		    $fields = array_merge($bf, $fields);

		    // Строим список фильтров
	    	$this->filters = array();
	    	$names = '';
	    	while (list($key, $field) = each($fields))
	     		if (($field['f_filter'] || isset($this->show_filters[$field['f_sname']])) && !in_array($key, $this->hide_filters) && $field['f_type'] != 100){
	            	$this->filters[$key] = $field;
	            	$names .= $key;
                }

            // Вычисляем префикс для сессий фильтра
	    	$this->prefix = md5($names);
        }
    }

    // Получаем общее количество фильтров в выборке
    public function getCount(){
		$this->getFiltersList();
		return count($this->filters);
	}

    // Установка значений фильтра на основе пришедшего POST-запроса или Данных в сессии
	public function setFilters() {

		if ($this->getCount() > 0) {

            reset($this->filters);

    		while (list($fname, $field) = each($this->filters)) {

                if (isset($_SESSION['filters_'.$this->prefix][$fname])) {

                    // Запоминаем данные пришедшие через POST
                    if (isset($_POST['filter']) && isset($_POST[$fname])) {

	                    if ($_POST[$fname] == '' || (empty($_POST[$fname]) && ($field['f_type'] == 90 || $field['f_type'] == 95)))
	                    	$value = '';
	                   	else if ($field['f_type'] == 50 || ($field['f_type'] > 69 && $field['f_type'] < 86))
	                   		$value = system::checkVar($_POST[$fname], isInt);
	                  	else if ($field['f_type'] == 90 || $field['f_type'] == 95)
	                   		$value = system::checkVar($_POST[$fname], isInt);
	                   	else
                            $value = system::checkVar($_POST[$fname], isString);

	                   	$_SESSION['filters_'.$this->prefix][$fname] = $value;

	                   	if (isset($_POST[$fname.'2'])) {
		                   	if (empty($_POST[$fname.'2']))
		                    	$value2 = '';
		                   	else if ($field['f_type'] > 24 && $field['f_type'] < 33)
		                   		$value2 = system::checkVar($_POST[$fname.'2'], isString);
		                  	else
		                   		$value2 = system::checkVar($_POST[$fname.'2'], isInt);

		                   	$_SESSION['filters_'.$this->prefix][$fname.'_2'] = $value2;
	                   	}
                    }

					if (isset($_SESSION['filters_'.$this->prefix][$fname.'_2']))
						$value2 = $_SESSION['filters_'.$this->prefix][$fname.'_2'];
                    else if (isset($value2)) unset($value2);

                    $value = $_SESSION['filters_'.$this->prefix][$fname];


                    // Устанавливаем на основе сохраненных данных фильтры
                    if ($value !== '' || (isset($value2) && $value2 !== '')) {

	                    if ($field['f_type'] == 50) {

                            // Галочка
                            if (!empty($value)){
	                            $value = ($value === 1) ? true : false;
		                    	$this->select->where($fname, '=', $value);
		                    	$this->is_filtered = true;
                            }

	                    } else if ($field['f_type'] > 69 && $field['f_type'] < 86) {

                            // Файлы
                            if ($value === 1) {
	                    		$this->select->where($fname, '<>', '');
	                    		$this->is_filtered = true;
                            } else if ($value === 2) {
                            	$this->select->where($fname, '=', '');
                            	$this->is_filtered = true;
                            }

	                    } else if ($field['f_type'] == 90 || $field['f_type'] == 95) {

	                    	// Справочники
	                    	if (!empty($value)) {
                                
	                    		if ($field['f_relation'] == 2) {
                                    $parents[] = $value;
                                } else {
                                    $this->select->where($fname, '=', $value);
                                }

                                $this->is_filtered = true;
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
			                  		$this->select->where($fname, '>=', $value);
			                  	else if ($value === '' && $value2 !== '')
			                  		$this->select->where($fname, '<=', $value2);
			                  	else if ($value !== '' && $value2 !== '')
			                  		$this->select->where($fname, 'BETWEEN', $value, $value2);
	                  		} else $this->select->where($fname, '=', $value);
                            $this->is_filtered = true;

	                  	} else if (is_numeric($value) || (isset($value2) && is_numeric($value2))) {

	                  		// Числовые поля
		                  	if (isset($value2)) {
			                  	if ($value !== '' && $value2 === '')
			                  		$this->select->where($fname, '>=', $value);
			                  	else if ($value === '' && $value2 !== '')
			                  		$this->select->where($fname, '<=', $value2);
			                  	else if ($value !== '' && $value2 !== '')
			                  		$this->select->where($fname, 'BETWEEN', $value, $value2);
	                  		} else $this->select->where($fname, '=', $value);

                            $this->is_filtered = true;

	                  	} else if (!empty($value)){

	                  		// Текстовые поля
	                  		$this->select->where($fname, 'LIKE', '%'.$value.'%');
	                  		$this->is_filtered = true;
                        }
                    }
                }
      		}
		}
    }

    // Вывод списка фильтров
	public function getHTML($templ_name = 'filters') {

		if ($this->getCount() > 0 && file_exists(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl')) {

            include(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl');
            $items = '';
            $num = 0;

            // Строим список фильтров
            reset($this->filters);
    		while (list($key, $field) = each($this->filters)){

      			$num ++;

         		if (isset($this->new_filters[$num])) {
                   	if ($this->new_filters[$num][2]) {
                    	page::assign('field.name', $this->new_filters[$num][0]);
	      				page::assign('field.content', $this->new_filters[$num][1]);
	      				$items .= page::parse($TEMPLATE['field_spec']);
      				} else $items .= $this->new_filters[$num][1];
          		}

            	if (!isset($_SESSION['filters_'.$this->prefix][$field['f_sname']]))
                   	$_SESSION['filters_'.$this->prefix][$field['f_sname']] = '';

               	if (isset($this->show_filters[$field['f_sname']]) && !empty($this->show_filters[$field['f_sname']][0]))
               		$field['f_name'] = $this->show_filters[$field['f_sname']][0];

                page::assign('field.name', $field['f_name']);

      			if ($field['f_type'] == 50 || ($field['f_type'] > 69 && $field['f_type'] < 96)) {

         			// Выпадающий список
            		$data = array();

      			    if ($field['f_type'] == 50) {
      			    	$data = lang::get('FILTER_BOOL');
      			    } else if ($field['f_type'] > 69 && $field['f_type'] < 86) {
      			    	$data = lang::get('FILTER_ISSET');
      			    } else if ($field['f_type'] == 90 || $field['f_type'] == 95) {
      			    	$data = ormObjects::getObjectsByClass($field['f_list_id']);
      			    }

      				$element = ui::SelectBox(
      					$field['f_sname'],
      					$data,
      					$_SESSION['filters_'.$this->prefix][$field['f_sname']],
      					172,
      					'&nbsp;',
      					'',
      					'selectbox_mini'
      				);
      				page::assign('element', $element);
      				$items .= page::parse($TEMPLATE['field_select']);

      			} else if (
	      				(isset($this->show_filters[$field['f_sname']]) && $this->show_filters[$field['f_sname']][1]) ||
	      				(
	      					($field['f_type'] == 25 || $field['f_type'] == 30 || $field['f_type'] == 32) &&
	      					(!isset($this->show_filters[$field['f_sname']]) || $this->show_filters[$field['f_sname']][1] !== false)
	      				)
      				){

          			// Выбор промежутка для чисел или дат

             		if (!isset($_SESSION['filters_'.$this->prefix][$field['f_sname'].'_2']))
                   		$_SESSION['filters_'.$this->prefix][$field['f_sname'].'_2'] = '';

      				page::assign('field.sname', $field['f_sname']);
      				page::assign('field.value', $_SESSION['filters_'.$this->prefix][$field['f_sname']]);
      				page::assign('field.value2', $_SESSION['filters_'.$this->prefix][$field['f_sname'].'_2']);
      				$items .= page::parse($TEMPLATE['field_period']);

      			} else {

                    // Обычный фильтр - текстовое поле
      				page::assign('field.sname', $field['f_sname']);
      				page::assign('field.value', $_SESSION['filters_'.$this->prefix][$field['f_sname']]);
      				$items .= page::parse($TEMPLATE['field']);
      			}
      		}

            $all_count = $num + count($this->new_filters);
            while ($num < $all_count) {
                $num++;
         		if (isset($this->new_filters[$num])) {
                   	if ($this->new_filters[$num][2]) {
                    	page::assign('field.name', $this->new_filters[$num][0]);
	      				page::assign('field.content', $this->new_filters[$num][1]);
	      				$items .= page::parse($TEMPLATE['field_spec']);
      				} else $items .= $this->new_filters[$num][1];
          		}
            }

      		page::assign('fields', $items);
      		return page::parse($TEMPLATE['frame']);
		}
    }
}

?>