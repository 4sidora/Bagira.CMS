<?php

$MODNAME['constructor'] = 'Конструктор';

$RIGHT['constructor']['tree'] = 'Иерархия классов данных';

$RIGHT['constructor']['class_upd'] = 'Изменение класса данных';
$RIGHT['constructor']['class_add'] = 'Добавление класса данных';
$RIGHT['constructor']['class_del'] = 'Удаление класса данных';

$RIGHT['constructor']['fgroup_upd'] = 'Изменение группы полей';
$RIGHT['constructor']['fgroup_add'] = 'Добавление группы полей';
$RIGHT['constructor']['fgroup_del'] = 'Удаление группы полей';
$RIGHT['constructor']['fgroup_moveto'] = 'Перемещение группы полей';

$RIGHT['constructor']['field_upd'] = 'Изменение поля';
$RIGHT['constructor']['field_add'] = 'Добавление поля';
$RIGHT['constructor']['field_del'] = 'Удаление поля';
$RIGHT['constructor']['field_moveto'] = 'Перемещение поля';

$RIGHT['constructor']['separator_add'] = 'Добавление разделителя';
$RIGHT['constructor']['separator_upd'] = 'Изменение разделителя';

$LANG['BTN_NEW_CLASS'] = 'Добавить класс данных';
$LANG['BTN_NEW_FGROUP'] = 'Добавить группу полей';
$LANG['BTN_NEW_LIST'] = 'Добавить';

$LANG['CONSTR_LIST'] = 'Справочник: ';

$LANG['CONSTR_FORM_FIELD'][1] = 'Имя класса';
$LANG['CONSTR_FORM_FIELD'][2] = 'Системное имя класса';
$LANG['CONSTR_FORM_FIELD'][3] = 'Системный';
$LANG['CONSTR_FORM_FIELD'][4] = 'Использовать как справочник';
$LANG['CONSTR_FORM_FIELD'][5] = 'Введите название класса!';
$LANG['CONSTR_FORM_FIELD'][6] = 'Введите системное название класса!';
$LANG['CONSTR_FORM_FIELD'][7] = 'Только уникальные имена объектов';
$LANG['CONSTR_FORM_FIELD'][8] = 'Склонение названия объекта';
$LANG['CONSTR_FORM_FIELD'][9] = 'Класс по умолчанию для подразделов';
$LANG['CONSTR_FORM_FIELD'][10] = 'Название объекта в винительном и родительном падежах, через запятую. Используется для формирования заголовков при работе в панели управления. Например, чтобы определить склонения для слова «страница», используйте словосочетания: добавить (что?) «страницу», изменение (чего?) «страницы».';
$LANG['CONSTR_FORM_FIELD'][11] = 'Укажите класс который должен автоматически выбираться при создании подразделов для объектов данного класса. Используется в модуле «Структура».';

$LANG['CONSTR_FORM_FIELD2'][1] = 'Имя группы';
$LANG['CONSTR_FORM_FIELD2'][2] = 'Системное имя группы';
$LANG['CONSTR_FORM_FIELD2'][3] = 'Отображать в формах';
$LANG['CONSTR_FORM_FIELD2'][4] = 'Запретить редактирование';

$LANG['CONSTR_FORM_FIELD3'][1] = 'Название поля';
$LANG['CONSTR_FORM_FIELD3'][2] = 'Системное название поля';
$LANG['CONSTR_FORM_FIELD3'][3] = 'Подсказка';
$LANG['CONSTR_FORM_FIELD3'][4] = 'Тип';
$LANG['CONSTR_FORM_FIELD3'][5] = 'Справочник';
$LANG['CONSTR_FORM_FIELD3'][6] = 'Видимое';
$LANG['CONSTR_FORM_FIELD3'][7] = 'Наследуемое';
$LANG['CONSTR_FORM_FIELD3'][8] = 'Участвует в поиске';
$LANG['CONSTR_FORM_FIELD3'][9] = 'Участвует в фильтрах';
$LANG['CONSTR_FORM_FIELD3'][10] = 'Обязательное';
$LANG['CONSTR_FORM_FIELD3'][11] = 'Системное';

$LANG['CONSTR_FORM_FIELD3'][12] = 'Максимальный размер (байт)';
$LANG['CONSTR_FORM_FIELD3'][13] = 'Уникальное';
$LANG['CONSTR_FORM_FIELD3'][14] = 'Быстрое добавление';
$LANG['CONSTR_FORM_FIELD3'][15] = 'Тип отношения';
$LANG['CONSTR_FORM_FIELD3'][16] = 'Специальное';
$LANG['CONSTR_FORM_FIELD3'][17] = 'Заголовок разделителя';
$LANG['CONSTR_FORM_FIELD3'][18] = 'Отступ сверху';
$LANG['CONSTR_FORM_FIELD3'][19] = 'Высота поля';

// Список возможных типов полей.
// Номера 0 и 1 зарезервированы, это разделители.
$LANG['CONSTR_TYPE_LIST'][10] = 'Строка - Текстовое поле';
$LANG['CONSTR_TYPE_LIST'][15] = 'E-mail - Текстовое поле';
$LANG['CONSTR_TYPE_LIST'][20] = 'URL - Текстовое поле';
$LANG['CONSTR_TYPE_LIST'][25] = 'Дата - Календарь';
$LANG['CONSTR_TYPE_LIST'][30] = 'Время - Текстовое поле';
$LANG['CONSTR_TYPE_LIST'][32] = 'Дата и время - Календарь';
$LANG['CONSTR_TYPE_LIST'][35] = 'Пароль - Поле с паролем';
$LANG['CONSTR_TYPE_LIST'][40] = 'Число - Числовое поле';
$LANG['CONSTR_TYPE_LIST'][45] = 'Число с точкой - Числовое поле';
$LANG['CONSTR_TYPE_LIST'][47] = 'Цена - Числовое поле';
$LANG['CONSTR_TYPE_LIST'][50] = 'Логический - Галочка';
$LANG['CONSTR_TYPE_LIST'][55] = 'Большой текст - Большое текстовое поле';
$LANG['CONSTR_TYPE_LIST'][60] = 'HTML–текст - Визуальный редактор';
//$LANG['CONSTR_TYPE_LIST'][65] = '';
$LANG['CONSTR_TYPE_LIST'][70] = 'Файл - Выбор файла';
$LANG['CONSTR_TYPE_LIST'][73] = 'Список файлов';
$LANG['CONSTR_TYPE_LIST'][75] = 'Изображение - Выбор файла';
$LANG['CONSTR_TYPE_LIST'][80] = 'Видео - Выбор файла';
$LANG['CONSTR_TYPE_LIST'][85] = 'Флеш-ролик - Выбор файла';
$LANG['CONSTR_TYPE_LIST'][90] = 'Справочник - Выпадающий список';
$LANG['CONSTR_TYPE_LIST'][95] = 'Справочник - Множественный выбор';
$LANG['CONSTR_TYPE_LIST'][97] = 'Подчиненый справочник';
$LANG['CONSTR_TYPE_LIST'][100] = 'Связь с объектом';
$LANG['CONSTR_TYPE_LIST'][105] = 'Теги';


$LANG['CONSTR_TYPE_LIST_name'][10] = 'Строка - Текстовое поле';
$LANG['CONSTR_TYPE_LIST_name'][15] = 'E-mail - Текстовое поле';
$LANG['CONSTR_TYPE_LIST_name'][20] = 'URL - Текстовое поле';

$LANG['CONSTR_RELTYPE'][0] = 'Содержит';
$LANG['CONSTR_RELTYPE'][1] = 'Принадлежит';
$LANG['CONSTR_RELTYPE'][2] = 'Выбор родителя';


$LANG['CONSTR_BASE_FIELD1'] = 'Номер объекта';
$LANG['CONSTR_BASE_FIELD2'] = 'Дата изменения';
$LANG['CONSTR_BASE_FIELD3'] = 'Дата создания';
$LANG['CONSTR_BASE_FIELD4'] = 'Имя объекта';

$LANG['CONSTR_BASE_FIELD'] = 'Название';
$LANG['CONSTR_BASE_FIELD_E'] = 'Введите имя!';
$LANG['CONSTR_BASE_FIELD_E2'] = 'Поле «%title%» обязательно для заполнения!';
$LANG['CONSTR_BASE_FIELD_E3'] = 'Указанный вами адрес электронного ящика не правильный!';
$LANG['CONSTR_BASE_FIELD_E4'] = 'Ссылка указана в неправильном формате!';
$LANG['CONSTR_BASE_FIELD_E5'] = 'Не определен шаблон оформления для поля «%title%».';
$LANG['CONSTR_BASE_FIELD_E6'] = 'Укажите целое число!';
$LANG['CONSTR_BASE_FIELD_E7'] = 'Укажите дробное или целое число!';
$LANG['CONSTR_BASE_FIELD_E8'] = 'Вы неправильно указали стоимость товара или услуги!';


$LANG['CONSTR_SHOWHIDE1'] = 'Показать дополнительные поля';
$LANG['CONSTR_SHOWHIDE2'] = 'Скрыть дополнительные поля';
$LANG['CONSTR_ADDTEXT'] = 'Добавление ';
$LANG['CONSTR_ADDTEXT_DEF'] = 'объекта';




?>