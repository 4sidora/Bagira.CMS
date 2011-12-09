<?php

class __list {

    public function __construct() {
    	ui::checkClasses('subscription', 'subscribe_msg', 'subscribe_user');
    }

    // Основная страница модуля
	public function defAction() {

        function getSubscribersCount($id, $obj) {        	$sel = new ormSelect('subscribe_user');
       	 	$sel->where('parents', '=', $id);
       	 	return $sel->getCount();
        }

        ui::newButton(lang::get('SUBSCRIBE_BTN_ADD'), '/subscription/subscribe_add');

	    $sel = new ormSelect('subscription');
        $sel->where('lang', '=', languages::curId());
        $sel->where('domain', '=', domains::curId());

        $table = new uiTable($sel);
        $table->formatValues(true);
        $table->addColumn('name', lang::get('SUBSCRIBE_TT1'), 200);
        $table->addColumn('last_subscribe', lang::get('SUBSCRIBE_TT2'), 200);
        $table->addColumn('id', lang::get('SUBSCRIBE_TT3'), 200, 0, 1, 'getSubscribersCount');

        $table->defaultRight('msg');
        $table->addRight('msg', 'list', single);
        $table->addRight('user', 'users', single);
        $table->addRight('subscribe_upd', 'edit', single);
        $table->addRight('subscribe_history', 'history', single);
        $table->addRight('subscribe_del', 'drop', multi);
        $table->addRight('subscribe_act', 'active', multi);

        $table->setDelMessage(lang::get('SUBSCRIBE_DEL_TITLE2'), lang::get('SUBSCRIBE_DEL_TEXT2'));
        $table->setMultiDelMessage(lang::get('SUBSCRIBE_DEL_TITLE_MULTI2'), lang::get('SUBSCRIBE_DEL_TEXT_MULTI2'));


        return $table->getHTML();
 	}

}

?>