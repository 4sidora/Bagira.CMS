<?php

class controller {

    // Засчитываем голос
 	public function doAction() {

        $answers = system::POST('answers');
        $error = 2;

    	if (count($answers) > 0) {

    		if ($vote = ormPages::get(system::POST('vote_id'), 'vote')) {

                if (!$vote->close) {

	                $sel = new ormSelect('answer');
	                $sel->fields('name, count');
	            	$sel->where('parents', '=', $vote->id);

	            	while($answ = $sel->getObject())
	                	if (in_array($answ->id, $answers)) {

	                    	$answ->count = $answ->count	+ 1;
	                		$answ->save();

	                    	if (!$vote->multiselect)
	                    		break;
	                	}

                    cache::delete('vote'.$vote->id.'1');
                    cache::delete('vote'.$vote->id.'0');

	            	$_SESSION['voting_'.$vote->id] = 1;
	            	$error = 0;

            	} else {

            	    // Голосование закрыто
            		$msg = lang::get('VOTING_MSG_CLOSE');
            		$error = 1;
            	}

    		} else $msg = lang::get('VOTING_MSG_NOT_FOUND');

    	} else $msg = lang::get('VOTING_MSG_CHOSE_VAR');


    	if (system::isAjax()) {

            if ($error < 2 && is_a($vote, 'ormPage'))
            	$data = page::macros('voting')->objView($vote->id);

            echo json_encode(array('error' => 0, 'html' => $data, 'msg' => $msg));
            system::stop();

    	} else {

        	if (!empty($_POST['back_url']))
				system::redirect($_POST['back_url']);
			else
			 	system::redirect('/');
    	}
  	}

    // Переводит голосование в режим просмотра, выводит его содержимое
  	public function viewAction(){

        if (system::issetUrl(2)) {
	  		$_SESSION['voting_'.system::url(2)] = 1;
	  	    echo page::macros('voting')->objView(system::url(2));
  	    }

  	    system::stop();
  	}

}

?>