<?php

class __list {

    public function __construct() {
    	ui::checkClasses('handbook');

        ui::addLeftButton(lang::right('list'), 'list');
        ui::addLeftButton(lang::right('state'), 'state');
        ui::addLeftButton(lang::right('delivery'), 'delivery');
        ui::addLeftButton(lang::right('payment'), 'payment');
    }

	// вывод списка
	public function defAction() {


        function getNumber($val, $obj) {
            return substr('00000', 0, 5-strlen($val)).$val;
        }

        $sel = new ormSelect('eshop_order');
        //$sel->where('form_id', '<>', 0);
        $sel->orderBy('date', desc);


        $table = new uiTable($sel);
        $table->showFilters(true);
        $table->formatValues(true);
        $table->addColumn('name', 'Номер', 100, false, true, 'getNumber');
        $table->addColumn('state', 'Статус', 300);
        $table->addColumn('date', 'Дата', 300);
        $table->addColumn('email', 'E-mail', 300);

        $table->defaultRight('order_view');
        $table->addRight('order_view', 'edit', single);
        $table->addRight('order_del', 'drop', multi);

        return $table->getHTML();

 	}


}

?>