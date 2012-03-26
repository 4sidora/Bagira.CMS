<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Статический класс для работы с текущим пользователем.
    Основные возможности:
    	- Авторизация (вход/выход)
    	- Определение группы, статуса пользователя
    	- Получение всей информации о пользователе
    	- Проверка прав доступа
*/

define('SOCIAL_TYPE_TWITTER', 1);
define('SOCIAL_TYPE_FACEBOOK', 2);
define('SOCIAL_TYPE_VK', 3);
define('SOCIAL_TYPE_GOOGLE', 4);
define('SOCIAL_TYPE_YANDEX', 5);

class user {

    private static $right = array();
    private static $defModul = '';

    private static $isGuest = true;
    private static $isAdmin = false;
    private static $guestGroup = 48;

    private static $obj;

    // Иницилизация класса
    static function init() {

     	if (isset($_SESSION['curUser']['name']) && $_SESSION['curUser']['name'] != 'none'){

      		self::$isGuest = false;
        	self::$isAdmin = $_SESSION['curUser']['isAdmin'];


            $key = 'user'.$_SESSION['curUser']['id'];

            if (!(self::$obj = cache::get($key))) {
              
                self::$obj = ormObjects::get($_SESSION['curUser']['id']);

                // Записываем в кэш
                cache::set($key, self::$obj);
            }

            /*
            self::$obj->last_visit = date('Y-m-d H:i:s');
            self::$obj->last_ip = $_SERVER['REMOTE_ADDR'];

            self::$obj->save();    */
      	}

       if (!isset($_SESSION['curUser']['name']))
       		self::guestCreate();

    }

    

    // Создает пользователя гостя
    private static function guestCreate() {

	   self::$isGuest = true;
       self::$isAdmin = false;

       self::updateSession(0, '', 0, 'none', 'none');
    }

    // Обновляет данные текущей сессии
    private static function updateSession($id, $login, $name, $email) {

       $_SESSION['curUser']['id'] = $id;
       $_SESSION['curUser']['login'] = $login;
       $_SESSION['curUser']['name'] = $name;
       $_SESSION['curUser']['email'] = $email;
       $_SESSION['curUser']['isAdmin'] = self::$isAdmin;
   
    }

    // Выход пользователя
    static function logout($redirect = true) {

    	system::log(lang::get('EXIT_USER'), info);
     	session_unset();

     	self::guestCreate();

        if ($redirect)
     	    system::redirect('/');
    }

    // Автоматическая авторизация указанного пользователя
    static function authHim(ormObject $user) {

        if ($user->isInheritor('user')) {

            self::$obj = $user;

            self::$obj->last_visit = date('Y-m-d H:i:s');
            self::$obj->last_ip = $_SERVER['REMOTE_ADDR'];
            self::$obj->error_passw = 0;
            self::$obj->save();

            // Загружаем данные и обновляем сессию
            self::getRights();
            self::$isAdmin = (count(self::$right) == 0) ? false : true;
            self::$isGuest = false;

            self::updateSession(self::$obj->id,
                                self::$obj->login,
                                self::$obj->name,
                                self::$obj->email);

            system::log(lang::get('ENTER_USER'), info);

            return true;
        }

        return false;
    }

    // Авторизация
    static function auth($login, $password) {

    	$ret = false;
     	$login = system::checkVar($login, isString);

		$sel = new ormSelect('user');
	    $sel->where(
	        $sel->val('active', '=', 1),
			$sel->val('login', '=', trim($login)),
			$sel->containedIn('user_group',
				$sel->val('active', '=', 1)
			)
		);
        $sel->limit(1);

  		if(self::$obj = $sel->getObject()) {

    		if (self::$obj->password == system::checkVar($password, isPassword)) {

                $ret = self::authHim(self::$obj);

      		} else {

        		$max_error = reg::getKey('/users/errorCountBlock');

          		//Смотрим, если у юзера уже N неправильных паролей, то блокируем его
            	if ((self::$obj->error_passw + 1) >= $max_error && $max_error > 0) {
             		self::$obj->active = 0;
             		self::sendMailBlock(self::$obj);
                }

                self::$obj->error_passw++;
                self::$obj->save();

                if (!self::$obj->active) {

                	//записываем что пользователь заблокирован по своей дурости из-за не знания пароля
                 	system::log(str_replace('%user%', $login, str_replace('%count%', $max_error, lang::get('BLOCKED_USER'))), error);

                } else {

                	//Записываем в журнал о неправильном вводе пароля
                 	system::log(str_replace('%user%', $login, lang::get('ERROR_PASSWORD')), error);
                }
            }
        }

        return $ret;
    }

    public static function socialAuth($service_name){

        if (user::isGuest()) {

            switch ($service_name) {
                case 'twitter':
                    self::authTwitter();
                    break;

                case 'facebook':
                    self::authFacebook();
                    break;

                case 'vk':
                    self::authVK();
                    break;

                case 'yandex':
                    self::authYandex();
                    break;

                case 'google':
                    self::authGoogle();
                    break;
            }
        }
    }

    // авторизация Google
    private static function authGoogle() {

        if (reg::getKey('/users/yandex_bool')) {

            try {
                $openid = new LightOpenID('http://'.$_SERVER['SERVER_NAME']);

                if(!$openid->mode) {

                    $openid->identity = 'https://www.google.com/accounts/o8/id';
                    $openid->required = array('contact/email');

                    header('Location: ' . $openid->authUrl());

                } elseif($openid->mode == 'cancel') {

                    self::closeWindowAndOpen('/');
                    system::stop();

                } else {

                    // Получение данных пользователя при успешной аутентификации
                    if ($openid->validate()) {

                        $attrs = $openid->getAttributes();
                        $name = substr($attrs['contact/email'], 0, strpos($attrs['contact/email'], '@'));

                        $user_info = array(
                            'identity' => $openid->identity,
                            'login' => $attrs['contact/email'],
                            'email' => $attrs['contact/email'],
                            'first_name' => $name,
                            'last_name' => '',
                            'social' => 'google',
                            'social_type' => SOCIAL_TYPE_GOOGLE
                        );

                        self::checkSocialUser($user_info);

                    } else {
                        echo 'Ошибка входа на сайт';
                        system::stop();
                    }
                }
            } catch(ErrorException $e) {
                echo $e->getMessage();
            }       
        }
    }

    // авторизация Yandex
    private static function authYandex() {

        if (reg::getKey('/users/yandex_bool')) {
            
            try {
                $openid = new LightOpenID('http://'.$_SERVER['SERVER_NAME']);

                if(!$openid->mode) {
    
                    $openid->identity = 'http://www.yandex.ru/';
                    $openid->required = array('contact/email');
                    $openid->optional = array('namePerson');

                    header('Location: ' . $openid->authUrl());

                } elseif($openid->mode == 'cancel') {

                    self::closeWindowAndOpen('/');
                    system::stop();

                } else {

                    // Получение данных пользователя при успешной аутентификации
                    if ($openid->validate()) {

                        $attrs = $openid->getAttributes();
                        $login = substr($openid->identity, 24, strlen($openid->identity) - 25);

                        $user_info = array(
                            'identity' => $openid->identity,
                            'login' => 'ya.'.$login,
                            'email' => $attrs['contact/email'],
                            'first_name' => strtok($attrs['namePerson'],' '),
                            'last_name' => strtok(' '),
                            'social' => 'yandex',
                            'social_type' => SOCIAL_TYPE_YANDEX
                        );

                        self::checkSocialUser($user_info);

                    } else {
                        echo 'Ошибка входа на сайт';
                        system::stop();
                    }
                }

            } catch(ErrorException $e) {
                echo $e->getMessage();
            }
        }
    }


    // авторизация VK
	private static function authVK() {

        if (reg::getKey('/users/vk_bool')) {
            
            $app_id = reg::getKey('/users/vk_id');
            $app_secret = reg::getKey('/users/vk_secret');
            $back_url = "http://".$_SERVER['SERVER_NAME']."/users/social-auth/vk";

            if (isset($_GET['code'])){

                $url = "https://api.vkontakte.ru/oauth/access_token?client_id=".$app_id."&client_secret=".$app_secret."&code=".$_GET['code'];
                $response = json_decode(@file_get_contents($url));

                if (isset($response->error))
                    system::redirect('/');
    
                $arrResponse = json_decode(@file_get_contents("https://api.vkontakte.ru/method/getProfiles?uid={$response->user_id}&access_token={$response->access_token}&fields=last_name"))->response;

                $user_info = array(
                    'identity' => 'http://vk.com/id'.$arrResponse[0]->uid,
                    'login' => $arrResponse[0]->uid,
                    'first_name' => $arrResponse[0]->first_name,
                    'last_name' => $arrResponse[0]->last_name,
                    'social' => 'vk',
                    'social_type' => SOCIAL_TYPE_VK
                );

                self::checkSocialUser($user_info);

            } else {
                system::redirect("http://api.vkontakte.ru/oauth/authorize?client_id=".$app_id."&scope=&redirect_uri=".$back_url."&response_type=code");
            }
        }
 	}

    private static function authFacebook() {

        if (reg::getKey('/users/facebook_bool')) {
            $app_id = reg::getKey('/users/facebook_id');
            $app_secret = reg::getKey('/users/facebook_secret');
            $back_url = "http://".$_SERVER['SERVER_NAME']."/users/social-auth/facebook";

            $code = (isset($_REQUEST["code"])) ? $_REQUEST["code"] : 0;

            if(empty($code)) {
                $_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
                $dialog_url = "http://www.facebook.com/dialog/oauth?client_id="
                  . $app_id . "&redirect_uri=" . urlencode($back_url) . "&state="
                  . $_SESSION['state']. "&scope=email";

                echo("<script> top.location.href='" . $dialog_url . "'</script>");

            } else if($_REQUEST['state'] == $_SESSION['state']) {

                $token_url = "https://graph.facebook.com/oauth/access_token?"
                  . "client_id=" . $app_id . "&redirect_uri=" . urlencode($back_url)
                  . "&client_secret=" . $app_secret . "&code=" . $code;

                $response = @file_get_contents($token_url);
                $params = null;
                parse_str($response, $params);

                $graph_url = "https://graph.facebook.com/me?access_token=". $params['access_token'];

                $user = json_decode(file_get_contents($graph_url));

                $user_info = array(
                    'identity' => 'http://www.facebook.com/profile.php?id='.$user->id,
                    'login' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'social' => 'facebook',
                    'social_type' => SOCIAL_TYPE_FACEBOOK
                );

                self::checkSocialUser($user_info);

            } else
                echo "The state does not match. You may be a victim of CSRF.";
        }
 	}


    // авторизация через Твиттер
	private static function authTwitter() {

        if (reg::getKey('/users/twitter_bool')) {
            $app_id = reg::getKey('/users/twitter_id');;
            $app_secret = reg::getKey('/users/twitter_secret');
            $back_url = "http://".$_SERVER['SERVER_NAME']."/users/social-auth/twitter";

            if (!isset($_REQUEST['oauth_verifier'])){
                $connection = new TwitterOAuth($app_id, $app_secret);
                $request_token = $connection->getRequestToken($back_url);

                $_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
                $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

                $url = $connection->getAuthorizeURL($token);

                header('Location: ' . $url);

            } else if (!empty($_SESSION['oauth_token']) && !empty($_SESSION['oauth_token_secret'])){

                $connection = new TwitterOAuth($app_id, $app_secret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
                $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

                unset($_SESSION['oauth_token']);
                unset($_SESSION['oauth_token_secret']);

                $connection = new TwitterOAuth($app_id, $app_secret, $access_token['oauth_token'], $access_token['oauth_token_secret']);
                $content = $connection->get('account/verify_credentials');

                $user = array(
                    'identity' => 'http://twitter.com/'.$content->screen_name,
                    'login' => '@'.$content->screen_name,
                    'first_name' => strtok($content->name,' '),
                    'last_name' => strtok(' '),
                    'social' => 'twitter',
                    'social_type' => SOCIAL_TYPE_TWITTER
                );

                self::checkSocialUser($user);
            }
        }
 	}

    // Проверяем, зарегистрирован ли указанный пользователь на сайте. Если да - авторизуем, если нет - регистрируем.
    private static function checkSocialUser($user_info){

        $sel = new ormSelect('user');
        $sel->where(
            $sel->val('social_identity', '=', $user_info['identity']),
            $sel->val('social_type', '=', $user_info['social_type'])
        );
        $sel->limit(1);

        if ($user = $sel->getObject()) {

            // Пользователь уже зарегистрирован
            $groups = $user->getParents();
            $sel = new ormSelect('user_group');
            $sel->where('id', '=', $groups, 'OR');
            $sel->where('active', '=', 1);

            if (!$user->active || $sel->getCount() < 1) {

                // Ошибка: Пользователь или группа выключены, авторизация не возможна
                echo lang::get('USERS_DISABLE_AUTH');
                die;

            } else if (user::authHim($user)) {

                // Пользователь авторизован, закрываем дочернее окно и возвращаемся на сайт
                self::closeWindowAndOpen('/');
            }

        } else {

            // Пользователь еще не создан, регистрируем
            if (reg::getKey('/users/confirm') || (reg::getKey('/users/ask_email') && empty($user_info['email']))) {

                // Запрашивает согласие с правилами или e-mail
                $_SESSION['SOCIAL_AUTH_USER_INFO'] = $user_info;
                echo page::macros('users')->socialAuthConfirm();

            } else {

                // регистрируем
                $user = self::createUserForSocial($user_info);

                if ($user && !$user->issetErrors()) {
                    
                    user::authHim($user);
                    self::closeWindowAndOpen('/');

                } else {

                    echo $user->getErrorListText();
                }
            }

            system::stop();
        }
    }

    private static function closeWindowAndOpen($url){
        echo "<script type='text/javascript'>window.opener.location.href='$url'; window.close();</script>";
    }

    private static function createUserForSocial($user_info) {

        if (!empty($user_info['login']) && !empty($user_info['first_name'])) {

            $obj = new ormObject();
            $obj->setParent(41);  	// Устанавливаем группу "Пользователи сайта"
            $obj->setClass('user');
            $obj->active = 1;
            $obj->login = $user_info['login'];
            $obj->name = $user_info['first_name'];
            $obj->surname = $user_info['last_name'];
            $obj->social_identity = $user_info['identity'];
            $obj->social_type = $user_info['social_type'];
            $obj->password = rand(100000, 999999);
            
            if (!empty($user_info['email']))
                $obj->email = $user_info['email'];
            else {
                $md5 = substr(md5($user_info['login'].$user_info['social'].rand(10, 99)), 0, 15);
                $obj->email = $md5.'@'.domains::curDomain()->getName();
            }
       
            if ($obj->save())
                unset($_SESSION['SOCIAL_AUTH_USER_INFO']);

            return $obj;
        }
    }

    static function socialAuthConfirm(){

        if (user::isGuest() && isset($_SESSION['SOCIAL_AUTH_USER_INFO'])) {
            
            $confirm = system::POST('confirm', isBool);
            $email = system::POST('email', isEmail);

            $validate = true;

            if (empty($_SESSION['SOCIAL_AUTH_USER_INFO']['email'])) {
                if (reg::getKey('/users/ask_email') && empty($email))
                    $validate = false;
                else if (!empty($email))
                    $_SESSION['SOCIAL_AUTH_USER_INFO']['email'] = $email;
            }

            if (reg::getKey('/users/confirm') && !$confirm)
                $validate = false;

            if ($validate) {

                $user = self::createUserForSocial($_SESSION['SOCIAL_AUTH_USER_INFO']);

                if ($user && !$user->issetErrors()) {

                    user::authHim($user);
                    self::closeWindowAndOpen('/');

                } else {

                    echo $user->getErrorListText();
                }

                system::stop();
            }
        }
    }


    /**
	* @return $ret true если пользователь успешно авторизовался
	* @param string $login - email пользователя
	* $identity - уникальный идентификатор полученный от соц. сети
	* $f_name - имя пользователя
	* $l_name - фамилия пользователя
	* $network - название соц. сети
	* @desc авторизует пользовател через соц. сеть, если такового нету, то создает затем авторизует
	*/

	static function authSoc($login, $identity, $f_name, $l_name, $network) {

    	$ret = false;
		//проверяем наличие соц. сети если нету созлаем ее
		$sel = new ormSelect('soc');
		$sel->where($sel->val('name', '=', $network));
		$sel->limit(1);
		if (!$sel->getObject()){
			$obj = new ormObject();
           	$obj->setClass("soc");
			$obj->active = 1;
           	$obj->name = $network;
			$obj->save();
		}

     	$login = system::checkVar($login, isString);

		//ищем пользователя по email'у
		$sel = new ormSelect('user');
	    $sel->where(
	        $sel->val('active', '=', 1),
			$sel->val('login', '=', trim($login)),
			$sel->containedIn('user_group', $sel->val('active', '=', 1))
		);
    	$sel->limit(1);

		//нашли пользователя с email
  		if(self::$obj = $sel->getObject()) {

			  	/*$user_id = self::$obj->id;

			  	//выбираем id соц. сети
			  	$sel = new ormSelect('soc');
	    		$sel->where( $sel->val('name', '=', trim($network)) );
        		$sel->limit(1);
			  	$obj = $sel->getObject();

			  	$soc_id = $obj->id; */

			  	//смотрим есть ли у пользователя с данный уникальный идентификатор
			  	$t = db::q("SELECT <<__user>>.email,<<identity>>.value
			  	FROM <<__user>>,<<identity>>
			  	WHERE <<__user>>.obj_id = <<identity>>.user_id AND <<identity>>.value ='".$identity."'",records);


			  	if ($t){
				 	$ret = self::authHim(self::$obj);
									echo ("
									<script type='text/javascript'>
				 						window.opener.location.href='/'; window.close();
									</script>
									");
			  	}else{
				  	//если нету перекидываем на регистрацию
					$_SESSION["soc_email"] = $login;
			  		$_SESSION["soc_identity"] = $identity;
			  		$_SESSION["soc_f_name"] = $f_name;
			  		$_SESSION["soc_l_name"] = $l_name;
			  		$_SESSION["soc_network"] = $network;

			  		system::redirect("/users/add_procSoc/");
				 	//db::q("INSERT INTO <<identity>> (value,user_id,soc_id) VALUES ('".$identity."', '".$user_id."', '".$soc_id."');");
				 	//$ret = self::authHim(self::$obj);
			  	}

			//не нашли пользователя
      		} else {

			  $_SESSION["soc_email"] = $login;
			  $_SESSION["soc_identity"] = $identity;
			  $_SESSION["soc_f_name"] = $f_name;
			  $_SESSION["soc_l_name"] = $l_name;
			  $_SESSION["soc_network"] = $network;

			  echo ("
						<script type='text/javascript'>
				 			window.opener.location.href='/users/add_procSoc/'; window.close();
						</script>
					");
			  die;

			  //system::redirect("/users/add_procSoc/");

            }


        return $ret;
    }


    // Отправка сообщения о блокировки пользователя
    private static function sendMailBlock($user) {

        page::assign('domain', 'http://'.domains::curDomain()->getName().languages::pre());
      	page::assign('login', $user->login);
        page::assign('name', $user->name);
       	system::sendMail('/users/mails/block.tpl', $user->email);
  	}

    // Проверяет вхождение пользователя в указанную группу
    static function inGroup($group_id) {

        if (self::$isGuest) {
            return ($group_id == self::$guestGroup) ? true : false;
    	} else if (self::$obj instanceof ormObject) {
    		return (array_key_exists($group_id, self::$obj->getParents())) ? true : false;
    	} else return false;

    }

    // Вернет массив, список групп в которые входит пользователь
    static function getGroups() {
           // print_r(self::$obj);
        if (self::$obj instanceof ormObject)  {
    		return self::$obj->getParents();
    	}else{
    		return array(self::$guestGroup => self::$guestGroup);
    	}
    }

    // Вернет любую информацию о текущем пользователе
    static function get($name) {
        if (self::$obj instanceof ormObject)
    		return self::$obj->__get($name);
    	else
    		return '';
    }

    // Вернет true, если пользователь имеет права доступа в панель администрирования
    static function isAdmin() {
		return self::$isAdmin;
    }

    // Вернет true, если пользователь гость (не авторизован)
    static function isGuest() {
		return self::$isGuest;
    }

    // Вернет экземпляр ORM-объекта для изменение данных пользвателя
    static function getObject() {
		if (self::$obj instanceof ormObject)
			return self::$obj;
    }



    // +++	Работа с правами +++

    /**
	* @return boolean
	* @param string $right - Имя права в панели администрирования
	* @param string $module - Системное имя модуля. Если не указанно, имя определяется исходя из текущего URL`a
	* @desc Проверяет существование указанного права для текущего модуля
	*/
    static function issetRight($right, $module = 0) {

    	if (!self::$isGuest) {

            if (empty($module))
	            $module = system::url(0);

    		self::getRights();

    		$right = str_replace('_proc_', '_', $right);

            if ($module == 'structure' && !strpos($right, ' ')) {
            	$sitever = languages::curId().' '.domains::curId();
            	return (isset(self::$right[$module]['rights'][$sitever][$right])) ? true : false;
            } else
    			return (isset(self::$right[$module]['rights'][$right])) ? true : false;

    	} else
    		return false;

    }

    // Проверяем имеет ли пользователь права на указанный модуль
    static function issetModule($module) {

    	if (self::$isAdmin) {

    		self::getRights();
    		return (isset(self::$right[$module]['rights'])) ? true : false;

    	} else
    		return false;

    }

    // Возвращает право по умолчанию для текущего модуля
    static function getDefaultRight($module) {

    	if (self::$isAdmin && system::$isAdmin) {

    	    self::getRights();

    		if (isset(self::$right[$module]['def_right']))
	    		return self::$right[$module]['def_right'];
	    	else
	    		return false;

		} else return false;
    }

    // Формирует массив с правами для текущего пользователя
 	static function getRights() {

        if (count(self::$right) == 0){

            // Формируем список групп в которые входит пользователь
            $groups = self::$obj->getParents();
            $objs = '';
            while (list($key, $val) = each ($groups))
            	$objs .= ' or rgu_obj_id = "'.$key.'" ';

            // Получаем все права текущего пользователя
            self::$right = self::getRightsFor(self::$obj->id, $objs);
        }

   		return self::$right;
 	}

 	// Формирует массив с правами для указанного объекта: группы или пользователя
 	static function getRightsForObject($obj) {

        if (is_numeric($obj)) {

        	return self::getRightsFor($obj);

        } else if ($obj instanceof ormObject) {

	        if ($obj->isInheritor('user_group')) {

	   			return self::getRightsFor($obj->id);

	   		} else if ($obj->isInheritor('user')) {

		  		// Формируем список групп в которые входит пользователь
		        $groups = $obj->getParents();
		        $groups_ids = '';
		        while (list($key, $val) = each ($groups))
		            $groups_ids .= ' or rgu_obj_id = "'.$key.'" ';

		   		return self::getRightsFor($obj->id, $groups_ids);
	   		}
        }

 	}

    /**
	* @return array
	* @param int $obj - ID объекта или ORM-объект
	* @param boolean $ru_names - Если true, в массиве используются русские имена
	* @desc Формирует список доступных модулей для указанного объекта: группы или пользователя
	*/
 	static function getModulesForObject($obj, $ru_names = true) {

    	$rights = self::getRightsForObject($obj);

		$modules = array();

		if (count($rights)) {
	        while (list($key, $val) = each($rights)) {

	            if ($ru_names){
	            	$name = lang::module($key);
	            	if (empty($name)) $name = $key;
	            } else $name = $key;

	        	$modules[] = array($val['id'], $name);
	        }
        }

        return $modules;
 	}

    /**
	* @return array
	* @param integer $obj_id - ID объекта
	* @param string $groups_ids - Дополнительные уловия в SQL запрос
	* @desc Вспомогательная функция для получения прав доступа объекта
	*/
 	private static function getRightsFor($obj_id, $groups_ids = '') {

		// Получаем список разрещенных прав
        $sql = 'SELECT m_id, m_name, mr_name, mr_is_default, mr_parent_id, mr_lang_id, mr_domain_id
        		FROM <<modules_rights>>, <<modules_rgu>>, <<modules>>
        		WHERE m_id = mr_mod_id and
        			  m_active = 1 and
        			  rgu_right_id = mr_id and
        			  rgu_value = 1 and
        			  (rgu_obj_id = "'.$obj_id.'" '.$groups_ids.')
        		ORDER BY m_sort ASC;';

 		$rights = db::q($sql, records);
        $old_mod = '';
 		$right = array();
   		while (list($key, $val) = each ($rights)) {

   			// В случае, если нет права по умолчанию, ставим первое попавшееся
            if ($old_mod != $val['m_name']) {
            	if (!empty($old_mod) && empty($right[$old_mod]['def_right'])) {
            		$right[$old_mod]['id'] = $tmp_id;
            		$right[$old_mod]['def_right'] = $tmp_def_right;
            	}
            	$old_mod = $val['m_name'];
            	$tmp_id = $tmp_def_right = '';
            }

   			// Добавляем права в список допустимых
   			if ($val['m_name'] == 'structure' && !strpos($val['mr_name'], ' ')) {

				// Добавление прав для модуля Структура (поддержка мультидоменности)
   				$sitever = $val['mr_lang_id'].' '.$val['mr_domain_id'];
   				if (isset($right[$val['m_name']]['rights'][$sitever]) && is_array($right[$val['m_name']]['rights'][$sitever]))
                    $right[$val['m_name']]['rights'][$sitever][$val['mr_name']] = 1;
       			else
          			$right[$val['m_name']]['rights'][$sitever] = array($val['mr_name'] => 1);

   			} else if (!isset($right[$val['m_name']]['rights'][$val['mr_name']]))
      			$right[$val['m_name']]['rights'][$val['mr_name']] = 1;

            //Дополнительна информ. о модуле
            if ($val['mr_is_default']) {
            	$right[$val['m_name']]['id'] = $val['m_id'];
            	$right[$val['m_name']]['def_right'] = $val['mr_name'];
            } else if ($val['mr_parent_id'] == 0) {
                $tmp_id = $val['m_id'];
            	$tmp_def_right = $val['mr_name'];
            }


            // Определяем имя модуля по умолчанию
            $def_mod = (self::$obj->def_modul == 0) ? 1 : self::$obj->def_modul;
            if (self::$obj->id == $obj_id && $val['m_id'] == $def_mod)
            	self::$defModul = $val['m_name'];
   		}

   	    // В случае, если нет права по умолчанию, ставим первое попашееся
     	if (!empty($old_mod) && empty($right[$old_mod]['def_right'])) {
            $right[$old_mod]['id'] = $tmp_id;
            $right[$old_mod]['def_right'] = $tmp_def_right;
      	}

   		// Если это пользователь, проверяем наличие запрещающих прав
   		if (!empty($groups_ids)) {

	   		$sql = 'SELECT mr_name, m_name
	     			FROM <<modules_rights>>, <<modules_rgu>>, <<modules>>
	                WHERE m_id = mr_mod_id and
	        			  m_active = 1 and
	        			  rgu_right_id = mr_id and
	        			  rgu_value = "-1" and
	        			  rgu_obj_id = "'.$obj_id.'"
	                GROUP BY mr_id;';

	        $ban_rights = db::q($sql, records);

	        // Удаляем из основного списка запрещенные права
	        while (list($key, $val) = each ($ban_rights))
	           	if (isset($right[$val['m_name']]['rights'][$val['mr_name']])) {
	           		unset($right[$val['m_name']]['rights'][$val['mr_name']]);
	             	if (count($right[$val['m_name']]['rights']) == 0)
	                   	unset($right[$val['m_name']]);
	            }
        }
           // print_r($right);
        return $right;
    }

    // Вернет имя модуля по умолчанию
    static function getDefModul() {

    	if (self::$isAdmin) {

	    	self::getRights();
	    	return self::$defModul;

    	} else return '';
    }


}

?>