<?php

$MODNAME['subscription'] = 'Рассылка';

$RIGHT['subscription']['list'] = 'Список категорий рассылки';
$RIGHT['subscription']['subscribe_add'] = 'Добавление рассылки';
$RIGHT['subscription']['subscribe_upd'] = 'Редактирование рассылки';
$RIGHT['subscription']['subscribe_del'] = 'Удаление рассылки';
$RIGHT['subscription']['subscribe_act'] = 'Активность рассылки';
$RIGHT['subscription']['subscribe_history'] = 'История изменения рассылки';
$RIGHT['subscription']['settings'] = 'Настройки модуля';

$RIGHT['subscription']['msg'] = 'Список выпусков рассылки';
$RIGHT['subscription']['msg_add'] = 'Создание нового выпуска';
$RIGHT['subscription']['msg_upd'] = 'Редактирование выпуска';
$RIGHT['subscription']['msg_del'] = 'Удаление выпуска';
$RIGHT['subscription']['msg_send'] = 'Рассылка писем подписчикам';

$RIGHT['subscription']['user'] = 'Список подписчиков';
$RIGHT['subscription']['user_add'] = 'Добавление подписчика';
$RIGHT['subscription']['user_upd'] = 'Редактрование подписчика';
$RIGHT['subscription']['user_del'] = 'Удаление подписчика';
$RIGHT['subscription']['user_addlist'] = 'Добавление списка подписчиков';






$LANG['SUBSCRIBE_BTN_ADD'] = 'Добавить рассылку';
$LANG['SUBSCRIBE_BTN_ADD2'] = 'Создать новый выпуск';
$LANG['SUBSCRIBE_BTN_ADD3'] = 'Добавить подписчика';
$LANG['SUBSCRIBE_BTN_ADD4'] = 'Добавить список подписчиков';

$LANG['SUBSCRIBE_TT1'] = 'Название';
$LANG['SUBSCRIBE_TT2'] = 'Дата последней рассылки';
$LANG['SUBSCRIBE_TT3'] = 'Кол-во подписчиков';

$LANG['SUBSCRIBE_USER_TT1'] = 'E-mail';
$LANG['SUBSCRIBE_USER_TT2'] = 'ФИО';

$LANG['SUBSCRIBE_MSG_TT1'] = 'Название выпуска';
$LANG['SUBSCRIBE_MSG_TT2'] = 'Статус';

$LANG['SUBSCRIBE_MSG'][1] = 'Не завершена отправка %part% части!';
$LANG['SUBSCRIBE_MSG'][2] = 'Выполняется отправка %part% части!';
$LANG['SUBSCRIBE_MSG'][3] = 'Отправлено %part1% из %part2% частей.';
$LANG['SUBSCRIBE_MSG'][4] = 'Ожидает отправки';
$LANG['SUBSCRIBE_MSG'][5] = 'Успешно отправлен';
$LANG['SUBSCRIBE_MSG'][6] = 'в';


$LANG['SUBSCRIBE_MSG_MB_TITLE'] = 'Доступ запрещен!';
$LANG['SUBSCRIBE_MSG_MB_TEXT'] = 'Вы не можете редактировать выпуск, так как в данный момент идет рассылка сообщений. Дождитесь окончания рассылки.';



$LANG['SUBSCRIBE_TEXT'][1] = 'Укажите список E-mail`ов разделенных любым символом.';
$LANG['SUBSCRIBE_TEXT'][2] = 'Список адресов для добавления';
$LANG['SUBSCRIBE_TEXT'][3] = 'Для выбора нескольких значений удерживайте нажатой клавишу Ctrl';
$LANG['SUBSCRIBE_TEXT'][4] = 'Подписать на рассылки';

$LANG['dtp'] = '<br /><br />Внимание! Вместе с рассылкой будут полностью удалены все подписчики и сообщения!';

$LANG['SUBSCRIBE_DEL_TITLE2'] = 'Удаление рассылки';
$LANG['SUBSCRIBE_DEL_TEXT2'] = 'Вы действительно хотите удалить рассылку <b>&name&</b>? '.$LANG['dtp'];

$LANG['SUBSCRIBE_DEL_TITLE_MULTI2'] = 'Удаление рассылок';
$LANG['SUBSCRIBE_DEL_TEXT_MULTI2'] = 'Вы действительно хотите удалить выбранные рассылки?'.$LANG['dtp'];

$LANG['SUBSCRIBE_ADDED_EMAILS'] = 'В список подпичиков было добавлено ';
$LANG['SUBSCRIBE_AE'][1] = 'адрес';
$LANG['SUBSCRIBE_AE'][2] = 'адреса';
$LANG['SUBSCRIBE_AE'][5] = 'адресов';


$LANG['SUBSCRIBE_TEXT_SETTINGS'][1] = 'Количество писем отправляемых за одну итерацию:';
$LANG['SUBSCRIBE_TEXT_SETTINGS'][2] = 'Ограничение сервера на отправку писем:';
$LANG['SUBSCRIBE_TEXT_SETTINGS'][3] = 'Укажите количество писем отправляемых за одну итерацию!';
$LANG['SUBSCRIBE_TEXT_SETTINGS'][4] = 'Количество писем должно быть положительным числом!';
$LANG['SUBSCRIBE_TEXT_SETTINGS'][5] = 'Укажите ограничение сервера на отправку писем.';
$LANG['SUBSCRIBE_TEXT_SETTINGS'][6] = 'Количество писем должно быть положительным числом!';

$LANG['SUBSCRIBE_TEXT_SEND'][1] = 'Тема письма';
$LANG['SUBSCRIBE_TEXT_SEND'][2] = 'Будет отправлена ';
$LANG['SUBSCRIBE_TEXT_SEND'][3] = 'часть писем.';
$LANG['SUBSCRIBE_TEXT_SEND'][4] = 'Рассылка сообщений';
$LANG['SUBSCRIBE_TEXT_SEND'][5] = 'Предпросмотр текста письма';
$LANG['SUBSCRIBE_TEXT_SEND'][6] = 'Отправить выпуск';
$LANG['SUBSCRIBE_TEXT_SEND'][7] = 'Будет продолжена рассылка';
$LANG['SUBSCRIBE_TEXT_SEND'][8] = 'части писем.';

$LANG['SUBSCRIBE_TEXT_SEND'][9] = 'Отправка ';
$LANG['SUBSCRIBE_TEXT_SEND'][10] = ' части не завершена!';
$LANG['SUBSCRIBE_TEXT_SEND'][11] = 'Что бы продолжить рассылку этой части, нажмите кнопку &quot;Отправить выпуск подписчикам&quot;.';


$LANG['SUBSCRIBE_TS'][1] = 'часть.';
$LANG['SUBSCRIBE_TS'][2] = 'части.';
$LANG['SUBSCRIBE_TS'][5] = 'частей.';

$LANG['SUBSCRIBE_HIST_START'] = 'Начал отправку %part% части выпуска <b>%name%</b> рассылки';
$LANG['SUBSCRIBE_HIST_CONTINUE'] = 'Возобновил отправку %part% части выпуска <b>%name%</b> рассылки';
$LANG['SUBSCRIBE_HIST_STOP'] = 'Завершил отправку %part% части выпуска <b>%name%</b> рассылки';


$LANG['SUBSCRIBE_NAMEFORMAT'][1] = 'Только Имя';
$LANG['SUBSCRIBE_NAMEFORMAT'][2] = 'Фамилия Имя';
//$LANG['SUBSCRIBE_NAMEFORMAT'][3] = 'Имя Отчество';
//$LANG['SUBSCRIBE_NAMEFORMAT'][4] = 'Фамилия Имя Отчество';

?>