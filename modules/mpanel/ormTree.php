<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс позволяет отображать вложенность ORM-объектов в виде дерева.
*/

class ormTree {

    private $root_id = 0;
    private $root_url = '';
    private $root_title = '';

    private $width = '';
    private $width2 = '';
    private $plus_table = '';
    private $rightRemove = '';
    private $rightEdit = '';
    private $rightActive = '';
    private $ajaxLink = '';
    private $del_title, $del_text;

    private $rights = array();
    private $orm_classes = array();
    private $miniStyle = false;
    private $funct_name = '';


    /**
     * @return null
     * @param integer $width - Общая ширина дерева
     * @param integer $width2 - Ширина отведенная под элементы управления
     * @desc Конструктор класса.
     */
    function __construct($width, $width2) {
        $this->width = $width;

        if ($width < 400)
            $this->plus_table = 1;

        $this->width2 = $width2;
    }

    // Указывает php-класс, с объектами которого будет проходить работа
    public function setClass($base_class) {
        $this->isPagesTree = ($base_class == 'ormPage') ? true : false;
    }

    /*
    	Устанавливает фильтр на выборку объектов по классам.
    	В качестве параметров перечисляется список разрешенных ORM-классов.
    */
    public function setOrmClasses() {
        $parram = func_get_args();
        if (count($parram) == 1 && is_array($parram[0]))
            $parram = $parram[0];

        $this->orm_classes = array();
        while (list($key, $class) = each ($parram))
            $this->orm_classes[$class] = 1;
    }


    // Устанавливает Минималистичный стиль отображения дерева
    public function miniStyle() {
        $this->miniStyle = true;
    }

    /**
     * @return null
     * @param integer $id - ID ORM-объекта
     * @param string $title - Заголовок для корня
     * @param string $url - Ссылка (если нужно)
     * @desc Устанавливает корень дерева с указанием параметров
     */
    public function setRoot($id, $title, $url = '#') {

        $this->root_id = system::checkVar($id, isInt);

        $title = system::checkVar($title, isString);
        if ($title != false)
            $this->root_title = $title;

        $url = system::checkVar($url, isString);
        if ($url != false)
            $this->root_url = $url;
    }


    // ***************************        Публичные методы        ********************************

    function setNotice($funct_name) {
        $this->funct_name = $funct_name;
    }

    // Устанавливает право для редактирования объектов
    function setRightEdit($r_name) {
        if (user::issetRight($r_name))
            $this->rightEdit = $r_name;
    }

    // Устанавливает право для вкл/выкл объектов
    function setRightActive($r_name) {
        if (user::issetRight($r_name))
            $this->rightActive = $r_name;
    }

    // Устанавливает право для удаления объектов
    function setRightRemove($r_name) {
        if (user::issetRight($r_name))
            $this->rightRemove = $r_name;
    }

    /*
    	Устанавливает право для динамической подгрузки подразделов.
    	Обычно указывается текущее право.
    */
    function setRightAjaxLoad($link) {
        $this->ajaxLink = $link;
    }

    /**
     * @return null
     * @param string $title - Заголовок сообщения
     * @param string $text - Текст сообщения
     * @desc Устанавливает текст для сообщения удаления записи
     */
    function setDelMessage($title, $text) {
        $this->del_title = $title;
        $this->del_text = $text;
    }


    /**
     * @return null
     * @param string $r_name - Системное название права
     * @param string $r_title - Русское название права
     * @param string $r_class - CSS-класс с картинкой
     * @param string $java - Обработчик на Javascript, если есть
     * @param string $r_show_in_root - Если TRUE, право будет отображаться у корня
     * @param string $r_list - ID выпадающего списка
     * @param string $r_list_html - Содержимое списка
     * @desc Добавляет новое право для веточки
     */
    function addRight($r_name, $r_title, $r_class, $java = 0, $r_show_in_root = 1, $r_list = '', $r_list_html = '') {
        $this->rights[] = array(
            'name' => $r_name,
            'title' => $r_title,
            'class' => $r_class,
            'show_in_root' => $r_show_in_root,
            'list' => $r_list,
            'list_html' => $r_list_html,
            'java' => $java
        );
    }

    // Добавляет отступ между прав
    function addEmptyRight() {
        $this->rights[] = 'empty';
    }

    // Очищает список прав
    function clearAllValue() {
        $this->rights = array();
    }

    private function getTreeObject($section_id) {
        if ($this->isPagesTree) {
            if ($page = ormPages::getPageOfSection($section_id, $this->orm_classes))
                if ($page->isEditable())
                    return $page;
                else
                    return $this->getTreeObject($section_id);
        }
    }

    /**
     * @return string
     * @param integer $section - ID объекта с которого начинается построение дерева
     * @param array $parse - Шаблон оформления
     * @desc Рекурсивная функция строит дерево н-го уровня
     */
    private function build_menu($section_id, $parse, $with_frame = true){

        $items = '';

        while($obj = $this->getTreeObject($section_id)) {

            if (isset($_SESSION['TREE_OPEN_NODES'][$obj->id]) && $obj->issetChildren())
                $sub_items = $this->build_menu($obj->id, $parse);

            page::assign('item.id', $obj->id);
            page::assign('item.parent_id', $section_id);
            page::assign('item.url', $this->rightEdit.'/'.$obj->id);


            $pach = '/css_mpanel/tree/images/';
            $ico = 'file1.gif';
            if ($obj->getClass()->issetField('active')) {

                page::assign('item.active', $obj->active);

                if (!$obj->active)
                    $ico = 'file0.gif';
                else {
                    $ico = 'classes/'.$obj->getClass()->getSName().'.png';
                    if (!file_exists(ROOT_DIR.$pach.$ico))
                        $ico = 'file1.gif';
                }

            } else page::assign('item.active', 1);
            page::assign('obj.ico', $pach.$ico);


            page::assign('item.name', $obj->name);

            if (!empty($this->funct_name) && function_exists($this->funct_name))
                $notice = call_user_func($this->funct_name, $obj);
            else $notice = '';
            page::assign('item.notice', $notice);

            page::assign('obj.url', 'http://'.domains::curDomain()->getName().$obj->_url);

            if (isset($_SESSION['TREE_OPEN_NODES'][$obj->id]) && $obj->issetChildren()) {
                page::assign('close', ' open');
                page::assign('sub_items', $sub_items);
            } else {
                page::assign('close', (($obj->issetChildren()) ? ' closed' : ''));
                page::assign('sub_items', '');
            }

            $items .= page::parse($parse['items']);
        }

        if (!empty($items) && $with_frame){
            page::assign('items', $items);
            $items = page::parse($parse['frame_items']);
        }

        return $items;
    }

    // Генерирует список дерево объектов
    function getHTML($templ_name = 'orm_tree') {

        if (file_exists(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl')){
            include(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl');

            $main_url = system::au().'/'.system::url(0).'/';
            page::assign('main_url', $main_url);
            page::assign('plus_table', $this->plus_table);

            // Помечаем веточку закрытой
            if (system::isAjax() && (system::url(2) == 'close' || system::url(2) == 'open') && system::issetUrl(3)) {

                if (system::url(2) == 'open' && is_numeric(system::url(3)))
                    $_SESSION['TREE_OPEN_NODES'][system::url(3)] = 1;
                else if (isset($_SESSION['TREE_OPEN_NODES'][system::url(3)]))
                    unset($_SESSION['TREE_OPEN_NODES'][system::url(3)]);

                system::stop();

            } else
                // По POST запросу формируем ветку дерева
                if (isset($_POST['id'])) {

                    if (!empty($_POST['id'])) {

                        //system::log($_POST['id']);

                        $_POST['id'] = str_replace('phtml_', '', $_POST['id']);
                        $pos = strpos($_POST['id'], '_');
                        $_POST['id'] = substr($_POST['id'], 0, strlen($_POST['id']) - (strlen($_POST['id']) - $pos));

                        //system::log($_POST['id']);
                    }

                    $_SESSION['TREE_OPEN_NODES'][$_POST['id']] = 1;

                    ormPages::init(array($_POST['id'] => 1));

                    // Строим одни уровень дерева
                    $tmp = $this->build_menu($_POST['id'], $TEMPLATE, false);
                    //echo str_replace('`%`', '%', $tmp);
                    echo $tmp;

                    system::stop();
                }

            // Парсим дерево разделов
            if (isset($_SESSION['TREE_OPEN_NODES']))
                ormPages::init($_SESSION['TREE_OPEN_NODES']);

            $subm = $this->build_menu($this->root_id, $TEMPLATE);
            page::assign('sub_items', $subm);
            page::assign('item.id', $this->root_id);
            page::assign('item.parent_id', '');
            page::assign('item.url', $this->root_url);
            page::assign('item.active', 1);
            page::assign('item.name', $this->root_title);
            page::assign('item.notice', '');
            page::assign('obj.ico', '/css_mpanel/tree/images/classes/core.png');
            page::assign('obj.url', '');
            page::assign('close', (empty($subm)) ? '' : ' open');
            page::fParse('items', $TEMPLATE['items']);
            page::fParse('frame_items', $TEMPLATE['frame_items']);

            // Выводим список прав для веточек
            $item = '';
            $zagl_width = 0;
            while (list($key, $right) = each ($this->rights)){

                if ($right['java'] == 1)
                    $zagl_width += 20;

                if ($right == 'empty' || (!user::issetRight($right['name']) && $right['name'] != 'getUrl()'))
                    $item .= ($right != 'empty') ? '' : page::parse($TEMPLATE['item_right_null']);
                else {
                    page::assign('title', $right['title']);
                    page::assign('image_style', $right['class']);
                    page::assign('url', $main_url.$right['name'].'/');
                    page::assign('hide_in_root', (($right['show_in_root']) ? '' : ' hide_in_root'));


                    if ($right['name'] == 'getUrl()')
                        $templ = 'item_right_url';
                    else
                        $templ = ($right['java'] == 1) ? 'item_right_del' : 'item_right';

                    if (!empty($right['list'])) {
                        $templ = 'item_right_list';
                        page::assign('list_id', $right['list']);
                        page::assign('list_html', $right['list_html']);
                        page::fParse('tree_list', $TEMPLATE['tree_list']);
                    }

                    $item .= page::parse($TEMPLATE[$templ]);
                }
            }

            page::assign('rights', $item);
            page::assign('act_link', $this->rightActive);
            page::assign('remove_link', $this->rightRemove);
            page::assign('load_link', $this->ajaxLink);
            page::assign('root_id', $this->root_id);
            page::assign('zagl_width', $zagl_width);

            // Текст сообщения об удалении элементов
            if (empty($this->del_title) || (empty($this->del_text))) {
                $this->del_title = lang::get('TABLE_DROP_TITLE');
                $this->del_text = lang::get('TABLE_DROP_TEXT');
            }
            page::assign('del_title', $this->del_title);
            page::assign('del_text', $this->del_text);

            page::assign('width', $this->width);
            page::assign('width2', $this->width2);
            page::assign('left', $this->width - $this->width2);

            page::assign('style_prefix', (($this->miniStyle) ? '_mini' : ''));

            page::assign('isEditable', (($this->rightEdit) ? 1 : 0));
            page::assign('isChangeActive', (($this->rightActive) ? 1 : 0));
            page::assign('isDragged', (($this->rightRemove) ? 1 : 0));
            page::assign('isShowRight', ((empty($this->rights)) ? 0 : 1));

            return page::parse($TEMPLATE['main']);

        }
    }
}

?>