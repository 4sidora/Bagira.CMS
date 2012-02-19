<?php

class controller {

    public function defAction() {

		return ormPages::get404();

 	}

    // Добавление комментария
    public function addAction() {

        if (user::isGuest() && reg::getKey('/comments/only_reg'))
            system::stop();

        if (user::isGuest() && system::POST('random_image') != $_SESSION['core_secret_number']) {
            echo json_encode(array('error' => 1, 'data' => lang::get('FEEDBACK_ERROR1')));
            system::stop();
	    } else
	    	$_SESSION['core_secret_number'] = '';

        // Добавляем новый комментарий
        $comment = new comment();
        $comment->setParentId(system::POST('parent_id'));
        $comment->setObjId(system::POST('obj_id'));

        $comment->setUserName(system::POST('username'));
        $comment->setEmail(system::POST('email'));
        $comment->setText(system::POST('text'));
        $comment->setSendEmail(system::POST('send_email'));

        $obj_id = $comment->save();

        if (!$obj_id) {
            echo json_encode(array('error' => 2, 'data' => $comment->getErrorListText(' ')));
            system::stop();
        } else {
            page::assign('current_url', system::POST('back_url'));
            $html = page::macros('comments')->view($comment->id());            
            echo json_encode(array('error' => 0, 'data' => $html));
            system::stop();
        }

        if (!empty($_POST['back_url']) && !system::isAjax())
			system::redirect($_POST['back_url'].'#comment'.$obj_id, true);
		else
		 	system::stop();
    }

    // Отписка от получения уведомлений о новых комментариях
    public function unsubscribeAction(){

        if ($page = ormPages::get(system::url(3))) {
            comments::unsubscribe(system::url(2), system::url(3));

            page::globalVar('title', lang::get('COM_TITLE'));
            page::globalVar('h1', lang::get('COM_TITLE'));

            return lang::get('COM_UNSUBSCRIBE').'"'.$page->name.'".';
        }
    }

    // Изменение рейтинга комментария
    public function change_rateAction(){

        if (!isset($_SESSION['comments_rate'][system::url(2)]))

            if ($comment = comments::get(system::url(2))) {

                if (system::url(3) == 'up')
                    $comment->rateUp();
                else
                    $comment->rateDown();

                $_SESSION['comments_rate'][$comment->id()] = 1;
                $comment->save();
            }
        
        system::stop();
    }


}

?>