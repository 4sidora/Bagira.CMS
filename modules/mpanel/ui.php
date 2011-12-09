<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс содержит набор методов для организации интерфейса панели администрирования.
	С его помощью можно создавать:
		-	Стандартные кнопки форм
		-	Показывать всплывающие подсказки после перезагрузки страницы
		-	Подсвечивать поля после ошибок
		-	Поле формы "SelectBox"
		-	Поле формы "CheckBox"
		-	Поле формы "Загрузка файла"
		-	Поле формы "Список файлов"
		-	Форма история изменений
*/

class ui {

    private static $name  = '';
    private static $back_button = '';
    private static $cancel_button = '';
    private static $buttons  = array();
    private static $left_buttons  = array();

    private static $pageHeader = '';
    private static $pageNavibar = array();
    private static $topControl = '';
    private static $bottomControl = '';
    private static $leftBlocks = '';
    private static $newLink  = array();

    public static $stop = false;



    static function addLeftButton($title, $link) {
        self::$left_buttons[] = array('title' => $title, 'link' => $link);
    }



    /**
     * @return null
     * @param string $fields_array - Массив с названиями полей которые нужно подсветить после перезагрузки страницы
     * @desc Подсвечивает после перезагрузки страницы нужные поля формы
     */
    static function selectErrorFields($fields_array) {
        $_SESSION['SelectField'] = $fields_array;
    }

    // ***************************        Сообщение MessageBox()       ********************************

    /**
     * @return null
     * @param string $title - Заголовок сообщения
     * @param string $text - Текст сообщения
     * @param boolean $replace - Если true, предыдущее сгенерированное сообщение будет затерто
     * @desc Выводит всплывающее сообщение (используя JQueryUI) после перезагрузки страницы
     */
    static function MessageBox($title, $text = '', $replace = false) {

        if (empty($_SESSION['MessageBox']) || $replace)
            $_SESSION['MessageBox'] = array('title' => $title, 'text' => $text);
        //  else print_r($_SESSION['MessageBox']);

    }

    // Генерирует HTML для сообщения MessageBox()
    private static function getMessageBoxHTML() {

        if (!empty($_SESSION['MessageBox']['title'])) {
            page::assign("mb_title", $_SESSION['MessageBox']['title']);
            page::assign("mb_text", $_SESSION['MessageBox']['text']);
        } else {
            page::assign("mb_title", '');
            page::assign("mb_text", '');
        }

        if (isset($_SESSION['MessageBox']))
            $_SESSION['MessageBox'] = '';
    }


    // ***************************        Стандартные кнопки newButton()       ********************************


    /**
     * @return null
     * @param string $title - Текст для кнопки
     * @param string $link - Ссылка на страницу или JavaScript-функция
     * @param string $list_id - ID HTML-тега для списка выбора. Не обязательный параметр.
     * @param string $list_html - Содержимое для списка выбора. Не обязательный параметр.
     * @desc Добавляет новую кнопку
     */
    static function newButton($title, $link, $list_id = '', $list_html = '') {
        self::$buttons[] = array('title' => $title, 'link' => $link, 'list_id' => $list_id, 'list_html' => $list_html);
    }

    static function setBackButton($link) {
        self::$back_button = $link;
    }

    static function setCancelButton($link) {
        self::$cancel_button = $link;
    }

    // Генерирует HTML для кнопок
    private static function getButtonsHTML() {

        $but = '';

        if (file_exists(MODUL_DIR.'/mpanel/template/buttons.tpl')) {

            include(MODUL_DIR.'/mpanel/template/buttons.tpl');

            while (list($key, $button) = each (self::$buttons)) {

                $show = false;

                if (substr($button['link'], 0, 4) == 'java') {

                    // Событие JavaScript
                    $show = true;
                    $onclick = $button['link'];

                } else {

                    // Проверяем есть ли такое право
                    if (!empty($button['link'])) {

                        $mas = array();
                        $tmp = strtok($button['link'], '/');
                        while ($tmp != '') {
                            $mas[] = $tmp;
                            $tmp = strtok("/");
                        }

                        if (!empty($mas[1]))
                            $show = user::issetRight($mas[1], $mas[0]);
                        else if (!empty($mas[0]))
                            $show = user::issetModule($mas[0]);
                    }

                    $onclick = system::au().$button['link'];
                }

                $templ = (empty($button['list_html'])) ? 'main_button' : 'main_button_list';

                if ($show) {
                    page::assign("title", $button['title']);
                    page::assign("link", $onclick);
                    page::assign("list_id", $button['list_id']);
                    page::assign("list", $button['list_html']);
                    $but .= page::parse($TEMPLATE[$templ]);
                }
            }

            // Вывод кнопки "назад"
            if (!empty(self::$back_button)) {

                if (!empty($but)){

                    page::assign("title", lang::get('PANEL_BTN_BACK2'));
                    page::assign("title2", lang::get('PANEL_BTN_BACK3'));
                    page::assign("link", system::au().self::$back_button);
                    $but .= page::parse($TEMPLATE['back_button']);

                } else {

                    page::assign("title", lang::get('PANEL_BTN_BACK2'));
                    page::assign("link", system::au().self::$back_button);
                    $but .= page::parse($TEMPLATE['back_button2']);
                }
            }

            // Вывод кнопки "Отмена"
            if (!empty(self::$cancel_button)) {

                if (!empty($but)){

                    page::assign("title", lang::get('PANEL_BTN_BACK'));
                    page::assign("title2", lang::get('PANEL_BTN_BACK3'));
                    page::assign("link", system::au().self::$cancel_button);
                    $but .= page::parse($TEMPLATE['back_button']);

                } else {

                    page::assign("title", lang::get('PANEL_BTN_BACK'));
                    page::assign("link", system::au().self::$cancel_button);
                    $but .= page::parse($TEMPLATE['back_button2']);
                }
            }
        }

        page::assign("down", '');
        page::fParse("buttons", $but);
        page::assign("down", 'down');
        page::fParse("buttons_down", $but);

    }

    /**
     * @return null
     * @param string $macros - Имя макроса для вывода в шаблон содержимого кнопки
     * @param string $title - Текст для кнопки
     * @param string $link - Ссылка на страницу или JavaScript-функция
     * @desc Генерирует код кнопки и парсит в шаблон
     */
    public static function insertButton($macros, $title, $link) {

        $but = '';

        if (file_exists(MODUL_DIR.'/mpanel/template/buttons.tpl')) {

            include(MODUL_DIR.'/mpanel/template/buttons.tpl');

            $show = false;

            if (substr($link, 0, 4) == 'java') {

                // Событие JavaScript
                $show = true;
                $onclick = $link;

            } else {

                // Проверяем есть ли такое право
                if (!empty($link)) {

                    $mas = array();
                    $tmp = strtok($link, '/');
                    while ($tmp != '') {
                        $mas[] = $tmp;
                        $tmp = strtok("/");
                    }

                    if (!empty($mas[1]))
                        $show = user::issetRight($mas[1], $mas[0]);
                    else if (!empty($mas[0]))
                        $show = user::issetModule($mas[0]);
                }

                $onclick = system::au().$link;
            }

            if ($show) {
                page::assign("title", $title);
                page::assign("link", $onclick);
                $but = page::parse($TEMPLATE['main_button']);
            }

        }

        page::assign($macros, $but);
    }

    /**
     * @return null
     * @param string $field_name - Имя поля формы и макроса для вывода элемента в шаблон
     * @param string $file_name - Имя прикрепленного файла, если есть.
     * @desc Элемент формы для загрузки файла
     */
    public static function loadFile($field_name, $file_name = '', $templ_name = 'load_file', $id = '') {

        if (file_exists(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl')) {

            include(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl');

            if (!empty($file_name) && file_exists(ROOT_DIR.$file_name)) {

                page::assign('sh_fileblock', '');
                page::assign('sh_selblock', 'display:none;');

            } else {

                page::assign('sh_fileblock', 'display:none;');
                page::assign('sh_selblock', '');
            }

            if (system::fileExtIs($file_name, array('flv', 'png', 'jpg', 'jpeg', 'gif')))
                page::assign('link_type', 'href="#" onclick="$.prettyPhoto.open(\'%value%\');return false;"');
            else
                page::assign('link_type', 'href="%value%" target="_target"');


            if (!empty($file_name) && file_exists(ROOT_DIR.$file_name) && !is_Dir(ROOT_DIR.$file_name)) {

                $info = stat(ROOT_DIR.$file_name);
                if (system::fileExtIs($file_name, array('png', 'gif', 'jpg', 'jpeg'))){
                    $size = getimagesize(ROOT_DIR.$file_name);

                    if ($templ_name == 'load_file')
                        $size_img =  lang::get('FILELOAD_SCALE').$size[0].'x'.$size[1].'px';
                    else
                        $size_img =  ', '.$size[0].'x'.$size[1].'px';

                    $view_text = lang::get('FILELOAD_VIEW');
                    $sh_dmini = '';

                } else {
                    $size_img =  '';
                    $view_text = lang::get('FILELOAD_DOWNL');
                    $sh_dmini = 'display:none;';
                }

                page::assign('text.view', $view_text);
                page::assign('sh_dmini', $sh_dmini);

                $ext = system::fileExt($file_name);
                $extList = array('png', 'fpng', 'gif', 'jpg', 'jpeg', 'doc', 'docx', 'rar', 'pdf', 'xls', 'xlsx');
                page::assign('file.ext', (in_array($ext, $extList)) ? $ext : 'na');

                if ($templ_name == 'load_file')
                    page::assign('file.size', lang::get('FILELOAD_SIZE').round(($info[7]/1024), 0).lang::get('FILELOAD_KB').$size_img);
                else
                    page::assign('file.size', round(($info[7]/1024), 0).lang::get('FILELOAD_KB').$size_img);

            } else {
                page::assign('file.size', '');
                page::assign('file.ext', 'na');
            }

            if (!empty($file_name) && !file_exists(ROOT_DIR.$file_name))
                page::assign('value', '');
            else
                page::assign('value', $file_name);  

            page::assign('sname', $field_name);
            page::assign('sid', $field_name);

            page::assign('text.change', lang::get('FILELOAD_CHANGE'));
            page::assign('text.replace_hint', lang::get('FILELOAD_REPLACE'));
            page::assign('text.download', lang::get('FILELOAD_BTN_DOWNL'));
            page::assign('text.replace', lang::get('FILELOAD_BTN_REPLACE'));
            page::assign('text.or', lang::get('FILELOAD_OR'));


            if (!empty($id))
                page::assign('sid', $id);

            $ret = page::parse($TEMPLATE['frame']);
            page::assign($field_name, $ret);
            return $ret;
        }

    }

    /**
     * @return null
     * @param string $field_name - Имя поля формы и макроса для вывода элемента в шаблон
     * @param string $value - Список файлов
     * @desc Элемент формы для загрузки списка файлов
     */
    public static function listFile($field_name, $value) {

        if (file_exists(MODUL_DIR.'/mpanel/template/list_file.tpl')) {

            include(MODUL_DIR.'/mpanel/template/list_file.tpl');

            /*

   if (system::fileExtIs($file_name, array('flv', 'png', 'jpg', 'jpeg', 'png')))
       page::assign('link_type', 'href="#" onclick="$.prettyPhoto.open(\'%value%\');"');
   else
       page::assign('link_type', 'href="%value%" target="_target"');
            */

            //

            if (!empty($value)) {

                $files = explode(";", $value);

                $items = '';
                while(list($key, $file_name) = each($files)) {

                    if (!empty($file_name) && file_exists(ROOT_DIR.$file_name) && !is_Dir(ROOT_DIR.$file_name)) {

                        $info = stat(ROOT_DIR.$file_name);
                        if (system::fileExtIs($file_name, array('png', 'gif', 'jpg', 'jpeg'))){
                            $size = getimagesize(ROOT_DIR.$file_name);
                            $size_img =  ', '.$size[0].'x'.$size[1].'px';
                        } else $size_img =  '';

                        page::assign('file.name', system::fileName($file_name));
                        page::assign('file.url', $file_name);
                        page::assign('file.ext', system::fileExt($file_name));
                        page::assign('file.size', round(($info[7]/1024), 0).' Кбайт'.$size_img);
                        $items .= page::parse($TEMPLATE['files']);
                    }
                }
                page::assign('files', $items);
                return page::parse($TEMPLATE['frame_view']);

            } else {
                page::assign('value', $value);
                page::assign('sname', $field_name);

                return page::parse($TEMPLATE['frame_add']);
            }
        }
    }



    // ***************************        Основные методы        ********************************

    // Поменять стандартный Титл и Заголовок страницы
    static function setHeader($val) {
        self::$pageHeader = $val;
    }

    /**
     * @return null
     * @param string $title - Заголовок элемента
     * @param string $link - Ссылка элемента
     * @desc Добавляет новый элемент в навибар админки
     */
    static function setNaviBar($title, $link = '') {
        self::$pageNavibar[] = array('title' => $title, 'link' => $link);
    }

    /**
     * @return null
     * @param string $title - Заголовок ссылки
     * @param string $link - Адрес ссылки
     * @desc Добавляет новую ссылку, рядом с настройками модуля
     */
    static function setNewLink($title, $link, $anchor = '') {
        self::$newLink[] = array('title' => $title, 'link' => $link, 'anchor' => $anchor);
    }

    // Включить левую панель, с указанным контентом
    static function setLeftPanel($val) {
        self::$leftBlocks = $val;
    }

    // Указывает содержимое нижней управляющей панели
    static function setBottomControl($val) {
        self::$bottomControl = $val;
    }

    // Указывает содержимое верней управляющей панели
    static function setTopControl($val) {
        self::$topControl = $val;
    }

    // Генерирует основное содержимое страницы для панели администрирования
    static function getMainHTML($content) {


        if (file_exists(MODUL_DIR.'/mpanel/template/config.tpl'))
            include(MODUL_DIR.'/mpanel/template/config.tpl');

        if (isset($TEMPLATE))
            self::getConfig($TEMPLATE);

        // Основное содержимое страницы
        page::assign('bottom_control', self::$bottomControl);
        page::assign('top_control', self::$topControl);
        self::getButtonsHTML();

        // Заголовок и Титл страницы
        page::assign('header', substr(self::$pageHeader, 0, 100));
        $title = (!empty(self::$pageHeader)) ? self::$pageHeader.' | ' : '';
        page::globalVar("title", $title.'Bagira.CMS');

        // Вывод всплывающего сообщения
        self::getMessageBoxHTML();

        if (!empty($_SESSION['SelectField'])) {
            page::assign('select_field', $_SESSION['SelectField']['select']);
            page::assign('focus_field', $_SESSION['SelectField']['focus']);
            $_SESSION['SelectField'] = '';
        } else {
            page::assign('select_field', '');
            page::assign('focus_field', '');
        }

        // Панель слева с кнопками
        $left_buttons = '';
        if (!empty(self::$left_buttons)) {

            while (list($num, $button) = each(self::$left_buttons)) {


                page::assign('link', system::au().'/'.system::url(0).'/'.$button['link']);
                page::assign('title', $button['title']);

                $act = ($button['link'] == system::url(1)) ? '_active' : '';

                $left_buttons .= page::parse($TEMPLATE['bt_item'.$act]);
            }
        }

        if (!empty($left_buttons)) {

            // Панель слева с кнопками
            page::assign('left_buttons', $left_buttons);
            page::assign('content', $content);
            return page::parse($TEMPLATE['left_buttons']);

        } else if (empty(self::$leftBlocks)) {

            // Обычная страница
            page::assign('content', $content);
            return page::parse($TEMPLATE['content']);

        } else {

            // Панель слева с деревом
            page::assign('left_column', self::$leftBlocks);
            page::assign('content', $content);
            return page::parse($TEMPLATE['left_column']);
        }

    }



    // Всяка хрень типа настроек модуля
    private static function getConfig($TEMPLATE) {


        $cModul = '';
        if (!empty(self::$newLink)) {

            // Настройки в виде списка
            while(list($num, $link) = each(self::$newLink)) {
                if (user::issetRight($link['link'])) {
                    $anchor = (!empty($link['anchor'])) ? '#'.$link['anchor'] : '';
                    page::assign('url', system::au().'/'.system::url(0).'/'.$link['link'].$anchor);
                    page::assign('title', $link['title']);
                    $cModul .= page::parse($TEMPLATE['config_item']);
                }
            }

            if (!empty($cModul)) {
                page::assign('config_items', $cModul);
                page::assign('text.settings', lang::get('TEXT_SETTINGS'));
                $cModul = page::parse($TEMPLATE['config_frame']);
            }

        } else if (user::issetRight('settings')) {

            // Обычные настройки
            page::assign('url_settings', system::au().'/'.system::url(0).'/settings');
            page::assign('text.settings', lang::get('TEXT_SETTINGS'));
            $cModul = page::parse($TEMPLATE['config_module']);

        }
        page::assign('settings', $cModul);



        // Ссылка "Помощь"
        page::assign('text.help', lang::get('TEXT_HELP'));
        page::fParse('help_link', $TEMPLATE['help_link']);

        // Ссылка "На сайт"
        page::assign('url_site', domains::curDomain()->getName().languages::pre());
        page::assign('text.to_site', lang::get('TEXT_TOSITE'));
        page::fParse('to_site', $TEMPLATE['to_site']);


        // Аккаунт пользователя и кнопка выход
        page::assign('username', user::get('name'));
        page::assign('user_url', ((user::issetRight('profile', 'core')) ? system::au().'/core/profile' : '#'));

        page::assign('url_exit', system::au().'/logout');
        page::assign('text.exit', lang::get('TEXT_URL_EXIT'));

        page::fParse('mpanel_config', $TEMPLATE['account']);


        // Формируем цепочку-заголовок
        page::assign("title", lang::module(system::url(0)));
        page::assign("link", system::au().'/'.system::url(0));
        $cep = page::parse($TEMPLATE['navibar_link']);

        for($i = 0; $i < count(self::$pageNavibar); $i++) {
            page::assign("title", substr(self::$pageNavibar[$i]['title'], 0, 100));
            page::assign("link", system::au().self::$pageNavibar[$i]['link']);
            $t = (empty(self::$pageNavibar[$i]['link'])) ? '' : '_link';
            $cep .= page::parse($TEMPLATE['navibar'.$t]);
        }
        page::assign('navibar', $cep);
        page::fParse('navibar', $TEMPLATE['navibar_frame']);


    }








    // ***************************        Выпадающий список SelectBox()       ********************************

    /**
     * @desc Ф-я необходимая для коректной работы ф-и SelectBox
     */
    private static function getSBItem($id, $name, $active_elem, $templ) {

        page::assign('item.id', $id);
        page::assign('item.name', $name);
        $act = '';

        if (is_array($active_elem)) {

            reset($active_elem);
            while (list($key, $act_val) = each ($active_elem)) {

                if (is_array($act_val)) {
                    $keys = array_keys($act_val);
                    $act_val = $act_val[$keys[0]];
                }
                if ($id == $act_val) $act = ' selected="selected"';

            }
            page::assign('active_name', '');

        } else {

            if ($id == $active_elem)
                $act = ' selected="selected"';
            if ($id == $active_elem)
                page::assign('active_name', $name);

        }

        page::assign('item.act', $act);

        return page::parse($templ);
    }

    /**
     * @return unknown - Создает макрос $form_name и связывает его с результатом выполнения
     * @param string $form_name - Название генерируемого элемента в форме и название макроса для вывода в шаблоне
     * @param array $data - Входящие данные, массив практичеки любой структуры
     * @param integer $active_elem - Значение активного элемента
     * @param integer $size - Визульный размер списка при выводе
     * @param string $null - Название нулевого элемента, если есть имеет значение 0
     * @param string $java - Название JavaScript-функции для события onChange.
     * @param string $templ_name - Название шаблона для построения списка
     * @param string $form_id - Специальный ID списка в форме. Если не указан ID == $form_name
     * @param string $addit_url - Дополнительный параметр. Используется для JavaScript-списков
     * @param string $title -  Специальный заголовок для списка. Используется для JavaScript-списков
     * @desc Универсальная ф-я для построения выпадающего списка.
     */
    static function SelectBox($form_name, $data, $active_elem, $size = 200, $null = '', $java='', $templ_name = 'selectbox', $form_id = '', $addit_url = '', $title = '') {

        if (file_exists(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl')){

            include(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl');

            if (!empty($java))
                $java = ' onChange="'.$java.'"';
            page::assign('selbox.java', $java);

            $title = (!empty($title)) ? $title.': ' : '';
            page::assign('selbox.title', $title);

            if (is_array($active_elem))
                page::assign('selbox.active', 0);
            else
                page::assign('selbox.active', $active_elem);

            page::assign('selbox.size', $size);
            page::assign('selbox.name', $form_name);
            page::assign('selbox.id', (empty($form_id)) ? $form_name : $form_id);
            page::assign('selbox.addit_url', $addit_url);

            $items = '';
            if (!empty($null))
                $items .= self::getSBItem(0, $null, $active_elem, $TEMPLATE['item']);


            $keys = array_keys($data);
            if (isset($keys[0]) && isset($data[$keys[0]]) && is_array($data[$keys[0]])) {

                // Данные как выборка из БД
                while (list($key, $item) = each ($data)){
                    $keys = array_keys($item);
                    $items .= self::getSBItem($item[$keys[0]], $item[$keys[1]], $active_elem, $TEMPLATE['item']);
                }

            } else if (isset($data['start'])) {

                // Данные как числовой ряд
                if ($data['start'] < $data['stop'])
                    for ($i = $data['start']; $i < $data['stop']+1; $i++)
                        $items .= self::getSBItem($i, $i, $active_elem, $TEMPLATE['item']);
                else
                    for ($i = $data['start']; $i > $data['stop']; $i--)
                        $items .= self::getSBItem($i, $i, $active_elem, $TEMPLATE['item']);

            } else {

                // Данные как языковые переменные
                while (list($key, $name) = each ($data))
                    $items .= self::getSBItem($key, $name, $active_elem, $TEMPLATE['item']);

            }

            page::assign('selbox.items', $items);
            $result = page::parse($TEMPLATE['frame']);

            page::assign($form_name, $result);
            return $result;

        }

    }



    // ***************************        CheckBox()       ********************************

    /**
     * @return unknown - Создает макрос $form_name и связывает его с результатом выполнения
     * @param string $form_name - Название генерируемого элемента в форме и название макроса для вывода в шаблоне
     * @param string $value - Значение которое будет содержать элемент
     * @param integer $active - Если 1 активен, 0 - не активен.
     * @param string $title - Подпись к элементу, если не указана - выводится просто галочка
     * @param string $java - Имя JavaScript-функции для события onClick()
     * @param string $form_id - Специальный ID элемента в форме. Если не указан ID == $form_name
     * @desc Строит checkbox (элемент флажок) с описанием.
     */
    static function CheckBox($form_name, $value, $active, $title='', $java='', $form_id = '', $class = 'checkbox') {

        if (!empty($java))
            $java = ' onclick="'.$java.'"';

        if (empty($form_id))
            $form_id = $form_name;

        if (!empty($title))
            $title = '&nbsp;&nbsp;<label for="'.$form_id.'">'.$title.'</label>';

        $checked  = ($active == 1) ? ' checked' : '';
        $t = '<input name="'.$form_name.'" type="checkbox"  id="'.$form_id.'" class="'.$class.'" value="'.$value.'"'.$checked.' '.$java.'>'.$title;

        page::assign($form_name, $t);
        return $t;
    }


    // Генерирует форму истории изменения объекта
    static function getHistoryTable($obj_id) {

        $obj_id = system::checkVar($obj_id, isInt);

        function getState($val) {
            if ($val == info)
                return 'info';
            else if ($val == error)
                return 'error';
            else if ($val == warning)
                return 'warning';
        }

        function getEditUser($val, $obj) {

            if (user::issetRight('user_upd', 'users'))
                return '<a href="'.system::au().'/users/user_upd/'.$obj['rev_user_id'].'" target="_blank">'.$val.'</a>';
            else
                return $val;
        }

        $mas = db::q('SELECT rev_state, rev_user, rev_user_id, rev_datetime,
        			concat(rev_message, " <b>", o_name, "</b>") rev_msg, rev_ip
		        	FROM <<revue>>, <<objects>>
		        	WHERE rev_obj_id = "'.$obj_id.'" and
		        		rev_obj_id = o_id', records);


        $table = new uiTable($mas);

        $table->addColumn('rev_state', 'Важность', 0, false, false, 'getState');
        $table->addColumn('rev_user', 'Пользователь', 0, false, false, 'getEditUser');
        $table->addColumn('rev_msg', 'Действие', 600);
        $table->addColumn('rev_datetime', 'Дата / Время', 0, false, false, 'viewDateTime2');
        $table->addColumn('rev_ip', 'IP');

        $table->emptyText('В журнале нет записей!');

        return $table->getHTML();
    }


    static function checkClasses() {

        $classes = '';
        $cl_name = func_get_args();

        while(list($num, $val) = each($cl_name))
            if (!ormClasses::get($val))
                $classes .= '<li>'.$val.'</li>';

        if (!empty($classes)) {

            $modul = lang::module(system::url(0));
            if (empty($modul))
                $modul = system::url(0);

            ui::$stop = true;

            ui::MessageBox(
                str_replace('%name%', $modul, lang::get('TEXT_CLASS_NOT_FOUND')),
                lang::get('TEXT_CLASS_NOT_FOUND2').'<ul>'.$classes.'</ul>'
            );
        }
    }

    /**
     * @return HTML
     * @param int $obj_id - ID объекта
     * @param int $field_id - ID поля
     * @desc Создает элемент формы для установления связей с объектами.
     */
    static function objectLinks($object, $field_id, $prefix = '', $fname = '', $width = 400, $templ_name = 'objectLinks') {

        if (file_exists(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl')) {
            include(MODUL_DIR.'/mpanel/template/'.$templ_name.'.tpl');

            $field = new ormField($field_id);

            if (is_a($field, 'ormField') && !$field->issetErrors()) {

                page::assign('field_id', $field->getSName().$prefix);
                page::assign('ol_width', $width);

                if (empty($fname))
                    page::assign('field_name', $field->getSName().$prefix);
                else
                    page::assign('field_name', $fname);

                $list = '';
                if ($object->id > 0) {

                    $sel = new ormSelect();
                    $sel->fields('id, name');

                    if ($field->getRelType() == 0)
                        $sel->depends($object->id, $field->id());
                    else if ($field->getRelType() == 1)
                        $sel->contains($object->id, $field->id());
                    else
                        $sel->contains($object->id);

                    while($obj = $sel->getObject()) {

                        page::assign('id', $obj->id);
                        page::assign('name', $obj->name);

                        $list .= page::parse($TEMPLATE['object_link']);
                    }

                } else if ($field->getRelType() == 2) {

                    $objs = $object->getParents();
                    if (count($objs) > 0)
                        foreach($objs as $val) {
                            if ($obj = ormPages::get($val['parent_id'])) {
                                page::assign('id', $obj->id);
                                page::assign('name', $obj->name);

                                $list .= page::parse($TEMPLATE['object_link']);
                            }
                        }
                }

                page::assign('list', $list);

                $ret = page::parse($TEMPLATE['frame']);
                page::assign($field->getSName(), $ret);

                return $ret;
            }
        }
    }

    static function checkObjectLinks() {

        if (system::url(0) == 'getObjectLinksTree') {

            if (file_exists(MODUL_DIR.'/mpanel/template/objectLinks.tpl'))
                include(MODUL_DIR.'/mpanel/template/objectLinks.tpl');

            $tree = new ormTree(457, 0);
            $tree->miniStyle();
            $tree->setClass('ormPage');
            $tree->setRoot(0, reg::getKey(ormPages::getPrefix().'/title_prefix'));
            $tree->setRightAjaxLoad('tree');

            page::assign('tree', $tree->getHTML());
            page::assign('parram', system::url(1));

            echo page::parse($TEMPLATE['tree_frame']);
            system::stop();

        } else if (system::url(0) == 'getObjectLinks') {

            if (file_exists(MODUL_DIR.'/mpanel/template/objectLinks.tpl')) {
                include(MODUL_DIR.'/mpanel/template/objectLinks.tpl');

                if (!$obj = ormPages::get(system::url(1)))
                    $obj = ormObjects::get(system::url(1));

                if (is_a($obj, 'ormObject') && !$obj->issetErrors()) {

                    page::assign('id', $obj->id);
                    page::assign('name', $obj->name);
                    page::assign('url', $obj->url);
                    page::assign('field_name', system::url(2));
                    page::assign('field_id', system::url(3));

                    if ($obj->getClass()->isPage())
                        echo page::parse($TEMPLATE['new_object_link']);
                    else
                        echo page::parse($TEMPLATE['object_link']);
                }
            }

            system::stop();

        } else if (system::url(0) == 'findObjectLinks' && isset($_POST['query'])) {

            // Формируем список подсказок
            $sel = new ormSelect();
            $sel->findInPages();
            $sel->fields('name');
            $sel->where('name', 'LIKE', '%'.$_POST['query'].'%');

            $list = $list2 = '';
            while($obj = $sel->getObject()) {
                $zapi = ($sel->getObjectNum() != 0) ? ', ' : '';
                $list .= $zapi."'".$obj->name."'";
                $list2 .= $zapi." ['".$obj->id."', '".system::url(1)."' ]";
            }

            echo "{ query:'".$_POST['query']."', suggestions:[".$list."], data:[".$list2."] }";
            system::stop();
        }

    }

}


?>