<?php

class controller {

    // Обработчик авторизации пользователя
    public function authAction() {

   		if (!user::auth(system::POST('login'), system::POST('passw'))) {

            if (system::isAjax()) {

                system::json(array('error' => 1));
                
            } else {

                $_SESSION['auth_error'] = system::POST('login');
                system::redirect('/users/auth-page');
            }
   		}

        if (system::isAjax()) {

            $param = array(
                'id' => user::get('id'),
                'login' => user::get('login'),
                'name' => user::get('name'),
                'surname' => user::get('surname'),
                'avatara' => user::get('avatara')
            );

            system::json($param);

        } else {

            if (!empty($_POST['back_url']))
                system::redirect($_POST['back_url'], true);
            else
                system::redirect('/');
        }
 	}

    public function social_authAction(){
        user::socialAuth(system::url(2));
    }

    public function social_auth_confirmAction(){
        user::socialAuthConfirm();
    }


    // Страница авторизации пользователя
    public function auth_pageAction() {

        if (!user::isGuest())
            system::redirect('/users/edit');

 		page::globalVar('h1', lang::get('USERS_NO_AUTH'));
 	    page::globalVar('title', lang::get('USERS_NO_AUTH'));

        if (!empty($_SESSION['auth_error']))
            page::assign('login', $_SESSION['auth_error']);
        else
            page::assign('login', '');

   		return page::macros('users')->authForm('auth_page');

 	}

    // Обработчик выхода пользователя из системы
    public function logoutAction() {

        user::logout(false);

        if (system::isAjax()) {

            system::json(array('error' => 0));

        } else {

            if (!empty($_POST['back_url']))
                system::redirect($_POST['back_url'], true);
            else
                system::redirect('/');
        }
 	}


    // Страница восстановления пароля
 	public function recoverAction() {

 		page::globalVar('h1', lang::get('USERS_RECOVER_H1'));
 	    page::globalVar('title', lang::get('USERS_RECOVER_H1'));
   		return page::macros('users')->recover();
 	}

    // Формирование письма для подтверждения смены пароля
 	public function recover_passwAction() {

        // Проверка капчи
     	if (!system::validCapcha('random_image')) {

            $answer = array(
                'msg' => lang::get('SITE_CAPHCA'),
                'field' => 'captcha'
            );

            if (system::isAjax()) {
                               
                system::json($answer);

            } else {

                system::savePostToSession();
                system::saveErrorToSession('recover', $answer);
                system::redirect('/users/recover');
            }
	    }

        // Ищем нужного пользователя
        if (empty($answer)) {
            
             $sel = new ormSelect('user');
             $sel->where(
                 $sel->logOR(
                     $sel->val('login', '=', system::POST('login_or_email')),
                     $sel->val('email', '=', system::POST('login_or_email'))
                 )
             );
             $sel->limit(1);

             if ($user = $sel->getObject()) {

                 // Формируем временный ключ восставновления
                 $key = md5(date('d.m.Y').$user->id);
                 $user->md5_flag = $key;
                 $user->save();

                 // Отправляет письмо с инструкциями
                 $url_pre = 'http://'.domains::curDomain()->getName().languages::pre();
                 page::assign('url', $url_pre.'/users/send_passw/'.$key);
                 page::assign('login', $user->login);
                 page::assign('name', $user->name);
                 system::sendMail('/users/mails/recover1.tpl', $user->email);
             }
        }

        if (system::isAjax())
            system::json(array('ok' => 1));
        else
            system::redirect('/users/ok/recover');
 	}

    // Формирование письма с новым паролем
 	public function send_passwAction() {

        page::globalVar('h1', lang::get('USERS_RECOVER_H1'));
	 	page::globalVar('title', lang::get('USERS_RECOVER_H1'));

    	if ($userKey = system::checkVar(system::url(2), isMD5)) {

        	// Ищем нужного пользователя
	        $sel = new ormSelect('user');
	        $sel->where('md5_flag', '=', $userKey);
	        $sel->limit(1);

	        if ($user = $sel->getObject()) {

	        	$key = md5(date('d.m.Y').$user->id);

		    	if ($key == $userKey) {

                    // Генерируем новый пароль
                    $passw = rand(100000, 999999);

					// Если пользователь был заблокирован за неправильный ввод паролей, активируем его.
					if ($user->error_passw == reg::getKey('/users/errorCountBlock')) {
						$user->active = 1;
						$user->error_passw = 0;
					}

					// Обнуляем флаг восстановления и сохраняем новый пароль
					$user->md5_flag = '';
					$user->password = $passw;
		        	$user->save();

                    // Отправляет письмо с инструкциями
		            page::assign('login', $user->login);
            		page::assign('name', $user->name);
		            page::assign('passw', $passw);
		            system::sendMail('/users/mails/recover2.tpl', $user->email);

					return lang::get('USERS_RECOVER_MSG2');
		    	}
	    	}
    	}

   		return ormPages::get404();
 	}

    // Страница регистрации пользователя
 	public function addAction() {

        if (!user::isGuest())
        	system::redirect('/users/edit');

   		page::globalVar('h1', lang::get('USERS_ADD_H1'));
 	    page::globalVar('title', lang::get('USERS_ADD_H1'));
   		return page::macros('users')->addForm();
 	}


    // Обработчик регистрации пользователя
 	public function add_procAction() {

        $answer = array();

        if (!reg::getKey('/users/reg'))
            system::redirect('/');

   		if (!user::isGuest())
        	system::redirect('/users/edit');

     	// Проверка капчи
        if (!system::validCapcha('random_image'))
            $answer = array('msg' => lang::get('SITE_CAPHCA'), 'field' => 'captcha');
	    
        // Проверка согласия с условиями оферты
        if (reg::getKey('/users/confirm') && !system::POST('confirm', isBool))
            $answer = array('msg' => lang::get('USERS_COMFIRM'), 'field' => 'confirm');

        // Добавляем объект
        if (empty($answer)) {
            
            $obj = new ormObject();
            $obj->setParent(41);  	// Устанавливаем группу "Пользователи сайта"
            $obj->setClass('user');
            $obj->tabuList('def_modul', 'active', 'last_visit', 'last_ip', 'groups');
            $obj->loadFromPost();
            $obj->active = 1;
            $obj->email = $obj->newVal('login');
			
			$max_tickets = reg::getKey('/booking/max_tickets');
			$obj->place_limit = ($max_tickets == '' || $max_tickets == 0) ? 6 : $max_tickets;

            if ($obj->save()) {

                if (reg::getKey('/users/activation')) {

                    // Регистрация с проверкой

                    // Формируем временный ключ активации пользователя
                    $key = md5(date('d.m.Y').'activate'.$obj->id);
                    $obj->md5_flag = $key;
                    $obj->active = 0;
                    $obj->save();

                    // Отправляем письмо
                    $url_pre = 'http://'.domains::curDomain()->getName().languages::pre();
                    page::assign('url', $url_pre.'/users/activate/'.$key);
                    page::assign('passw', system::POST('password'));
                    page::assign('login', $obj->login);
                    page::assign('name', $obj->name);
                    system::sendMail('/users/mails/activate.tpl', $obj->email);

                    $_SESSION['user_email'] = $obj->login;

                } else {

                    // Регистрация без проверки

                    // Отправляем письмо
                    page::assign('passw', system::POST('password'));
                    page::assign('login', $obj->login);
                    page::assign('name', $obj->name);
                    system::sendMail('/users/mails/registration.tpl', $obj->email);

					// Автоматическая авторизация
					user::authHim($obj);
                }

                

                // Все хорошо. Пользователь добавлен.
                if (system::isAjax())
                    system::json(array('ok' => 1));
                else
                    system::redirect('/users/ok');

            } else {

                // Произошли ошибки
                
                if ($obj->issetErrors(32))
                    $answer = array('msg' => lang::get('USERS_ISSET'), 'field' => 'login');
                else {
                    $tmp = $obj->getErrorFields();
                    $answer = array('msg' => $obj->getErrorListText(' '), 'field' => $tmp['focus']);
                }
            }
        }

        if (!system::isAjax()) {

            system::savePostToSession();
            system::saveErrorToSession('add_user', $answer);

            if (!empty($_POST['back_url']))
                system::redirect($_POST['back_url'], true);
            else
                system::redirect('/users/add');

        } else
            system::json($answer);
 	}


    // Сообщение после успешной регистрации или об отправке сообщения для восстановления пароля
    public function okAction() {

        if (system::url(2) == 'recover') {

            // Восстановление пароля
            page::globalVar('h1', lang::get('USERS_RECOVER_H1'));
            page::globalVar('title', lang::get('USERS_RECOVER_H1'));

            return lang::get('USERS_RECOVER_MSG');

        } else if (reg::getKey('/users/activation') && isset($_SESSION['user_email'])) {

            // Регистрация - С проверкой пользователя
            page::globalVar('h1', lang::get('USERS_ADD_H1'));
 	    	page::globalVar('title', lang::get('USERS_ADD_H1'));

            return str_replace('%email%', $_SESSION['user_email'], lang::get('USERS_ADD_MSG'));

        } else {

            // Регистрация - Без активации пользователя
            page::globalVar('h1', lang::get('USERS_ADD_H1'));
 	    	page::globalVar('title', lang::get('USERS_ADD_H1'));

            return lang::get('USERS_ADD_MSG3');
        }
    }


 	// Подтверждение регистрации
 	public function activateAction() {

    	if (!reg::getKey('/users/reg') || !reg::getKey('/users/activation'))
            system::redirect('/');

    	if ($userKey = system::checkVar(system::url(2), isMD5)) {

        	// Ищем нужного пользователя
	        $sel = new ormSelect('user');
	        $sel->where('md5_flag', '=', $userKey);
	        $sel->limit(1);

	        if ($user = $sel->getObject()) {

	        	$key = md5(date('d.m.Y').'activate'.$user->id);

		    	if ($key == $userKey) {

					$user->active = 1;
					$user->md5_flag = '';
		        	$user->save();

                    // Авторизуем пользователя
                    user::authHim($user);

                    page::globalVar('h1', lang::get('USERS_ADD_H1'));
 	    			page::globalVar('title', lang::get('USERS_ADD_H1'));
					return lang::get('USERS_ADD_MSG2');
		    	}
	    	}
    	}

   		return ormPages::get404();
 	}

    // Страница редактирования личных данных пользователя
 	public function editAction() {

   		if (user::isGuest())
        	system::redirect('/users/add');

   		page::globalVar('h1', lang::get('USERS_EDIT_H1'));
 	    page::globalVar('title', lang::get('USERS_EDIT_H1'));
   		return page::macros('users')->editForm();

 	}

 	// Обработчик изменения пользователя
 	public function edit_procAction() {

   		if (user::isGuest())
        	system::redirect('/users/add');

        if (system::url(2) == 'del-photo') {
            $obj = user::getObject();
            $obj->avatara = '';
            $obj->save();
            
            if (!empty($_POST['back_url']))
				system::redirect($_POST['back_url'], true);
			else
				system::redirect('/users/edit');
        }

        $obj = user::getObject();
        $obj->tabuList('def_modul', 'active', 'last_visit', 'last_ip', 'groups');
        $obj->loadFromPost();
        $obj->active = 1;

        if ($obj->save()) {

            cache::delete('user'.$obj->id);

            $answer = array('msg' => lang::get('USERS_CHANGE_MSG'), 'field' => '');

        } else {

        	system::savePostToSession();

            $tmp = $obj->getErrorFields();
            $answer = array('msg' => $obj->getErrorListText(' '), 'field' => $tmp['focus']);
		}

        if (!system::isAjax()){

            system::saveErrorToSession('edit_user', $answer);

            if (!empty($_POST['back_url']))
				system::redirect($_POST['back_url'], true);
			else
				system::redirect('/users/edit');

        } else
            system::json($answer);
 	}

    // Страница изменения пароля
 	public function change_passwordAction() {

 		page::globalVar('h1', lang::get('USERS_CHANGE_PSW_H1'));
 	    page::globalVar('title', lang::get('USERS_CHANGE_PSW_H1'));
   		return page::macros('users')->changePassword();
 	}

    public function change_password_procAction() {

        if (user::isGuest())
        	system::redirect('/users/add');

        $cur_password = system::POST('current_password', isPassword);
        $new_passw = system::POST('password', isPassword);
        $new_passw2 = system::POST('password2', isPassword);

        if ($cur_password == user::get('password')) {

            if ($new_passw && $new_passw == $new_passw2)

                if ($user = user::getObject()) {

                    $user->password = system::POST('password');

                    if ($user->save()) {

                        if (system::isAjax())
                            system::json(array('ok' => 1));
                        else
                            system::redirect('/users/change_password/ok');
                    }
                }

        } else {

            $answer = array('msg' => lang::get('USERS_CHANGE_PSW_MSG'), 'field' => 'current_password');

            if (!system::isAjax()) {

                system::saveErrorToSession('change_password', $answer);
                system::redirect('/users/change_password');

            } else 
                system::json($answer);
        }

        system::redirect('/users/change_password');
    }




}

?>