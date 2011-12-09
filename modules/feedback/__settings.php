<?php

class __settings {

    public function __construct() {
    	ui::checkClasses('feedback', 'feedback_form');
    }

	// вывод списка
	public function defAction() {


        ui::newButton(lang::get('FEEDBACK_BTN_ADD'), '/feedback/form_add');

        function getClassName($val, $obj) {
        	if ($class = ormClasses::get($val))
        		return $class->getName();
        }

        function getAction($val, $obj) {

        	$ret = '';

        	if (!empty($val))
        		$ret .= 'Отправка письма на почту';

        	if ($obj->any_sections || $obj->section != 0) {
        		if (!empty($ret)) $ret .= ', ';
        		$ret .= 'Сохранение заявки в БД';
        	}

        	return $ret;
        }


        $sel = new ormSelect('feedback_form');
        $sel->fields('mailing_list, captcha, form_class, section');

		$table = new uiTable($sel);
        $table->formatValues(true);
        $table->addColumn('name', 'Название формы', 200);
        $table->addColumn('mailing_list', 'Выполняемое действие', 400, 0, 1, 'getAction');
        $table->addColumn('captcha', 'Защита от спама', 100);
        $table->addColumn('form_class', 'Тип', 120, 0, 1, 'getClassName');

        $table->defaultRight('form_upd');
        $table->addRight('form_upd', 'edit', single);
        $table->addRight('form_del', 'drop', multi);

        return $table->getHTML();

 	}


}

?>