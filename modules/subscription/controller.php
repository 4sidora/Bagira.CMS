<?php

class controller {

    public function subscribeAction() {

        $email = system::POST('email', isEmail);

        if (empty($email)) {

            $answer = array(
                'error' => 1,
                'msg' => lang::get('SUBSCRIPTION_EMPTY_EMAIL')
            );

        } else if (empty($_POST['subscribes'])) {

            $answer = array(
                'error' => 2,
                'msg' => lang::get('SUBSCRIPTION_EMPTY_LIST')
            );

        } else {

            $sid = mailingProcess::addEmail($email, $_POST['subscribes'], true);

            if ($sid) {

                $answer = array(
                    'error' => 0,
                    'msg' => lang::get('SUBSCRIPTION_MSG')
                );

            } else {

                $answer = array(
                    'error' => 3,
                    'msg' => lang::get('SUBSCRIPTION_ERROR')
                );
            }
		}

        if (!system::isAjax()) {

            if (!empty($answer['error'])) {

                system::saveErrorToSession('subscription', $answer);
                
                if (!empty($_POST['back_url']))
                    system::redirect($_POST['back_url'], true);

            } else
                system::redirect('/subscription/ok');

        } else
            system::json($answer);
 	}

    // Подтверждение подписки
    public function okAction() {
        page::globalVar('h1', lang::get('SUBSCRIPTION_TITLE'));
        page::globalVar('title', lang::get('SUBSCRIPTION_TITLE'));
        return lang::get('SUBSCRIPTION_MSG');
    }

    // Просмотр письма
 	public function viewAction() {

 		if (system::issetUrl(2)) {
 			page::assign('hello', '');
       		echo page::parse(mailingProcess::getMailHTML(system::url(2)));
        }

 		system::stop();
 	}

 	// Отписаться от рассылки
 	public function unsubscribeAction() {

 		if (system::issetUrl(2) && system::issetUrl(3)) {

 		    if (($obj = ormObjects::get(system::url(2))) && $obj->isInheritor('subscription')) {

                mailingProcess::delEmailById(system::url(3), $obj->id);

	            page::assign('name', $obj->name);
	       		return page::parse(lang::get('SUBSCRIPTION_MSG2'));
       		}
        }

 		return ormPages::get404();
 	}


}

?>