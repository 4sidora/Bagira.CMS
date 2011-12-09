<?php

class controller {

    public function subscribeAction() {

        $email = system::POST('email', isEmail);

        if (!empty($email) && !empty($_POST['subscribes'])) {

            $sid = mailingProcess::addEmail($email, $_POST['subscribes'], true);

            if ($sid) {

            	page::globalVar('h1', lang::get('SUBSCRIPTION_TITLE'));
            	page::globalVar('title', lang::get('SUBSCRIPTION_TITLE'));
            	return lang::get('SUBSCRIPTION_MSG');

            } else if (!empty($_POST['back_url']))
            	system::redirect($_POST['back_url']);
		}

		system::redirect('/');
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