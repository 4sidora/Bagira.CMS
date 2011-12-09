<?php

class __values {

    public function __construct() {
    	ui::checkClasses('handbook');
    }

    // вывод списка
	public function defAction() {

        if (!system::issetUrl(2))
        	system::redirect('/reference');

        if ($class = ormClasses::get(system::url(2))) {

            if (!$class->isInheritor('handbook') || $class->getSName() == 'handbook')
                system::redirect('/reference');

            ui::setNaviBar(lang::right('values'));
            ui::setHeader($class->getName());
            ui::setBackButton('/reference');

            if (user::issetRight('val_upd')) {
                $count = 0;
                $fields = $class->loadFields();
                foreach($fields as $field)
                    if ($field['f_view'] == 1)
                        $count ++;

            } else $count = 4;

            $sel = new ormSelect($class->getSName());

            if ($count > 5) {
                ui::newButton(lang::get('BTN_NEW_LIST'), '/reference/val_add/'.system::url(2));

                $table = new uiTable($sel);
                $table->showSearch(true);
                $table->addColumn('name', 'Имя объекта');
                $table->addColumn('id', '#', 200);

                $table->defaultRight('val_upd');
                $table->addRight('val_upd', 'edit', single);
                $table->addRight('val_del', 'drop', multi);

                return $table->getHTML();

            } else {

                ui::newButton(lang::get('BTN_SAVE'), "javascript:sendForm('save');");
                ui::newButton(lang::get('BTN_APPLY'), "javascript:sendForm('apply');");

                $form = new ormMultiForm('change');
                $form->setData($sel);
                $form->showColumnID();
                $form->setRight('val_proc_upd');
                $form->moreParam(system::url(2));

                if (!user::issetRight('val_upd') || !user::issetRight('val_add'))
                    $form->withoutAdditions();

                if (!user::issetRight('val_del'))
                    $form->withoutRemoving();

                return $form->getHTML();
            }
        } else system::redirect('/reference');
 	}

}

?>