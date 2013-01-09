<?php

class __list {

    public function __construct() {
    	ui::checkClasses('feedback', 'feedback_form');
    }

    // вывод списка
	public function defAction() {

        function getClassName($val, $obj) {
        	if ($obj = ormPages::get($val))
        		return $obj->getClass()->getName();
        }

        $sel = new ormSelect('feedback');
        $sel->findInPages();
        $sel->where('form_id', '<>', 0);
        $sel->orderBy('create_date', desc);


        $table = new uiTable($sel);
        $table->showSearch(true);
        $table->formatValues(true);
        $table->addColumn('content', 'Текст сообщения', 300);
        $table->addColumn('name', 'Имя пользователя', 120);
        $table->addColumn('email', 'E-mail', 120);
        $table->addColumn('create_date', 'Дата публикации', 120);
        $table->addColumn('id', 'Тип', 120, 0, 1, 'getClassName');


        $table->defaultRight('message_upd');
        $table->addRight('message_act', 'active', multi);
        $table->addRight('message_upd', 'edit', single);
        $table->addRight('message_del', 'drop', multi);

        return $table->getHTML();


 	}

}

?>