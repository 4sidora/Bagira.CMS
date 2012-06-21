<?php

class controller {


	public function defAction() {

        // Устанавливаем статус системы "в режиме администрирования".
        system::$isAdmin = true;
        page::$macros = 0;


        // Попытка авторизации
        if(!empty($_POST['enter']))
            if (!user::auth($_POST['login'], $_POST['passw']))
               $this->showAuthForm();
            else
                header("Location: ".$_SERVER["HTTP_REFERER"]);

		//проверяем наличие кукисов если есть авторизуем и делаем редирект
		if (!empty($_COOKIE["remember-me"]) && user::isGuest()){

			$params = explode('-',$_COOKIE["remember-me"]); //разбиваем строку на 2 параметра
			if ($params[1] == system::user_ip()) {
				$user = new ormSelect("user");
				$user->where(
					$user->val('active', '=', 1),
					$user->val('id', '=', $params[0])
				);
				$user->limit(1);

				$user = $user->getObject();

				if (user::authHim($user))
					system::redirect('/');
			}
		}

        // Если пользователь не админ, показываем форму авторизации
        if (!user::isAdmin())
            $this->showAuthForm();

        // Определяем текущий домен
        domains::curDomain();

        // Выход из системы
        if (system::issetUrl(0) && system::url(0) == 'logout')
        	user::logout();


        if (system::url(0) == 'showhide') {
    		$_SESSION['SH_FIELDS'] = (system::url(1) == 0) ? 'hide' : 'show';
    		system::stop();
		}

		// Обработка запросов от поля ObjectLinks
		ui::checkObjectLinks();

        system::$defTemplate = MODUL_DIR.'/mpanel/template/default.tpl';

        // Определяем модуль
        if (!system::issetUrl(0))
          	system::setUrl(0, user::getDefModul());

        // Если есть ссылка на обработчик формы
        if (!empty($_POST['right']))
        	system::setUrl(1, system::POST('right', isVarName));

        // Определяем право
        if (system::issetUrl(1)) {

        	// Проверяем существует ли указанное право
            if (user::issetRight(system::url(1)))
            	$currRight = system::url(1);
            else if (user::issetRight(str_replace('_proc', '', system::url(1))))
            	$currRight = system::url(1);


        } else {

        	// Пытаемся найти право по умолчанию
         	$def_right = user::getDefaultRight(system::url(0));

          	if ($def_right) {
           		$currRight = $def_right;
                system::setUrl(1, $def_right);
            }
        }

        $this->getMenu();
        page::assign('current_url', system::getCurrentUrl());
        page::assign('admin_url', system::au());

        if (!empty($currRight)) {

            // Определяем имя и метод контролера
            $pos = strpos($currRight, '_');
            if ($pos) {
            	$class_name = '__'.substr($currRight, 0, $pos);
            	$action_name = substr($currRight, $pos + 1, strlen($currRight) - $pos);
            } else {
            	$class_name = '__'.$currRight;
            	$action_name = 'defAction';
            }

            $mod_name = MODUL_DIR.'/'.system::url(0).'/'.$class_name.'.php';

     		// Пытаемся подгрузить модуль
    		if (file_exists($mod_name)) {

    	    	include($mod_name);

				if (file_exists(MODUL_DIR.'/'.system::url(0).'/lang-ru.php'))
        			include(MODUL_DIR.'/'.system::url(0).'/lang-ru.php');

                ui::setHeader(lang::right($currRight));

    	    	if (class_exists($class_name)) {

               		eval('$c = new '.$class_name.'();');

               		if (ui::$stop)

                        $content = '.';

               		else if (method_exists($c, $action_name))

                    	$content = call_user_func(array($c, $action_name));
               	}
            }

            if (empty($content)) {

            	$msg = lang::get('TEXT_PROC_NOTFOUND2').'<br />'.system::getCurrentUrl().'<br /><br />
	                        '.lang::get('TEXT_PROC_NOTFOUND3').'<br />'.$mod_name.'<br /><br />
	                        '.lang::get('TEXT_PROC_NOTFOUND4');

                system::log(lang::get('TEXT_PROC_NOTFOUND').' '.system::getCurrentUrl());
            	ui::MessageBox(lang::get('TEXT_PROC_NOTFOUND'), $msg);
             	system::redirect('/');
            }

        } else {

            system::log(lang::get('TEXT_ERROR_RIGHT_LOG').system::getCurrentUrl());
        	ui::MessageBox(lang::get('TEXT_ERROR_RIGHT'), lang::get('TEXT_ERROR_RIGHT2'));
        	system::redirect('/');

        }


        //Производим сжатие страницы
        if (reg::getKey('/config/gzip')) {

        	$PREFER_DEFLATE = false;
        	$FORCE_COMPRESSION = false;
			$AE = (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : $_SERVER['HTTP_TE'];
   			$support_gzip = (strpos($AE, 'gzip') !== FALSE) || $FORCE_COMPRESSION;
            $support_deflate = (strpos($AE, 'deflate') !== FALSE) || $FORCE_COMPRESSION;

            if ($support_gzip && $support_deflate)
            	$support_deflate = $PREFER_DEFLATE;

             if ($support_deflate) {
             	header("Content-Encoding: deflate");
              	ob_start("compress_output_deflate");
             } else {
             	if ($support_gzip) {
              		header("Content-Encoding: gzip");
                	ob_start("compress_output_gzip");
              	} else ob_start();
             }
        }

		return ui::getMainHTML($content);
 	}

    // Формируем меню с модулями
 	private function getMenu() {

  		if (file_exists(MODUL_DIR.'/mpanel/template/menu.tpl')) {
			include(MODUL_DIR.'/mpanel/template/menu.tpl');

            if (file_exists(MODUL_DIR.'/mpanel/lang-ru.php'))
        		include(MODUL_DIR.'/mpanel/lang-ru.php');

			$mas = user::getRights();
			///print_r($mas);

            $items = $sub_items = '';
            $num = 0;
            while (list($name, $modul) = each ($mas)) {

                if (!empty($modul['rights'])){
	                $num ++;

	            	if (file_exists(MODUL_DIR.'/'.$name.'/lang-ru.php'))
	             		include(MODUL_DIR.'/'.$name.'/lang-ru.php');

	                $mod_name = (isset($MODNAME[$name])) ? $MODNAME[$name] : $name;
	                page::assign('name', $mod_name);
	                page::assign('url', system::au().'/'.$name);

	                if ($num < 7) {
		                $act = (system::url(0) == $name) ? 'act' : 'no_act';
						$items .= page::parse($TEMPLATE['item_'.$act]);
					} else {

						$sub_items .= page::parse($TEMPLATE['sub_item']);
					}
				}
            }

            // Список редко используемых модулей
            if (!empty($sub_items)) {

            	page::assign('eshe', $LANG['eshe']);
                page::assign('sub_items', $sub_items);
            	$sub_menu = page::parse($TEMPLATE['sub_menu']);

            } else $sub_menu = '';
                         // echo $sub_menu;

            // Вывод списка языковых версий и доменов
            $sub_menu .= $this->getLangsAndDomains($TEMPLATE);


            page::assign('items', $items);
            page::assign('sub_menu', $sub_menu);
            $menu = page::parse($TEMPLATE['basic_menu']);

            page::assign('menu', $menu);

            // Устанавливаем доступ к языковым переменным
            if (isset($RIGHT) && isset($MODNAME)) {
		        lang::setLang($LANG);
		        lang::setRight($RIGHT);
		        lang::setModule($MODNAME);
	        }


		}
    }

    private function getLangsAndDomains($TEMPLATE){

    		$sub_menu = '';
            $langs = languages::getAll();
            $domains = domains::getAll();
            page::assign('ldObjectLinks', '');

            if (count($langs) > 1 || count($domains) > 1) {

	            $isMultiDom = (count($domains) > 1) ? true : false;
                $sub_items = $curLD = '';
                $mas = array();
                $num = 0;

	            while (list($num2, $domain) = each ($domains)) {

	                reset($langs);
	            	while (list($num1, $lang) = each ($langs)) {

	            	    if (user::issetRight($lang['l_id'].' '.$domain['d_id'], 'structure')) {

	                        if ($isMultiDom) {
		            	        $lanver = $domain['d_name'];
				                if ($lang['l_id'] != $domain['d_def_lang'])
				                	$lanver .= '/'.$lang['l_prefix'];
	                        } else $lanver = $lang['l_name'];

                            $link = ADMIN_URL;
			                if ($domain['d_id'] != domains::curSiteDomain()->id())
			                	$link .= '/'.str_replace('.', '_', $domain['d_name']);
			                if ($lang['l_id'] != $domain['d_def_lang'])
			                	$link = '/'.$lang['l_prefix'].$link;

			        		if (domains::curId() == $domain['d_id'] && languages::curId() == $lang['l_id'])
			        			$curLD = $link;

			                page::assign('name', $lanver);
			                page::assign('url', $link);
							$sub_items .= page::parse($TEMPLATE['langver']);

							$mas[] = array(
                            	'id' => $link,
                            	'name' => $lanver
                            );

							$num ++;
						}
					}
	            }

	            if (!empty($sub_items) && $num > 1) {

	                if ($isMultiDom)
		                $lanver = domains::curDomain()->getName().languages::pre();
	                else
	                	$lanver = languages::curLang()->getName();

	            	page::assign('eshe', $lanver);
	                page::assign('sub_items', $sub_items);
	            	$sub_menu = page::parse($TEMPLATE['sub_menu']);

	            	ui::SelectBox('ldObjectLinks', $mas, $curLD, 474, '', 'onSelectldObjectLinks()');
	            }

            }

            return $sub_menu;
    }

    private function showAuthForm(){
    	if (file_exists(MODUL_DIR.'/mpanel/template/auth.tpl')) {
			include(MODUL_DIR.'/mpanel/template/auth.tpl');

			page::assign('title', lang::get('CMF'));

			if (user::isGuest()) {

                // Пользователь не авторизован
            	page::assign('url', system::getCurrentUrl());
            	page::assign('login', lang::get('AUTHFORM_LOGIN'));
            	page::assign('passw', lang::get('AUTHFORM_PASSW'));
            	page::assign('enter', lang::get('AUTHFORM_ENTER'));
            	echo page::parse($TEMPLATE['frame']);

            } else {

                // Пользователь авторизован, но не является админом
            	page::assign('exit_url', '/users/logout');
            	page::assign('user', user::get('name'));
            	page::assign('hello', lang::get('AUTHFORM_HELLO'));
            	page::assign('big_text', lang::get('AUTHFORM_BIG_TEXT'));
            	page::assign('exit_text', lang::get('AUTHFORM_EXIT_TEXT'));
            	echo page::parse($TEMPLATE['frame_no_admin']);

            }
            system::stop();
		}
    }

}

?>