<?php

class __list {

	// вывод списка
	public function defAction() {


        $count = ormObjects::getCountTrashObjects();
        $max_count = uiTable::getMaxCount();
        if(uiTable::getCurPage() != 1) {
		    $niz = (empty($start_pos)) ? uiTable::getCurPage() * $max_count - $max_count : 0;
            $objects = ormObjects::getTrashObjects($niz, $max_count);
		} else $objects = ormObjects::getTrashObjects($max_count);

        if (user::issetRight('object_del') && !empty($objects))
    		ui::newButton(lang::get('BTN_NEW_CLEAК_TRASH'), 'javascript:clearTrash();');


		$table = new uiTable($objects, $count);

        $table->addColumn('name', lang::get('TRASH_TABLE_1'), 400);
        $table->addColumn('class', lang::get('TRASH_TABLE_2'));
        $table->addColumn('date', lang::get('TRASH_TABLE_3'), 0, false, false, 'viewDateTime');
        $table->addColumn('user', lang::get('TRASH_TABLE_4'), 200);

        $table->addRight('object_restore', 'restore', multi, 'restoreObj(this)', lang::get('TRASH_TABLE_RESTORE'));
        $table->addRight('object_del', 'drop', multi);

        $table->setDelMessage(lang::get('TRASH_DEL_TITLE'), lang::get('TRASH_DEL_MSG'));
        $table->setMultiDelMessage(lang::get('TRASH_DEL_TITLE2'), lang::get('TRASH_DEL_MSG2'));

        $table->emptyText(lang::get('TRASH_EMPTY_MSG'));

        $html = '
        <script type="text/javascript" src="/css_mpanel/restore_objects.js"></script>
        <input id="clearTrashTitle" type="hidden" value="'.lang::get('TRASH_DEL_TITLE3').'">
        <input id="clearTrashText" type="hidden" value="'.lang::get('TRASH_DEL_MSG3').'">
        ';

        return $html.$table->getHTML();

 	}


}

?>