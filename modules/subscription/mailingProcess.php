<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

    Класс выполняющий рассылку писем частями.


*/

class mailingProcess {

    // Формируем текст письма
    static function getMailHTML($release_id) {

    	if ($release = ormObjects::get($release_id)) {

			if ($release->isInheritor('subscribe_msg')) {

	            $subscribe = $release->getParent();
	            page::$macros = 1;

	        	$mail = '';
	            if (file_exists(TEMPL_DIR.'/subscription/mails/'.$subscribe->template.'.tpl')) {

	        		include(TEMPL_DIR.'/subscription/mails/'.$subscribe->template.'.tpl');

                    page::assign('domain_name', domains::curDomain()->getName());
	        		page::assign('site_name', domains::curDomain()->getSiteName());
		       		page::assign('base_email', domains::curDomain()->getEmail());

                    page::assign('subscribe.id', $subscribe->id);
	          		page::assign('release.id', $release->id);
		            page::assign('release.name', $release->name);
		            page::assign('release.message', $release->message);

	                $sel = new ormSelect();
			        $sel->depends($release->id, 1462);

	          		$num = 0;
		            $list = '';
		            while($obj = $sel->getObject()) {

	             		$num ++;

	             		page::assign('obj.num', $num);
			            page::assign('class-first', ($num == 1) ? 'first' : '');
			            page::assign('class-last', ($num == $sel->getObjectCount()) ? 'last' : '');
			            page::assign('class-odd', ($num % 2 == 0) ? 'odd' : '');
			            page::assign('class-even', ($num % 2 != 0) ? 'even' : '');
		                page::assign('class-third', ($num % 3 == 0) ? 'third' : '');

	                 	page::assign('obj.id', $obj->id);
	                 	page::assign('obj.url', $obj->url);
	                  	$obj->parseAllFields();

		                $list .= page::parse($TEMPLATE['list']);
		            }
	             	page::assign('list', $list);

	              	if (!empty($list))
		                page::fParse('list', $TEMPLATE['frame_list']);

		            $mail = page::parse($TEMPLATE['frame']);
	            }

	            return $mail;
            }
    	}
    }

    // Вернет количество чатей на которые нужно разбить список пользователей, чтобы "правильно" выполнить рассылку.
    static function getPartCount($subscribe_id) {
        if (is_numeric($subscribe_id)) {
    		$sel = new ormSelect('subscribe_user');
		    $sel->where('parents', '=', $subscribe_id);
		    return ceil($sel->getCount() / reg::getKey('/subscription/count_mails_day'));
		} else return 0;
    }


	static function start($release_id, $subject, $part = 1) {

        if ($release = ormObjects::get($release_id)) {

	    	$_SESSION['SUBSCR_PART'] = system::checkVar($part, isInt);
	     	$_SESSION['SUBSCR_SUBJECT'] = str_replace(array('{', '}'), '%', system::checkVar($subject, isString));
	     	$_SESSION['SUBSCR_MAILHTML'] = self::getMailHTML($release_id);

            if ($release->error_part_num == 0) {

                // Рассылка части с начала
		     	$release->last_subscribe = date('Y-m-d H:i:s');
		     	$release->error_part_num = $_SESSION['SUBSCR_PART'];
		     	if ($release->part_count_awaiting <= 0) {
		     		$count = self::getPartCount($release->parent_id);
		     		$release->part_count_awaiting = $count;
		     		$release->part_count = $count;
		    	}
	            $release->save();
	            $start = 0;

	            page::assign('part', $_SESSION['SUBSCR_PART']);
                page::assign('name', $release->name);
	            system::revue($release->getParent(), page::parse(lang::get('SUBSCRIBE_HIST_START')), info);

            } else {

                // Продолжение незавершенной рассылки
                $_SESSION['SUBSCR_PART'] = $release->error_part_num;
            	$start = $release->error_iteration_num;

                page::assign('part', $_SESSION['SUBSCR_PART']);
                page::assign('name', $release->name);
            	system::revue($release->getParent(), page::parse(lang::get('SUBSCRIBE_HIST_CONTINUE')), info);
            }

            // Вычисляем кол-во итераций для рассылки всех писем в указанной части
	      	$sel = new ormSelect('subscribe_user');
			$sel->where('parents', '=', $release->parent_id);

	        $count_in_part = reg::getKey('/subscription/count_mails_day');
	        if ($_SESSION['SUBSCR_PART'] * $count_in_part > $sel->getCount())
	        	$count_in_part = $sel->getCount() - ($_SESSION['SUBSCR_PART'] - 1) * $count_in_part;

	      	$count = ceil($count_in_part / reg::getKey('/subscription/count_mails'));


            return json_encode(array('error' => 0, 'start' => $start, 'count' => $count));
    	}
	}

    // Отправляем указанный блок писем
    static function sendNextBlock($release_id, $num_block) {

    	if ($release = ormObjects::get($release_id)) {

            $subscribe = $release->getParent();

            $release->last_subscribe = date('Y-m-d H:i:s');
            $release->error_iteration_num = $num_block + 1;
            $release->save();

        	$start = ($_SESSION['SUBSCR_PART'] - 1) * reg::getKey('/subscription/count_mails_day') + ($num_block - 1) * reg::getKey('/subscription/count_mails');

        	$sel = new ormSelect('subscribe_user');
		    $sel->where('parents', '=', $release->parent_id);
            $sel->limit($start, reg::getKey('/subscription/count_mails'));

            while($obj = $sel->getObject()) {

                // Подставляем ФИО пользователя
                page::assign('user.id', $obj->id);
                page::assign('user_name', '');
                if (file_exists(TEMPL_DIR.'/subscription/mails/'.$subscribe->template.'.tpl')) {

        			include(TEMPL_DIR.'/subscription/mails/'.$subscribe->template.'.tpl');

	                if ($obj->first_name != '') {

	                	if ($subscribe->name_format == 2)
	                		$name = $obj->second_name.' '.$obj->first_name;
	                	else
	                		$name = $obj->first_name;

	                	page::assign('user_name', $name);
	                	$hello = page::parse($TEMPLATE['hello_username']);

	                } else $hello = page::parse($TEMPLATE['hello']);

                } else $hello = '';
                page::assign('hello', $hello);

	            // Отправляет письмо
		        $mail = new phpmailer();
		        $mail->From = $subscribe->back_email;
		        $mail->FromName = $subscribe->back_name;
		        $mail->AddAddress($obj->name);
		        $mail->WordWrap = 50;
		        $mail->IsHTML(true);
		        $mail->AddAttachment(ROOT_DIR.'/'.$release->file, system::fileName($release->file));
		        $mail->Subject = page::parse($_SESSION['SUBSCR_SUBJECT']);
		        $mail->Body = page::parse($_SESSION['SUBSCR_MAILHTML']);
		        $mail->Send();
            }

		}
    }

    static function stop($release_id) {

        if ($release = ormObjects::get($release_id)) {

	     	$release->last_subscribe = date('Y-m-d H:i:s');
	     	$release->error_part_num = 0;
	     	$release->error_iteration_num = 0;
	     	$release->part_count_awaiting = $release->part_count_awaiting - 1;
            $release->save();

            $subscribe = $release->getParent();
            $subscribe->last_subscribe = date('Y-m-d H:i:s');
            $subscribe->save();

            page::assign('part', $_SESSION['SUBSCR_PART']);
            page::assign('name', $release->name);
            system::revue($subscribe, page::parse(lang::get('SUBSCRIBE_HIST_STOP')), info);

    	}
	}

    static function delEmail($user_email, $subscription_id) {



  		if (!empty($subscription_id)) {

            $sel = new ormSelect('subscribe_user');
            $sel->where('name', '=', $user_email);

			if ($obj = $sel->getObject()) {

				$parents = $obj->getParents();

			    if (count($parents) > 1)
			    	$obj->delParent($subscription_id);
				else
					$obj->delete();

				return true;
            }
        }

    	return false;
  	}


	static function delEmailById($user_id, $subscription_id) {

  		if (($obj = ormObjects::get($user_id)) && !empty($subscription_id))

			if ($obj->isInheritor('subscribe_user')) {

				$parents = $obj->getParents();

			    if (count($parents) > 1)
			    	$obj->delParent($subscription_id);
				else
					$obj->delete();

				return true;
            }

    	return false;
  	}

  	static function addEmail($email, $subscriptions, $copyUserData = false) {

    	$email = system::checkVar($email, isEmail);

		if (!empty($email) && !empty($subscriptions)) {

    		$obj = new ormObject();
		    $obj->setClass('subscribe_user');
      		$obj->name = $email;

      		if (!user::isGuest() && $copyUserData) {
            	$obj->second_name = user::get('surname');
            	$obj->first_name = user::get('name');
            	$obj->user_id = user::get('id');
        	}

			// Указываем на какие подписки подписать
			while(list($key, $val) = each($subscriptions))
				$obj->setNewParent($val);

			// Сохраняем изменения
			$sid = $obj->save();

            if ($obj->issetErrors(29)) {

            	// Если указанный e-mail уже существует, пытаемся найти его и подписать на рассылки.
                $sel = new ormSelect('subscribe_user');
                $sel->where('name', '=', $email);
                $sel->limit(1);

                if ($obj = $sel->getObject()) {

                    if (!user::isGuest() && $copyUserData) {
		            	$obj->second_name = user::get('surname');
		            	$obj->first_name = user::get('name');
		            	$obj->user_id = user::get('id');
		        	}

	        		reset($subscriptions);
	        		while(list($key, $val) = each($subscriptions))
	        			$obj->setNewParent($val);

					$sid = $obj->save();
                }
            }

            return $sid;
     	}
  	}
}

?>