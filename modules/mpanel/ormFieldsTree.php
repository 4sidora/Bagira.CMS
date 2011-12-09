<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс для построения "дерева" отображающего структуру класса данных (ormClass)
 	Показывает вложенные поля по группам.
*/

class ormFieldsTree {

    private $rightEdit = '';
    private $rightEdit2 = '';
    private $rightEdit4 = '';

    private $rights = array();
    private $rights2 = array();
    private $rights3 = array();
    private $rights4 = array();


    function __construct() {

        // Настраиваем отображение для полей
        $this->setFieldsRightEdit("changeField(%item.id%, 'upd')");
        $this->addFieldsEmptyRight();
        $this->addFieldsEmptyRight();
        $this->addFieldsRight('field_upd', lang::right('field_upd'), 'compose_image', "changeField(%item.id%, 'upd')");
        $this->addFieldsRight('field_del', lang::right('field_del'), 'drop_image', 'delField(%item.id%)');

        // Настраиваем отображение для разделителей
        $this->setFieldsRightEdit2("changeSepar(%item.id%, 'upd')");
        $this->addFieldsEmptyRight2();
        $this->addFieldsEmptyRight2();
        $this->addFieldsRight2('separator_upd', lang::right('separator_upd'), 'compose_image', "changeSepar(%item.id%, 'upd')");
        $this->addFieldsRight2('field_del', lang::right('field_del'), 'drop_image', 'delField(%item.id%)');

        // Настраиваем отображение для групп
        $this->setGroupsRightEdit("changeGroup(%item.id%, 'upd')");
        $this->addGroupsRight('separator_add', lang::right('separator_add'), 'add_vacum', "addSepar(%item.id%)");
        $this->addGroupsRight('separator_add', lang::right('separator_add'), 'add_abc', "changeSepar(%item.id%, 'add')");
        $this->addGroupsRight('field_add', lang::right('field_add'), 'add_image', "changeField(%item.id%, 'add')");
        $this->addGroupsEmptyRight();
        $this->addGroupsRight('fgroup_upd', lang::right('fgroup_upd'), 'compose_image', "changeGroup(%item.id%, 'upd')");
        $this->addGroupsRight('fgroup_del', lang::right('fgroup_del'), 'drop_image', 'delGroup(%item.id%)');

        // Настраиваем отображение для системных групп
        $this->addGroupsRight2('separator_add', lang::right('separator_add'), 'add_vacum', "addSepar(%item.id%)");
        $this->addGroupsRight2('separator_add', lang::right('separator_add'), 'add_abc', "changeSepar(%item.id%, 'add')");
        $this->addGroupsRight2('field_add', lang::right('field_add'), 'add_image', "changeField(%item.id%, 'add')");
        $this->addGroupsEmptyRight2();
        $this->addGroupsEmptyRight2();
        $this->addGroupsEmptyRight2();
    }

    // ***************************        Публичные методы        ********************************

    /**
     * @return nil
     * @param string $r_name - Системное название права
     * @param string $r_title - Русское название права
     * @param string $r_class - CSS-класс с картинкой для пиктограмки
     * @param string $java - Обработчик на JavaScript
     * @desc Добавляет новое право для поля
     */
    private function addFieldsRight($r_name, $r_title, $r_class, $java = 0) {
        $this->rights[] = array('name' => $r_name,
                                'title' => $r_title,
                                'class' => $r_class,
                                'java' => $java);
    }

    private function addFieldsEmptyRight() {
        $this->rights[] = 'empty';
    }

    private function setFieldsRightEdit($r_name) {
        $this->rightEdit = $r_name;
    }


    // Все тоже самое для групп полей
    private function addFieldsRight2($r_name, $r_title, $r_class, $java = 0) {
        $this->rights4[] = array('name' => $r_name,
                                'title' => $r_title,
                                'class' => $r_class,
                                'java' => $java);
    }

    private function addFieldsEmptyRight2() {
        $this->rights4[] = 'empty';
    }

    private function setFieldsRightEdit2($r_name) {
        $this->rightEdit4 = $r_name;
    }

    // Все тоже самое для групп полей
    private function addGroupsRight($r_name, $r_title, $r_class, $java = 0) {
        $this->rights2[] = array('name' => $r_name,
                                 'title' => $r_title,
                                 'class' => $r_class,
                                 'java' => $java);
    }

    private function addGroupsEmptyRight() {
        $this->rights2[] = 'empty';
    }

    private function setGroupsRightEdit($r_name) {
        $this->rightEdit2 = $r_name;
    }

    // Все тоже самое для групп полей  2
    private function addGroupsRight2($r_name, $r_title, $r_class, $java = 0) {
        $this->rights3[] = array('name' => $r_name,
                                 'title' => $r_title,
                                 'class' => $r_class,
                                 'java' => $java);
    }

    private function addGroupsEmptyRight2() {
        $this->rights3[] = 'empty';
    }

    // Очищает все настройки класса
    private function clearAllValue() {
        $this->rights = array();
        $this->rights2 = array();
    }




    // Кнопки редактирования для групп полей
    private function getRightForGroup($parse, $group_id, $other = false){

        $groupRights = '';
        $rrright = ($other) ? $this->rights3 : $this->rights2;

        reset($rrright);
        while (list($key, $right) = each ($rrright)){
            if ($right == 'empty' || !user::issetRight($right['name']))
                $groupRights .= ($right != 'empty') ? '' : page::parse($parse['item_right_null']);
            else {
                page::assign('title', $right['title']);
                page::assign('image_style', $right['class']);

                if (!empty($right['java'])) {
                    page::fParse('right_url', $right['java']);
                    $templ = 'item_right_java';
                } else {
                    page::assign('right_url', $main_url.$right['name'].'/'.$group_id);
                    $templ = 'item_right';
                }
                $groupRights .= page::parse($parse[$templ]);
            }
        }

        return $groupRights;
    }

    /**
     * @return Сгенерированный HTML
     * @param ormFieldsGroup $obj -  экземпляр ORM-группы, для которой необходимо сгенерировать HTML
     * @param Bool $isUpd - Если 1 генерировать для обновления, 0 - для добавления
     * @param String $templ_name - имя шаблона оформления
     * @desc Генерирует HTML для обновления группы через AJAX
     */
    function getGroupHTML($group, $isUpd, $templ_name = 'orm_fields_tree'){

        if (file_exists(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl')){
            include(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl');

            page::assign('item.id', $group->id());
            page::assign('item.name', $group->getName());
            page::assign('item.sname', $group->getSName());
            page::assign('sh', (($group->getView()) ? ' lshow' : ' lhide'));

            $other = ($group->getSystem() || $group->isClone()) ? true : false;
            page::assign('item.right', $this->getRightForGroup($TEMPLATE, $group->id(), $other));

            if ($isUpd)
                $ret = page::parse($TEMPLATE['upd_group']);
            else
                $ret = page::parse($TEMPLATE['new_group']);

        } else $ret = '';

        return $ret;
    }

    // Кнопки редактирования для полей
    private function getRightForField($parse, $field_id, $all_rights = true, $field_name = ''){

        $fieldRights = '';

        $rights = ($all_rights) ? $this->rights : $this->rights4;
        reset($rights);

        while (list($key, $right) = each ($rights)){

            if ($field_name == 'name' && $right['name'] == 'field_del')

                $fieldRights .= page::parse($parse['item_right_null']);

            else if ($right == 'empty' || !user::issetRight($right['name']))

                $fieldRights .= ($right != 'empty') ? '' : page::parse($parse['item_right_null']);

            else {

                page::assign('title', $right['title']);
                page::assign('image_style', $right['class']);

                if (!empty($right['java'])) {
                    page::fParse('right_url', $right['java']);
                    $templ = 'item_right_java';
                } else {
                    page::assign('right_url', $right['name'].'/'.$field_id);
                    $templ = 'item_right';
                }

                $fieldRights .= page::parse($parse[$templ]);
            }
        }

        return $fieldRights;
    }

    /**
     * @return Сгенерированный HTML
     * @param ormField $obj -  экземпляр ORM-поля, для которого необходимо сгенерировать HTML
     * @param Bool $isUpd - Если 1 генерировать для обновления, 0 - для добавления
     * @param String $templ_name - имя шаблона оформления
     * @desc Генерирует HTML для обновления поля через AJAX
     */
    function getFieldHTML($obj, $isUpd, $templ_name = 'orm_fields_tree'){

        $ret = '';

        if (file_exists(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl')){
            include(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl');

            page::assign('item.id', $obj->id());
            page::fParse('item.url', $this->rightEdit);
            page::assign('item.sname', $obj->getSName());

            if ($obj->getName() != '')
                page::assign('item.name', $obj->getName());
            else
                page::assign('item.name', '- - - - -');

            if ($obj->getType() == 0)
                page::assign('item.type', '');
            else
                page::assign('item.type', lang::get('CONSTR_TYPE_LIST', $obj->getType()));

            page::assign('star', (($obj->getRequired()) ? '*' : ''));
            page::assign('sh', (($obj->getView()) ? 'lshow' : 'lhide'));

            if ($obj->getType() != 0 && ($obj->getSystem() || $obj->isClone())) {
                $postfix = '_not_edit';
            } else {

                page::assign('item.right', $this->getRightForField($TEMPLATE, $obj->id(), $obj->getType(), $obj->getSName()));
                $postfix = '_edit';
            }

            if ($obj->getType() == 0) {

                if ($isUpd)
                    $ret = page::parse($TEMPLATE['upd_separator']);
                else
                    $ret = page::parse($TEMPLATE['separator']);

            } else if ($isUpd)
                $ret = page::parse($TEMPLATE['upd_field'.$postfix]);
            else
                $ret = page::parse($TEMPLATE['field'.$postfix]);

        }

        return $ret;

    }

    /**
     * @return Сгенерированный HTML
     * @param ormClass $class -  экземпляр ORM-класса, для которого необходимо построить структуру
     * @param String $templ_name - имя шаблона оформления
     * @desc Генерирует структуру полей для класса данных
     */
    function getHTML($class, $templ_name = 'orm_fields_tree') {

        if (file_exists(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl')){
            include(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl');

            $nodes['groups'] = $class->getAllGroups();
            $nodes['fields'] = $class->getAllFields();

            $main_url = system::au().'/'.system::url(0).'/';
            page::assign('main_url', $main_url);

            if (is_array($nodes) && isset($nodes['groups']) && isset($nodes['fields'])) {

                $groups = '';
                while (list($key, $node) = each ($nodes['groups'])) {

                    // Парсим данные о вложенных полях
                    $fields = '';
                    reset($nodes['fields']);
                    while (list($key, $field) = each ($nodes['fields']))

                        if ($field['f_group_id'] == $node['fg_id']){


                            if (empty($field['f_name']))
                                $field['f_name'] = '- - - - -';
                            
                            page::assign('item.id', $field['f_id']);
                            page::assign('item.parent_id', $field['f_group_id']);
                            page::fParse('item.url', $this->rightEdit);
                            page::assign('item.name', $field['f_name']);
                            page::assign('item.sname', $field['f_sname']);
                            page::assign('item.type', lang::get('CONSTR_TYPE_LIST', $field['f_type']));

                            page::assign('star', (($field['f_required']) ? '*' : ''));
                            page::assign('sh', (($field['f_view']) ? 'lshow' : 'lhide'));

                            if (empty($field['f_type'])) {

                                page::assign('item.right', $this->getRightForField($TEMPLATE, $field['f_id'], $field['f_type'], $field['f_sname']));
                                $fields .= page::parse($TEMPLATE['separator']);

                            } else if ($field['f_system'] || $field['f_is_clone']) {

                                $fields .= page::parse($TEMPLATE['field_not_edit']);

                            } else {

                                page::assign('item.right', $this->getRightForField($TEMPLATE, $field['f_id'], $field['f_type'], $field['f_sname']));
                                $fields .= page::parse($TEMPLATE['field_edit']);

                            }
                        }

                    page::assign('items', $fields);
                    page::assign('item.id', $node['fg_id']);
                    page::fParse('sub_items', $TEMPLATE['frame_items']);

                    // Парсим данные о группе полей 		fg_view   fg_sname
                    page::assign('item.id', $node['fg_id']);
                    page::assign('item.parent_id', 0);
                    page::fParse('item.url', $this->rightEdit2);
                    page::assign('item.name', $node['fg_name']);
                    page::assign('item.sname', $node['fg_sname']);
                    page::assign('sh', (($node['fg_view']) ? ' lshow' : ' lhide'));

                    $other = ($node['fg_system'] || $node['fg_is_clone']) ? true : false;
                    page::assign('item.right', $this->getRightForGroup($TEMPLATE, $node['fg_id'], $other));

                    $groups .= page::parse($TEMPLATE['groups']);

                }

                page::assign('frame_items', $groups);


            } else page::assign('frame_items', '');

            ui::insertButton('button_new_group', lang::get('BTN_NEW_FGROUP'), 'javascript:changeGroup('.$class->id().', \'add\');');

            return page::parse($TEMPLATE['main']);

        }

    }


}

?>