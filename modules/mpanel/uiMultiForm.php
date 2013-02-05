<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс позволяет создавать мультиформы.
	Мультиформы - это формы для редактирования сразу несколько записей.
	Своеобразный сплав обычной формы и таблицы.
*/

class uiMultiForm extends uiTableFunctions {

	private $select, $right, $form_name;
    private $java, $requred_field;
	private $page_fields = array('title', 'h1', 'pseudo_url', 'keywords', 'description');
	private $more_param = array();
	private $columns = array();
    private $empty_obj = array('id' => 'new1');

	private $without_add = false;
	private $without_del = false;
	private $inside_form = false;

	private $width = 200;


    // Создает форму с указанным именем
    public function __construct($form_name = '') {
    	$this->form_name = $form_name;
	}

	/**
    * @return null
    * @param string $field - Имя поля/столбца
    * @param string $title - Заголовок столбца
    * @param integer $width - Ширина столбца
    * @param string $hint - Подсказка для столбца
    * @param boolean $editable - Если false, режим "только чтение"
    * @param string $funct_name - Имя php-функции для обработки значений поля
    * @desc Добавляет в форму столбец/поле
    */
    public function addColumn($field, $title = 0, $width = 0, $hint = 0, $editable = true, $funct_name = '') {
    	$this->columns[$field] = array(
    		'f_sname' => $field,
    		'width' => $width,
    		'f_name' => $title,
    		'editable' => $editable,
    		'funct_name' => $funct_name,
    		'f_hint' => $hint
    	);
    }

	// Прикрепляет к форме указанный Javascript
    public function attachJavaScript($file) {
    	$this->javascript[] = $file;
    }

    /**
    * @return null
    * @param string $mas - Ассоциативный массив, названия ключей должны совпадать с названием столбцов
    * @desc Устанавливает выборку объектов/записей
    */
    public function setData($mas) {
    	$this->select = $mas;
	}

    // Устанавливает право для обработки формы
	public function setRight($right) {
        $this->right = $right;
	}

    /*
    	Устанавливает дополнительные параметры, передаваемые обработчику формы
    */
	public function moreParam() {
        $this->more_param = func_get_args();
	}

    /**
    * @return string
    * @param integer $num - Номер параметра, если не указан метод вернет список параметров в виде массива
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
	public function getHTML($templ_name = 'multi_form') {

		if (file_exists(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl')){

   			include(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl');

            page::assign('mright', $this->right);
            page::assign('form_name', $this->form_name);


            // Вывод столбцов
	        $columns = '';
            reset($this->columns);
            while(list($name, $column) = each($this->columns)) {
            	$columns .= $this->getColumn($column, $TEMPLATE);
            }
            page::assign('columns', $columns);

            $column_del = ($this->without_del) ? '' : $TEMPLATE['colum_del'];
            page::assign('column_del', $column_del);


            // Вывод форм редактрования для объектов
            $lines = '';
            while(list($key, $obj) = each($this->select))
            	$lines .= $this->parseLineForm($obj, $TEMPLATE);

            // Выводим форму добавления нового объекта
            if (!$this->without_add)
				$lines .= $this->parseLineForm($this->empty_obj, $TEMPLATE);

	        page::assign('lines', $lines);

            // Дополнительные параметры
            $params = '';
            while (list($key, $val) = each($this->more_param)) {
               	page::assign('val', $val);
               	$params .= page::parse($TEMPLATE['params']);
            }
            page::assign('params', $params);

            // Парсим все что получилось в единую форму
            if (!$this->inside_form){
                page::fParse('html', $TEMPLATE['frame']);
            	return page::parse($TEMPLATE['form_frame']);
            } else return page::parse($TEMPLATE['frame']);
   		}
	}


	private function getColumn($column, $TEMPLATE){

	 	$width = (!empty($column['width'])) ? $column['width'] : $this->width;
	 	page::assign('width', $width + 11);

   		$title = $column['f_name'];
     	if (!empty($column['f_hint'])) {
      		page::assign('hint', $column['f_hint']);
            page::assign('title', $title);
            $title = page::parse($TEMPLATE['acronym']);
        }
        if (!empty($column['f_required'])) $title .= ' *';

        page::assign('title', $title);
        return page::parse($TEMPLATE['colums']);
	}

	// Создает поле
	private function parseLineForm($obj, $TEMPLATE, $new_num = 1) {

        $is_upd = (!is_numeric($obj['id'])) ? false : true;
        $current_id = (!$is_upd) ? 'new'.$new_num : $obj['id'];
		page::assign('obj_id', $current_id);

    	$del_check = ($this->without_del || !$is_upd) ? '' : $TEMPLATE['del_check'];
     	page::fParse('del_check', $del_check);

        $flist = '';
      	reset($this->columns);
     	while(list($name, $column) = each($this->columns)) {

     	    if ($is_upd)
     	    	$new = ' '.$name;
     	    else
     	    	$new = (empty($flist)) ? ' new' : ' new_'.$name;

     	    page::assign('new', $new);
      		$flist .= $this->parseField($obj, $column, $TEMPLATE, $current_id);
       	}

     	page::assign('object', $flist);

      	return page::parse($TEMPLATE['lines']);
    }


    // Парсим поле
	private function parseField($obj, $field, $TEMPLATE, $current_id) {

        $is_upd = (!is_numeric($obj['id'])) ? false : true;
        $is_editable = (!isset($field['editable']) || $field['editable']);
		$width = (empty($field['width'])) ? $this->width : $field['width'];
		page::assign('width', $width);

        if (isset($_SESSION['SAVING_POST']) && isset($_SESSION['SAVING_POST'][$field['f_sname']]))

	     	$value = $_SESSION['SAVING_POST'][$field['f_sname']];

	    else {

			$value = (isset($obj[$field['f_sname']])) ? $obj[$field['f_sname']] : '';

			if (!empty($field['funct_name']))
			    $value = $this->exeFunction($value, $obj, $field['funct_name']);

        }

		if ($is_editable){
			page::assign('field.name', $field['f_name']);
			page::assign('field.sname', $field['f_sname']);
			page::assign('field.value', $value);
			$value = page::parse($TEMPLATE['field']);
       	}

        page::assign('content', $value);
       	return page::parse($TEMPLATE['field_frame']);
	}


    // ***********************************************************************************************************

    /**
    * @return null
    * @param string $change_funct - Имя php-функции для обработки добавления/изменения записей.
    * @param string $del_funct - Имя php-функции для обработки удаления записей.
    * @param string $addit_val - Дополнительный параметр передаваемый в функцию.
    * @desc Сохраняет все пришедшие данные. Метод используется обработчиком формы.
    */
	public function process($change_funct, $del_funct = '', $addit_val = '') {
          // print_r($_POST);
 		if (isset($_POST['obj'.$this->form_name])) {

    		while(list($id, $fields) = each($_POST['obj'.$this->form_name])) {

                $id = system::checkVar($id, isInt);

	            if (!$this->without_del && isset($_POST['delete_'.$this->form_name.'_'.$id])) {

                    if (!empty($del_funct))
	                    if (function_exists($del_funct))
		            		call_user_func($del_funct, $id, $this->form_name, $addit_val);
	                    else
	                    	trigger_error('Not found user function "'.$del_funct.'()"!', E_USER_ERROR);

	            } else {

					$check = false;
					foreach ($fields as $val) {
						if (!empty($val)) $check = true;
					} 

	            	if ($check) {

	            		if (function_exists($change_funct))
	                    	$is_ok = call_user_func($change_funct, $id, $fields, $this->form_name, $addit_val);
		                else
		                	trigger_error('Not found user function "'.$change_funct.'()"!', E_USER_ERROR);

			            if (!$is_ok)
					    	system::savePostToSession();
					}
	            }
            }
      	}
	}
}

?>
