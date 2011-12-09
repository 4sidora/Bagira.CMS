<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс позволяет создавать мультиформы для редактирования ORM-объектов.
	Мультиформы - это формы для редактирования сразу нескольких ORM-объектов.
	Своеобразный сплав обычной формы и таблицы.
*/

class ormMultiForm extends uiTableFunctions {

	private $select, $right, $form_name;
    private $java, $requred_field, $addit_function;
	private $more_param = array();
	private $columns = array();

	private $without_add = false;
	private $without_del = false;
	private $inside_form = false;
    private $showID = false;

	private $width = 200;             // Ширина столбцов по умолчанию


    // Создает форму с указанным именем
    public function __construct($form_name = '') {
    	$this->form_name = $form_name;
	}

	/**
 	* @return NULL
  	* @param String $field - Системное имя свойства ORM-объекта, содержимое которого нужно вывести.
    * @param String $title - Название свойства, если не указано используется название по умолчанию.
    * @param String $width - Ширина столбца.
    * @param String $hint - Подсказка для столбца.
    * @param String $editable - Если TRUE, столбец редактируемый.
    * @param String $funct_name - Имя php-функции для обработки значений столбца
  	* @desc Добавляет столбец / поле формы
 	*/
    public function addColumn($field, $title = 0, $width = 0, $hint = 0, $editable = true, $funct_name = '') {
    	$this->columns[$field] = array(
    		'width' => $width,
    		'f_name' => $title,
    		'editable' => $editable,
    		'funct_name' => $funct_name,
    		'f_hint' => $hint
    	);
    }

    public function showColumnID() {
        $this->showID = true;
    }

	// Прикрепляет указанный JavaScript файл к форме
    public function attachJavaScript($file) {
    	$this->javascript[] = $file;
    }

    // Устанавливает выборку объектов (источник данных объект ormSelect)
    public function setData($select) {
    	$this->select = $select;
	}

    // Устанавливает право для обработки формы
	public function setRight($right) {
        $this->right = $right;
	}

    // Устанавливает дополнительные параметры, передаваемые обработчику формы
	public function moreParam() {
        $this->more_param = func_get_args();
	}

    /**
    * @return string
    * @param integer $num - Порядковый номер параметра, если не указан метод вернет список параметров в виде массива.
    * @desc Вернет дополнительные параметры переданные в POST. Метод используется обработчиком формы.
    */
	public function getParam($num = -1) {
        if (isset($_POST['params_'.$this->form_name]))
        	if ($num > -1 && isset($_POST['params_'.$this->form_name][$num]))
        	    return $_POST['params_'.$this->form_name][$num];
        	else
        		return $_POST['params_'.$this->form_name];
 		else return '';
	}

    // Запрещает добавление объектов через форму
	public function withoutAdditions() {
    	$this->without_add = true;
	}

    // Запрещает удаление объектов через форму
	public function withoutRemoving() {
    	$this->without_del = true;
	}

	// Форма не является самостоятельной. Если метод вызван, элементы формы не обрамляются тегом <form>.
    public function insideForm() {
    	$this->inside_form = true;
	}

	// Основной метод - Генерация формы редактирования объекта
	public function getHTML($templ_name = 'orm_multi_form') {

		if (file_exists(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl')){

   			include(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl');

            // Вывод столбцов
            $class = ormClasses::get($this->select->getObjectsClass());
            $fields = $class->loadFields();

            if (isset($this->columns['id'])) {
                $fields['id'] = array(
                    'f_sname' => 'id',
                    'f_type' => '0'
                );
            }

            if ($this->showID) {

                $idWidth = 50;
                $field['f_name'] = '#';
                $field['f_sname'] = 'id';
                $field['editable'] = false;
                $field['width'] = $idWidth;
                $field['f_view'] = true;
                $field['f_type'] = 10;

                $fields['id'] = $field;

            } else
                $idWidth = 0;


            //page::assign('width', $this->width);
            //page::assign('width_plu', $this->width-4);
            page::assign('mclass', $class->id());
            page::assign('mright', $this->right);
            page::assign('form_name', $this->form_name);


            // Вывод столбцов
	        $columns = '';
            if (!empty($this->columns)) {

                reset($this->columns);
                while(list($name, $column) = each($this->columns)) {
                	if (isset($fields[$name])) {

	                	if (!empty($column['f_name']))
	                		$fields[$name]['f_name'] = $column['f_name'];

	                	if (!empty($column['f_hint']))
	                		$fields[$name]['f_hint'] = $column['f_hint'];

	                	if (!empty($column['width']))
	                		$fields[$name]['width'] = $column['width'];

	                	$columns .= $this->getColumn($fields[$name], $TEMPLATE);
	              	}
                }

            } else {

                // Выводим все видимые поля объекта
               
                $count = 0;

                while(list($key, $field) = each($fields))
	                if ($field['f_view'] && $field['f_type'] != 97 && $key != 'id')
                        $count ++;

                $this->width = ceil((850 - $idWidth) / $count);

                reset($fields);
	            while(list($key, $field) = each($fields))
	                if ($field['f_view'] && $field['f_type'] != 97) 
	                	$columns .= $this->getColumn($field, $TEMPLATE);

            }
            page::assign('columns', $columns);

            $column_del = ($this->without_del) ? '' : $TEMPLATE['colum_del'];
            page::assign('column_del', $column_del);



            // Вывод форм редактрования для объектов
            $lines = '';
            while($obj = $this->select->getObject())
            	$lines .= $this->parseLineForm($obj, $fields, $TEMPLATE);

            // Выводим форму добавления нового объекта
            if (!$this->without_add) {
                $obj = new ormObject();
                $obj->setClass($this->select->getObjectsClass());
				$lines .= $this->parseLineForm($obj, $fields, $TEMPLATE, 1);
            }

            page::assign('java_rules', $this->java);
            page::assign('addit_function', $this->addit_function);

            page::assign('requred_field', $this->requred_field);
	        page::assign('lines', $lines);

            // Дополнительные параметры
            $params = '';
            while (list($key, $val) = each($this->more_param)) {
               	page::assign('val', $val);
               	$params .= page::parse($TEMPLATE['params']);
            }
            page::assign('params', $params);

            if (isset($_SESSION['SAVING_POST']['obj'.$this->form_name]))
                unset($_SESSION['SAVING_POST']['obj'.$this->form_name]);

            // Парсим все что получилось в единую форму
            if (!$this->inside_form){
                page::fParse('html', $TEMPLATE['frame']);
            	return page::parse($TEMPLATE['form_frame']);
            } else return page::parse($TEMPLATE['frame']);
   		}
	}


	private function getColumn($field, $TEMPLATE){

	 	$width = (!empty($field['width'])) ? $field['width'] : $this->width;
	 	page::assign('width', $width + 11);

   		$title = $field['f_name'];
     	if (!empty($field['f_hint'])) {
      		page::assign('hint', $field['f_hint']);
            page::assign('title', $title);
            $title = page::parse($TEMPLATE['acronym']);
        }
        if (!empty($field['f_required'])) $title .= ' *';

        page::assign('title', $title);
        return page::parse($TEMPLATE['colums']);
	}

	// Создает поле
	private function parseLineForm($obj, $fields, $TEMPLATE, $new_num = 0) {

		$flist = '';

        $is_upd = ($obj->id == '') ? false : true;

        $current_id = ($obj->id == '') ? 'new'.$new_num : $obj->id;
		page::assign('obj_id', $current_id);

    	$del_check = ($this->without_del || !$is_upd) ? '' : $TEMPLATE['del_check'];
     	page::fParse('del_check', $del_check);

        page::assign('new', (($new_num) ?  ' new' : '' ));

    	if (!empty($this->columns)) {

            reset($this->columns);
     		while(list($name, $column) = each($this->columns)) {
                if (isset($fields[$name])) {
                	$fields[$name]['width'] = $column['width'];
                	$fields[$name]['editable'] = $column['editable'];
                	$fields[$name]['funct_name'] = $column['funct_name'];
                	$flist .= $this->parseField($obj, $fields[$name], $TEMPLATE, $current_id);
	            }
       		}

        } else {

        	reset($fields);
	        while(list($key, $field) = each($fields)) {
		    	if ($field['f_view'] && $field['f_type'] != 97) {
		        	$flist .= $this->parseField($obj, $field, $TEMPLATE, $current_id);
		    	}
	    	}
        }

     	page::assign('object', $flist);

      	return page::parse($TEMPLATE['lines']);
    }


    // Парсим поле
	private function parseField($obj, $field, $TEMPLATE, $current_id) {

        $is_upd = ($obj->id == '') ? false : true;
        $is_editable = (!isset($field['editable']) || $field['editable']);
		$width = (empty($field['width'])) ? $this->width : $field['width'];
		page::assign('width', $width);

		if (isset($_SESSION['SAVING_POST']['obj'.$this->form_name]) && ($field['f_type'] < 70 || $field['f_type'] > 85)) {

            $tmp = $_SESSION['SAVING_POST']['obj'.$this->form_name];
            if (isset($tmp[$current_id][$field['f_sname']]))
            	$value = $tmp[$current_id][$field['f_sname']];

  		} else $value = '';

	    if (empty($value)) {

            if ($field['f_type'] == 105)
                $value = $obj->__get('_'.$field['f_sname']);
            else 
                $value = $obj->__get($field['f_sname']);

			$function = (!empty($field['funct_name'])) ? $field['funct_name'] : '';
            
			if (!$is_editable)
				$value = parent::processValue($obj, $field['f_sname'], $field['f_type'], $value, $function);
        }

		if ($is_editable){

			page::assign('field.id', $field['f_id']);
			page::assign('field.name', $field['f_name']);
			page::assign('field.sname', $field['f_sname']);
			page::assign('field.value', $value);

			$obj_id = $field['f_sname'].'_'.$current_id;
			$elem_name = 'obj'.$this->form_name.'['.$current_id.']['.$field['f_sname'].']';

	  		// Специальная обработка данных
			if ($field['f_type'] == 50) {

				page::assign('element', ui::CheckBox($elem_name, 1, $value, '', '', $obj_id));

			} else if ($field['f_type'] == 32) {

			    if (empty($value) || $value == '0000-00-00 00:00:00') {
			    	page::assign('field.date', '');
					page::assign('field.time', '00:00');
			    } else {
					page::assign('field.date', date('d.m.Y', strtotime($value)));
					page::assign('field.time', date('H:i', strtotime($value)));
				}

			} else if ($field['f_type'] == 25) {

			    $time = (empty($value) || $value == '0000-00-00') ? '' : date('d.m.Y', strtotime($value));
				page::assign('field.date', $time);

			} else if ($field['f_type'] == 30) {

			    $time = (empty($value)) ? '' : date('H:i:s', strtotime($value));
				page::assign('field.time', $time);

            } else if ($field['f_type'] > 69 && $field['f_type'] < 86) {

                // файлы
                if (system::$isAdmin)
                    page::assign('element', ui::loadFile($elem_name, $value, 'load_file_mini', $obj_id));
                else
                    page::fParse('element', '');

			} else if ($field['f_type'] == 90 || $field['f_type'] == 95) {

	  			// Выпадающий список или Список со множественным выбором
	     		// Получаем список объектов справочника
	       		$data = ormObjects::getObjectsByClass($field['f_list_id']);
	            $multi = ($field['f_type'] == 95) ? 'selectbox_multi' : 'selectbox_template';
	            $empty = (!$field['f_required']) ? '&nbsp;' : '';

	            if ($field['f_quick_add']) $width = $width - 22;
	            $min = ($field['f_type'] == 95) ? 8 : 30;
	            page::assign('width_plu', $width - $min);

                page::assign('element', ui::SelectBox($elem_name, $data, $value, $width, $empty, '', $multi, $obj_id));

				if ($field['f_quick_add'])
	   				page::fParse('plus', $TEMPLATE['field_'.$field['f_type'].'_plus']);
	   			else
	      			page::assign('plus', '');

                
			} else if ($field['f_type'] == 100) {

                // Связь с объектом
                page::assign('element', ui::objectLinks($obj, $field['f_id'], '_'.$current_id, $elem_name, $width - 30, 'objectLinks2'));

		    }

			// Генерируем яву (создаем правила проверки для элементов формы)
	  		if ($field['f_required'] && (($field['f_type'] == 35 && $this->action == 'add') || $field['f_type'] != 35)){

	  			$msg = str_replace('%title%', $field['f_name'], lang::get('CONSTR_BASE_FIELD_E2'));

	  			$zpt = (!empty($this->requred_field)) ? ', ' : '';
	  			$empty = ($field['f_type'] == 90 || $field['f_type'] == 95) ? 'null' : '""';

                if ($field['f_type'] == 100) {

                    $this->requred_field .= $zpt.'["'.$obj_id.'", "'.$current_id.'", "'.$msg.'", '.$empty.', 1]';

                } else 
                      $this->requred_field .= $zpt.'["'.$obj_id.'", "'.$current_id.'", "'.$msg.'", '.$empty.']';

	   		}

	  		if ($field['f_type'] == 15)
	      		$this->java .= page::parse('rules.push("valid_email,'.$obj_id.','.lang::get('CONSTR_BASE_FIELD_E3').'");');

            if ($field['f_type'] == 20)
                $this->java .= page::parse('rules.push("reg_exp,'.$obj_id.',^(((f|ht){1}tp:/)*/[-a-zA-Z0-9@:%_\+.~#?&//=]+)*$, '.lang::get('CONSTR_BASE_FIELD_E4').'");');

            if ($field['f_type'] == 40)
                $this->java .= page::parse('rules.push("digits_only,'.$obj_id.','.lang::get('CONSTR_BASE_FIELD_E6').'");');

            if ($field['f_type'] == 45)
                $this->java .= page::parse('rules.push("float_only,'.$obj_id.','.lang::get('CONSTR_BASE_FIELD_E7').'");');

            if ($field['f_type'] == 47)
                $this->java .= page::parse('rules.push("price_only,'.$obj_id.','.lang::get('CONSTR_BASE_FIELD_E8').'");');


            if (isset($TEMPLATE['field_'.$field['f_type']]))
	       		$value = page::parse($TEMPLATE['field_'.$field['f_type']]);
	       	else
	       		$value = str_replace('%title%', $field['f_sname'], lang::get('CONSTR_BASE_FIELD_E5'));
       	}

        page::assign('content', $value);
       	return page::parse($TEMPLATE['field_frame']);
	}


    // ***********************************************************************************************************

    /**
    * @return null
    * @param string $callback - Имя php-функции для обработки добавления/изменения объектов.
    * @desc Сохраняет все пришедшие данные. Метод используется обработчиком формы.
    */
	public function process($callback = '', $addit_parram = '') {

 		if (isset($_POST['obj'.$this->form_name]) && isset($_POST['class_'.$this->form_name])) {

 		    $class = ormClasses::get($_POST['class_'.$this->form_name]);
    		$mas = $class->loadFields();

    		while(list($id, $fields) = each($_POST['obj'.$this->form_name])) {

                $keys = array_keys($fields);
                if (is_numeric($id)){
                	$obj = ormObjects::get($id);
                } else if (!$this->without_add && !empty($fields[$keys[0]])) {
	                $obj = new ormObject();
	                $obj->setClass($class->getSName());
                }

                if (isset($obj) && is_a($obj, 'ormObject'))
	            	if (!$this->without_del && isset($_POST['delete_'.$obj->id])) {

	            		$obj->toTrash();

	            	} else {

	                    reset($mas);
		            	while(list($key, $f_val) = each($mas)) {

                            if (($f_val['f_type'] > 89 && $f_val['f_type'] < 101) && $f_val['f_relation'] == 2 && isset($fields[$key])) {

                                // Справочник с типом "Выбор родителя"
                                $obj->clearParents();
                                $ps = $obj->getParents();
                                $parents = $fields[$key];

                                if (!empty($parents)) {
                                    if (is_numeric($parents) && !empty($parents)) {

                                        $pos = (isset($ps[$parents])) ? $ps[$parents]['position'] : 0;
                                        $obj->setNewParent($parents, $pos);

                                    } else if (is_array($parents)) {

                                        while(list($key, $val) = each($parents))
                                            if (!empty($val)) {
                                                $pos = (isset($ps[$val])) ? $ps[$val]['position'] : 0;
                                                $obj->setNewParent($val, $pos);
                                            }
                                    }
                                }

                            } else if ($f_val['f_type'] == 50 && !isset($fields[$key])) {
                             
                                // Галочка
                                $obj->__set($key, false);

                            } else if (isset($fields[$key])) {


                                // Дополнительная проверка для файловых полей
                                if ($f_val['f_type'] > 69 && $f_val['f_type'] < 86)

                                    if (!empty($_FILES['file_obj'.$this->form_name]['tmp_name'][$id][$key])) {

                                        // Создаем переменную обманку, чтобы объект сам обработал файл
                                        
                                        $file = array(
                                            'name' => $_FILES['file_obj'.$this->form_name]['name'][$id][$key],
                                            'type' => $_FILES['file_obj'.$this->form_name]['type'][$id][$key],
                                            'tmp_name' => $_FILES['file_obj'.$this->form_name]['tmp_name'][$id][$key],
                                            'error' => $_FILES['file_obj'.$this->form_name]['error'][$id][$key],
                                            'size' => $_FILES['file_obj'.$this->form_name]['size'][$id][$key],
                                        );

                                        $_FILES['file_'.$key] = $file;
                                    }


		                		$obj->__set($key, $fields[$key]);

		                	} else if (!empty($fields[$key.'_date']) && isset($fields[$key.'_time'])) {

		                	    $datetime = $fields[$key.'_date'].' '.$fields[$key.'_time'].':00';     
	                            $obj->__set($key, $datetime);
		                	}
	                    }

                        if (!empty($callback) && function_exists($callback))
                        	$is_ok = call_user_func($callback, $obj, $addit_parram);
                        else $is_ok = true;

                        if ($is_ok)
		            		$is_ok = $obj->save();

		            	if ($is_ok === false) {
		            	    // echo $obj->getErrorListText();
				            system::savePostToSession();
					    	ui::MessageBox(lang::get('TEXT_MESSAGE_ERROR'), $obj->getErrorListText());
					    	//ui::selectErrorFields($obj->getErrorFields());
					 	}

					 	unset($obj);
	            	}
            }
      	}
	}

}

?>
