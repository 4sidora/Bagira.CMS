<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс позволяет автоматически создавать формы редактирования на основе
	ORM-объектов.
*/

class ormEditForm {

	private $obj, $right;
    private $java = '';
    private $odd_field = true;
    private $captcha = false;
    private $class_list = '';
    private $without_sh = false;
    private $without_tabs = false;

    private $javascript = array();
	private $tabs = array();
	private $tabs_addit = array();
	private $fields = array();
	private $new_fields = array();
    private $tabu_list = array();
    private $allowed_list = array();
    private $read_only = array();

    private $addit_function = '';

    // Поля которые можно показать\скрыть для объектов ormPage
	private $page_fields = array('title', 'h1', 'keywords', 'description', 'tags');   //  'pseudo_url',


    // Отключает вывод вкладок
    public function addRuleJS($rule) {
    	$this->java[] = $rule;
    }


    // Отключает вывод вкладок
    public function withoutTabs() {
    	$this->without_tabs = true;
    }

    // Принудительно убирает элемент "Срыть\показать доп. поля" для страниц
    public function withoutSH() {
    	$this->without_sh = true;
    }

    // Поля которые доступны только в режиме чтения
    public function readOnly() {
    	$this->read_only = func_get_args();
    }

    // Запрещает вывод указанных полей в форме
    public function tabuList() {
    	$this->tabu_list = func_get_args();
    }

    // Устанавливаем список полей для вывода в форму
    // Запрещает вывод не указанных полей
    public function allowedList() {
    	$this->allowed_list = func_get_args();
    }

	// Покажет каптчу, ввод обязателен
    public function showCaptcha() {
    	$this->captcha = true;
    }

    /**
	* @return null
	* @param ormObject $object -  экземпляр ORM-объекта
	* @param string $right - название обработчика панели администрирования
	* @desc Конструктор
	*/
    public function __construct($object, $right) {
    	$this->obj = $object;
        $this->right = $right;
	}

    /**
	* @return null
	* @param string $name - Системное имя поля
	* @param string $html - HTML для замены
	* @param boolean $all - Если TRUE, $html вставляется без стандартного оформления
	* @desc Заменяет отображение стандартного поля на указанный html
	*/
	public function replaceField($name, $html, $all = false) {
    	$this->fields[$name] = array($html, $all);
	}


    /**
	* @return NULL
	* @param String $tab_name - Системное имя вкладки в которую добавляют поле
	* @param Int $position - Позиция поля
	* @param String $sname - Системное имя поля
	* @param String $title - Заголовок поля
	* @param String $html - HTML поля
	* @param String $hint - Подсказка для поля
	* @param Bool $required - Если поле обязательно для заполнения, укажите TRUE
	* @param Bool $all - Если TRUE, поле вставляется без стандартного оформления
	* @desc Добавляет поле на указанную вкладку, в указанную позицию
	*/
    public function addField($tab_name, $position, $sname, $title, $html, $hint = '', $required = false, $all = false) {
    	$this->new_fields[$tab_name][$position] = array($sname, $title, $html, $hint, $required, $all);
    }

    /**
	* @return NULL
	* @param string $tab_name - Системное имя вкладки
	* @param integer $position - Позиция отступа
	* @param integer $count - Количество отступов
	* @desc Добавляем отступ между полями
	*/
    public function addPadding($tab_name, $position, $count = 1) {
    	$this->new_fields[$tab_name][$position] = $count;
    }

    /**
	* @return NULL
	* @param string $title - Название вкладки
	* @param string $html - Содержимое вкладки
	* @desc Добавляет новую вкладку в форму
	*/
	public function newTabs($title, $html) {
    	$this->tabs_addit[$title] = $html;
	}

    /**
	* @return NULL
	* @param string $tab_name - Системное имя вкладки
	* @param string $html - Содержимое вкладки
	* @desc Добавляет html в начало указанной вкладки
	*/
    public function addInTopTabs($tab_name, $html) {
   		$this->tabs[$tab_name]['top'] = $html;
    }

    /**
	* @return NULL
	* @param string $tab_name - Системное имя вкладки
	* @param string $html - Содержимое вкладки
	* @desc Добавляет html в конец указанной вкладки
	*/
    public function addInBottomTabs($tab_name, $html) {
    	$this->tabs[$tab_name]['bottom'] = $html;
    }

    // Прикрепляет к форме указанный файл JavaScript
    public function attachJavaScript($file) {
    	$this->javascript[] = $file;
    }

    /**
	* @return string HTML
	* @param string $class_list - HTML cодержимое списка
	* @desc Создает список ORM-классов для быстрой смены типа формы.
	*/
	public function setORMList($class_list) {
    	$this->class_list = $class_list;
	}

	/**
	* @return string HTML
	* @param string $templ_name - Имя шаблона оформления
	* @desc Генерация формы редактирования объекта
	*/
	public function getHTML($templ_name = 'orm_edit_form') {

        if (system::$isAdmin)
        	$file = MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl';
        else
            $file = TEMPL_DIR.'/'.$templ_name.'.tpl';

		if (file_exists($file)){

   			include($file);

            // Формируем список полей по группам
            $fields = $groups = $fields_frame = '';
            $this->java = '';
            $old_group = '';
            $count_groups = $this->position = 0;


            if ($this->obj->id == ''){
            	page::assign('obj.id', $this->obj->getParentId());
            	page::assign('obj.class_id', $this->obj->getClass()->id());
            } else if (is_numeric($this->obj->id)){
            	page::assign('obj.id', $this->obj->id);
            	page::assign('obj.class_id', 0);
            }

			page::assign('right', $this->right);

            // Список быстрой сметы класса добавляемого объекта
			if (!empty($this->class_list)) {
  				page::assign('class_name', lang::get('CONSTR_ADDTEXT').$this->obj->getClass()->getPadej(1));
      			page::assign('class_list', $this->class_list);
                $class_list = page::parse($TEMPLATE['class_list']).'<br clear="all"/>';
            } else $class_list = '';

            // Если мы добавляем страницу, выводим специальные элементы управления
            if (system::$isAdmin && ($this->obj instanceof ormPage) && !$this->without_sh) {

            	if (isset($_SESSION['SH_FIELDS']) && $_SESSION['SH_FIELDS'] == 'show') {
             		page::assign('sh1', ' style="display:none;"');
                    page::assign('sh2', '');
                } else {
                    page::assign('sh1', '');
                    page::assign('sh2', ' style="display:none;"');
                }

                page::assign('sh_text1', lang::get('CONSTR_SHOWHIDE1'));
                page::assign('sh_text2', lang::get('CONSTR_SHOWHIDE2'));
                page::assign('class_list', $class_list);

				$fields = page::parse($TEMPLATE['showhide']);

			} else $fields = $class_list;

			// Подключаем обработчик добавления страниц
	        if ($this->obj instanceof ormPage) {
	        	$fields .= '<input id="old_name_val" type="hidden" value="'.$this->obj->name.'">';
	            $this->javascript[] = '/css_mpanel/edit_page.js';
	        }

            // Получаем список полей объекта
            $obj_fields = $this->obj->getClass()->loadFields();

            $hide_clear_field = false;
                // print_r($obj_fields);
      		// Перебираем все поля данного объекта
      		while (list($key, $field) = each ($obj_fields)) {

		    	if (empty($old_group))
		    		$old_group = $field;

				// Парсим данные для группы(вкладки)
				if ($old_group['fg_id'] != $field['fg_id'] && $field['fg_view']) {

                    if (!$this->without_tabs) {
                              //  print_r($field);
                               // echo '<br /><br /><br />';
					    $this->tikatika(true);

					    if (isset($this->tabs[$old_group['fg_sname']]['top']))
					   		$fields = $this->tabs[$old_group['fg_sname']]['top'].$fields;

					   	if (isset($this->tabs[$old_group['fg_sname']]['bottom']))
					   		$fields .= $this->tabs[$old_group['fg_sname']]['bottom'];

						page::assign('fields', $fields);
						page::assign('group.id', $old_group['fg_id']);
						page::assign('group.name', $old_group['fg_name']);
						page::assign('group.sname', $old_group['fg_sname']);

	               		$groups .= page::parse($TEMPLATE['group']);
	               		$fields_frame .= page::parse($TEMPLATE['fields_frame']);
	               		$fields = '';
	               		$old_group = $field;
	               		$count_groups ++;
	               		$this->position = 0;

               		} else $fields .= $this->checkSpecField($old_group['fg_sname'], $TEMPLATE);
				}

    			// Парсим данные для поля

		    	if (($field['f_view'] || (!$field['f_view'] && $field['f_type'] == 0)) && $field['fg_view']
		    		&& (empty($this->allowed_list) || in_array($field['f_sname'], $this->allowed_list)) && !in_array($field['f_sname'], $this->tabu_list)) {

                    $fields .= $this->checkSpecField($field['fg_sname'], $TEMPLATE);

                    if (isset($this->fields[$field['f_sname']])) {

                        // Вставляем HTML разработчика
                        if ($this->fields[$field['f_sname']][1]) {

                    		$fields .= $this->fields[$field['f_sname']][0];

                    	} else if (!empty($this->fields[$field['f_sname']][0])) {

							$fields .= $this->parseSpecField(
								$field['f_sname'],
								$field['f_name'],
								$this->fields[$field['f_sname']][0],
								$field['f_hint'],
								$field['f_required'],
								$TEMPLATE);

                    	}
                    	$fields .= $this->tikatika();

                    } else {

                    	// Генерируем автоматически поле формы
                    	if (!$hide_clear_field || ($field['f_type'] != 0 && $hide_clear_field)) {

                            if (empty($field['f_type'])) {
                                $fields .= $this->parseSepar($field, $TEMPLATE);
                            } else {
                                $fields .= $this->parseField($field, $TEMPLATE);
                            }

                            $fields .= $this->tikatika(($field['f_type'] == 0 || $field['f_type'] == 55 || $field['f_type'] == 60) ? true : false);
                            $hide_clear_field = false;
                        }
		    		}

				} else $hide_clear_field = true;
			}

			// Капча
			if ($this->captcha && isset($TEMPLATE['captcha'])) {
				$this->java .= 'rules.push("required,random_image,'.lang::get('CONSTR_BASE_FIELD_E9').'");';
				page::fParse('captcha', $TEMPLATE['captcha']);
			} else
				page::assign('captcha', '');

            // Парсим форму с вкладками или без
            if (!empty($old_group) || !empty($fields)){

                // Добавляем дополнительный HTML сверху или снизу вкладки
                if (!empty($old_group)) {
		            if (isset($this->tabs[$old_group['fg_sname']]['top']))
						$fields = $this->tabs[$old_group['fg_sname']]['top'].$fields;

					if (isset($this->tabs[$old_group['fg_sname']]['bottom']))
						$fields .= $this->tabs[$old_group['fg_sname']]['bottom'];
                }

	   			if (!empty($old_group) && ($count_groups > 0 || count($this->tabs_addit) > 0)) {

	      			// "Доделываем" последнюю стандартную вкладку
	                if (!empty($fields)) {
                             //echo '||'.$fields.'||';
						page::assign('fields', $fields);
						page::assign('group.id', $old_group['fg_id']);
						page::assign('group.name', $old_group['fg_name']);
						page::assign('group.sname', $old_group['fg_sname']);

	                	$groups .= page::parse($TEMPLATE['group']);
	                	$fields_frame .= page::parse($TEMPLATE['fields_frame']);
					}

	                // Добавляем вкладки добавленные разработчиком "на лету"
					while (list($key, $html) = each ($this->tabs_addit)) {
	                	page::assign('fields', $html);
						page::assign('group.id', md5($key));
						page::assign('group.name', $key);
						page::assign('group.sname', md5($key));
	                	$groups .= page::parse($TEMPLATE['group']);
	                	$fields_frame .= page::parse($TEMPLATE['fields_frame']);
					}

	                page::assign('groups', $groups);
	                page::assign('fields', $fields_frame);
	                page::fParse('form', $TEMPLATE['form_with_tabs']);

	            } else {
	               	// Парсим простую форму (без вкладок)
	                page::assign('fields', $fields);
	                page::fParse('form', $TEMPLATE['simple_form']);
	            }
            }

            page::assign('java_rules', $this->java);

            $javascript = '';
            while (list($key, $url) = each ($this->javascript)) {
	        	page::assign('url', $url);
				$javascript .= page::parse($TEMPLATE['javascript']);
			}
            page::assign('javascript', $javascript);

            page::assign('addit_function', $this->addit_function);


            if (!empty($_SESSION['SAVING_POST']))
               	$_SESSION['SAVING_POST'] = '';

            return page::parse($TEMPLATE['frame']);
   		}
	}

    /*
	    Вспомагательная функция-счетчик. Добавляет, если нужно, после каждого
	    второго поля "разрыв" для коректного отображение полей различной высоты.
    */
    private function tikatika($cur_state = false){
    	$this->odd_field = $cur_state || !$this->odd_field;
    	if ($this->odd_field)
    		return '<div class="clear"></div>';
    }

    // Вставляем, если нужно, дополнительные поля
	private function checkSpecField($tabs_name, $TEMPLATE){

	    $this->position ++;
        $ret = '';
                         //  echo $tabs_name.' - '.$this->position.'<br />';
     	if (isset($this->new_fields[$tabs_name][$this->position])){

      		$f = $this->new_fields[$tabs_name][$this->position];

            if (is_array($f)) {

	        	if ($f[5])
	         		$ret .= $f[2];
	           	else
	            	$ret .= $this->parseSpecField($f[0], $f[1], $f[2], $f[3], $f[4], $TEMPLATE);

	          $ret .= $this->tikatika();

            } else
            	for ($i = 0; $i < $f; $i++)
            		$ret .= $TEMPLATE['field_0'].$this->tikatika(true);

            $ret .= $this->checkSpecField($tabs_name, $TEMPLATE);
      	}

      	return $ret;
    }

    /**
	* @return string HTML
	* @param string $sname - Системное название поля
	* @param string $title - Заголовок поля
	* @param string $html - Содержимое поля
	* @param string $hint - Подсказка для поля
	* @param boolean $required - Обязательность ввода данных
	* @param array $TEMPLATE - Шаблон оформления
	* @desc Парсинг стандартного поля формы.
	*/
	private function parseSpecField($sname, $title, $html, $hint, $required, $TEMPLATE){

		page::assign('field.name', $title);
		page::assign('field.hint', $hint);

		if (!empty($hint))
			page::fParse('field.name', $TEMPLATE['acronym']);

	    if ($required){
	        $msg = str_replace('%title%', $title, lang::get('CONSTR_BASE_FIELD_E2'));
			$this->java .= page::parse('rules.push("required,'.$sname.','.$msg.'");');
			$zvezd = '*';
	   	} else $zvezd = '';
		page::assign('field.zvezd', $zvezd);

	    page::assign('content', $html);
	    return page::parse($TEMPLATE['field_standart']);
	}


	private function parseField($field, $TEMPLATE) {


		if (in_array($field['f_sname'], $this->read_only)) {

  			$value = $this->obj->__get($field['f_sname']);

  			page::assign('value', $value);

  			if ($field['f_type'] == 90 || $field['f_type'] == 95 || $field['f_type'] == 105)
     			page::assign('val', $this->obj->__get('_'.$field['f_sname']));
            else
            	page::assign('val', $value);

            $value = page::parse($TEMPLATE['read_only']);
            page::assign('content', $value);

			return page::parse($TEMPLATE['field_standart']);
     	}


   		// Определяем значение поля
   		if (isset($_SESSION['SAVING_POST']) && ($field['f_type'] < 70 || $field['f_type'] > 85) && isset($_SESSION['SAVING_POST'][$field['f_sname']]))
     		$value = $_SESSION['SAVING_POST'][$field['f_sname']];
       	else if ($field['f_type'] == 105)
            $value = $this->obj->__get('_'.$field['f_sname']);
        else
			$value = $this->obj->__get($field['f_sname']);

		page::assign('field.value', $value);

        // Для страниц, для кнопки "Показать \ скрыть доп. поля"
		if (($this->obj instanceof ormPage) && in_array($field['f_sname'], $this->page_fields)) {
        	$sh_page = (isset($_SESSION['SH_FIELDS']) && $_SESSION['SH_FIELDS'] == 'show') ? '' : ' style="display:none;"';
        	page::assign('sh_page', $sh_page);
		} else page::assign('sh_page', '');

  		// Специальная обработка данных
		if ($field['f_type'] == 50) {

			page::assign('element', ui::CheckBox($field['f_sname'], 1, $value));

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

		} else if ($field['f_type'] == 70 || $field['f_type'] == 75 ||$field['f_type'] == 80 ||$field['f_type'] == 85) {

			if (system::$isAdmin)
				page::assign('element', ui::loadFile($field['f_sname'], $value));
            else
            	page::fParse('element', $TEMPLATE['upload_file_field']);

		} else if ($field['f_type'] == 73) {

			page::assign('element', ui::listFile($field['f_sname'], $value));

		} else if ($field['f_type'] == 90 || $field['f_type'] == 95) {

  			// Выпадающий список или Список со множественным выбором
     		// Получаем список объектов справочника

            page::assign('field.sname', $field['f_sname']);
       		$data = ormObjects::getObjectsByClass($field['f_list_id']);
            $multi = ($field['f_type'] == 95) ? 'selectbox_multi' : 'selectbox_template';
            $empty = (!$field['f_required']) ? '&nbsp;' : '';

            if (system::$isAdmin) {

				page::assign('element', ui::SelectBox($field['f_sname'], $data, $value, 400, $empty, '', $multi));

				if ($field['f_quick_add'])
	   				page::fParse('plus', $TEMPLATE['field_'.$field['f_type'].'_plus']);
	   			else
	      			page::assign('plus', '');

      		} else
      			page::assign('element', ui::SelectBox($field['f_sname'], $data, $value, 200, $empty, '', $multi));

		} else if ($field['f_type'] == 97) {

  			// Подчиненный справочник

     		// Получаем список объектов справочника
            $sel = new ormSelect($field['f_list_id']);
            $sel->where('parents', '=', $this->obj->id);

            // Выводим мультиформу для добавления и редактирования
            $form = new ormMultiForm('subject_list_'.$field['f_id']);
	        $form->setData($sel);
	        $form->insideForm();
	        $re = $form->getHTML();
	      //  echo $re;
      		page::assign('element', $re);


		}  else if ($field['f_type'] == 100) {

  			// Связь с объектом
      		page::assign('element', ui::objectLinks($this->obj, $field['f_id']));

		}



		page::assign('field.id', $field['f_id']);
		page::assign('field.name', $field['f_name']);
		page::assign('field.hint', $field['f_hint']);
		page::assign('field.sname', $field['f_sname']);
		page::fParse('field.dotted', ((!empty($field['f_hint'])) ? 'dotted' : ''));

        if (empty($field['f_max_size']))
            $field['f_max_size'] = ($field['f_type'] == 60) ? 200 : 100;
        page::assign('field.max_size', $field['f_max_size']);

		// Генерируем яву (создаем правила проверки для элементов формы)
  		if ($field['f_required'] && (($field['f_type'] == 35 && $this->obj->id == '') || $field['f_type'] != 35)){
  			$msg = str_replace('%title%', $field['f_name'], lang::get('CONSTR_BASE_FIELD_E2'));

  			if ($field['f_type'] == 60) {

                // HTML - редактор
                $this->addit_function .= page::parse('
  					function checkValue%field.sname%(){
  					 	var value = CKEDITOR.instances["%field.sname%"].getData();
    					return (value != "") ? true : [[ "cke_%field.sname%", "'.$msg.'" ]];
					}
  				');

  				$this->java .= page::parse('rules.push("function,checkValue%field.sname%");');

            } else if ($field['f_type'] == 100) {

                // Выбор объекта
  			    $this->addit_function .= page::parse('
  					function checkObjectLinks%field.sname%(){
  						var count = $("#objectsLinkList_%field.sname% > li").length;
    					return (count > 0) ? true : [[ %field.sname%, "'.$msg.'" ]];
					}
  				');

  				$this->java .= page::parse('rules.push("function,checkObjectLinks%field.sname%");');

  			} else if ($field['f_type'] == 32) {

                 // Каледарь и время
                 $this->java .= page::parse('rules.push("required,%field.sname%_date,'.$msg.'");');

            } else
                  $this->java .= page::parse('rules.push("required,%field.sname%,'.$msg.'");');

			$zvezd = 'chek';
   		} else $zvezd = '';

		page::assign('field.zvezd', $zvezd);

  		if ($field['f_type'] == 15)
      		$this->java .= page::parse('rules.push("valid_email,%field.sname%,'.lang::get('CONSTR_BASE_FIELD_E3').'");');

        if ($field['f_type'] == 20)
        	$this->java .= page::parse('rules.push("reg_exp,%field.sname%,^(((f|ht){1}tp:/)*/[-a-zA-Z0-9@:%_\+.~#?&//=]+)*$, '.lang::get('CONSTR_BASE_FIELD_E4').'");');

        if ($field['f_type'] == 40)
      		$this->java .= page::parse('rules.push("digits_only,%field.sname%,'.lang::get('CONSTR_BASE_FIELD_E6').'");');

        if ($field['f_type'] == 45)
      		$this->java .= page::parse('rules.push("float_only,%field.sname%,'.lang::get('CONSTR_BASE_FIELD_E7').'");');

      	if ($field['f_type'] == 47)
      		$this->java .= page::parse('rules.push("price_only,%field.sname%,'.lang::get('CONSTR_BASE_FIELD_E8').'");');

        if (isset($TEMPLATE['field_'.$field['f_type']]))
       		return page::parse($TEMPLATE['field_'.$field['f_type']]);
       	else
       		return str_replace('%title%', $field['f_sname'], lang::get('CONSTR_BASE_FIELD_E5'));

	}

    // Парсим разделитель
    function parseSepar($field, $TEMPLATE) {

        if (!empty($field['f_max_size']))
            page::assign('size', ' style="margin-top:'.$field['f_max_size'].'px;"');
        else
            page::assign('size', '');

        if (!empty($field['f_name'])) {
            page::assign('title', $field['f_name']);
            return page::parse($TEMPLATE['field_0_text']);
        } else
            return page::parse($TEMPLATE['field_0']);
    }
}

?>
