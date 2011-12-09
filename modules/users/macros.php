<?php

class usersMacros {


    /**
	* @return stirng
	* @param string $templ_name - Шаблон оформления
	* @desc МАКРОС: Выводит форму авторизации или ссылку на личный кабинет текущего пользователя
	*/
 	function authForm($templ_name = 'auth') {

    	$templ_file = '/users/'.$templ_name.'.tpl';
        $TEMPLATE = page::getTemplate($templ_file);

	    if (!is_array($TEMPLATE))
	    	return page::errorNotFound('users.authForm', $templ_file);

	   	if (user::isGuest()) {

            if (!empty($_SESSION['auth_error'])) {
            	$_SESSION['auth_error'] = 0;
            	page::fParse('auth_error', $TEMPLATE['auth_error']);
            } else page::assign('auth_error','');

			return page::parse($TEMPLATE['frame_form']);

	   	} else {

            page::assign('user_id', user::get('id'));
            page::assign('user_name', user::get('name'));
            return page::parse($TEMPLATE['frame_account']);

	   	}

	}

	/**
	* @return stirng
	* @param string $templ_name - Шаблон оформления
	* @desc МАКРОС: Выводит форму напоминания пароля
	*/
 	function recover($templ_name = 'recover') {

    	$templ_file = '/users/'.$templ_name.'.tpl';
        $TEMPLATE = page::getTemplate($templ_file);

	    if (!is_array($TEMPLATE))
	    	return page::errorNotFound('users.recover', $templ_file);

	   	if (user::isGuest()) {

	   	    if (!empty($_SESSION['reg_user_error'])) {
                page::assign('error_msg', $_SESSION['reg_user_error']);
                page::assign('error_field', $_SESSION['reg_user_error2']);
                $_SESSION['reg_user_error'] = '';
            } else {
                page::assign('error_msg', '');
                page::assign('error_field', '');
            }

            page::assign('login_or_email', ((isset($_SESSION['SAVING_POST']['login_or_email'])) ? $_SESSION['SAVING_POST']['login_or_email'] : ''));

			return page::parse($TEMPLATE['frame']);

	   	} else return lang::get('USERS_ALREADY_LOGGED');
	}

    /**
	* @return stirng
	* @param string $templ_name - Шаблон оформления
	* @desc МАКРОС: Выводит форму редактирования акаунта
	*/
	function editForm($templ_name = 'edit') {

        if (!user::isGuest()) {

	     	$templ_file = '/users/'.$templ_name.'.tpl';
	        $TEMPLATE = page::getTemplate($templ_file);

		    if (!is_array($TEMPLATE))
		    	return page::errorNotFound('users.editForm', $templ_file);


	        $user = user::getObject();

            // Парсим все поля
            $fields = $user->getClass()->loadFields();
		    while(list($name, $field) = each($fields))
		    	page::assign('obj.'.$name, $user->__get($name));

            // Выводим аватару пользователя
            if ($user->avatara != '' && isset($TEMPLATE['photo'])) {
                page::assign('photo', $user->avatara);
                page::fParse('photo', $TEMPLATE['photo']);
            } else page::assign('photo', '');

            // Сообщение об ошибках
            if (!empty($_SESSION['reg_user_error'])) {
                page::assign('error_msg', $_SESSION['reg_user_error']);
                page::assign('error_field', $_SESSION['reg_user_error2']);
                $_SESSION['reg_user_error'] = '';
            } else {
                page::assign('error_msg', '');
                page::assign('error_field', '');
            }

	        return page::parse($TEMPLATE['frame']);
        }
	}

    /**
	* @return stirng
	* @param string $templ_name - Шаблон оформления
	* @desc МАКРОС: Выводит форму изменения пароля
	*/
 	function changePassword($templ_name = 'change_password') {

    	$templ_file = '/users/'.$templ_name.'.tpl';
        $TEMPLATE = page::getTemplate($templ_file);

	    if (!is_array($TEMPLATE))
	    	return page::errorNotFound('users.changePassword', $templ_file);

	   	if (!user::isGuest()) {
            if (system::url(2) == 'ok')
                return page::parse($TEMPLATE['frame_ok']);
            else {

                if (system::url(2) == 'error') {
                    page::assign('error_msg', lang::get('USERS_CHANGE_PSW_MSG'));
                    page::assign('error_field', 'current_password');
                } else {
                    page::assign('error_msg', '');
                    page::assign('error_field', '');
                }

                return page::parse($TEMPLATE['frame']);
            }
	   	}
	}


    /**
	* @return stirng
	* @param string $templ_name - Шаблон оформления
	* @desc МАКРОС: Выводит форму регистрации пользователя
	*/
    function addForm($templ_name = 'add') {

        if (reg::getKey('/users/reg')) {

	     	$templ_file = '/users/'.$templ_name.'.tpl';
	        $TEMPLATE = page::getTemplate($templ_file);

		    if (!is_array($TEMPLATE))
		    	return page::errorNotFound('users.addForm', $templ_file);

            // Парсим все поля
            $fields = ormClasses::get('user')->loadFields();
		    while(list($name, $field) = each($fields))
		    	page::assign('obj.'.$name, '');

            // Вывод сообщения об ошибках
            if (!empty($_SESSION['reg_user_error'])) {
                page::assign('error_msg', $_SESSION['reg_user_error']);
                page::assign('error_field', $_SESSION['reg_user_error2']);
                $_SESSION['reg_user_error'] = '';
            } else {
                page::assign('error_msg', '');
                page::assign('error_field', '');
            }

            // Согласие с условиями регистрации
            page::assign('checked', ((isset($_SESSION['SAVING_POST']['confirm'])) ? 'checked' : ''));

            page::assignSavingPost();

	        return page::parse($TEMPLATE['frame']);
        }
	}






}

?>