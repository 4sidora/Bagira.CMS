<?php

class controller {

    public function defAction() {
        return ormPages::get404();
    }

    // Обработчик отправки сообщения (отзыва, вопрос-ответа и пр.)
    public function send_messageAction() {

        if ($form_obj = ormObjects::get(system::POST('form_id'))) {

            $issetErrors = false;
            $answer = array();

            // Проверка капчи
            if ($form_obj->captcha && (system::POST('random_image') != $_SESSION['core_secret_number'])) {

                $issetErrors = true;
                
                $answer = array(
                    'field' => 'random_image',
                    'msg' => lang::get('FEEDBACK_ERROR1')
                );
                
            } else
                $_SESSION['core_secret_number'] = '';


            // Если указанно куда, добавляем объект в БД
            if (($form_obj->any_sections || $form_obj->section) && !$issetErrors) {

                // Определяем раздел, в который будем добавлять отзыв
                if ($form_obj->any_sections)
                    $section = system::POST('section_id', isInt);
                else {
                    $section = $form_obj->section;
                    $section = (!empty($section)) ? $section[0] : 0;
                }


                // Создаем объект обратной связи
                $obj = new ormPage();
                $obj->setParent($section);
                $obj->setClass($form_obj->form_class);

                $obj->tabuList('pseudo_url', 'h1', 'keywords', 'title', 'description', 'answer',
                               'active', 'is_home_page', 'view_in_menu', 'view_submenu', 'in_search',
                               'in_index', 'in_new_window', 'other_link', 'img_act', 'img_no_act', 'img_h1');

                $obj->loadFromPost();

                $obj->active = 0;
                $obj->view_in_menu = 1;
                $obj->view_submenu = 1;
                $obj->in_search = 1;
                $obj->in_index = 1;
                $obj->is_home_page = 0;

                if ($obj->name != '') {
                    $obj->h1 = $obj->name;
                    $obj->title = $obj->name;
                }

                $obj->pseudo_url = rand(1000, 9999);
                $obj->template_id = ($sect = ormPages::get($form_obj->section)) ? $sect->template_id : 1;
                $obj->form_id = $form_obj->id;
                $obj->setRightForAll(2);

                $obj_id = $obj->save();

                if ($obj_id) {

                    $obj->pseudo_url = $obj_id;
                    $obj->save();

                } else {

                    $issetErrors = true;
                    
                    $f = $obj->getErrorFields();

                    $answer = array(
                        'field' => $f['focus'],
                        'msg' => $obj->getErrorListText(' ')
                    );
                }
            }

            if (!$issetErrors) {

                // Отправка нужных писем

                page::assign('site_name', domains::curDomain()->getSiteName());
                page::assign('base_email', domains::curDomain()->getEmail());

                while(list($key, $val) = each($_POST))
                    page::assign($key, system::checkVar($val, isText));

                // Если указан список адресатов, отправляем письма
                if ($form_obj->mailing_list != '') {

                    $mail = new phpmailer();
                    $mail->From = $this->parse($form_obj->admin_sender_address);
                    $mail->FromName = $this->parse($form_obj->admin_sender_name);
                    /*
                  if (!empty($this->files))
                    for($i = 0; $i < count($this->files); $i++)
                       $mail->AddAttachment($this->files[$i][0], $this->files[$i][1]);
                    */
                    $mail->AddAddress($form_obj->mailing_list);
                    $mail->WordWrap = 50;
                    $mail->IsHTML(true);

                    $mail->Subject = $this->parse($form_obj->admin_subject);
                    $mail->Body = $this->parse($form_obj->admin_template);

                    $mail->Send();
                }

                // Если нужно, отправляем уведомление пользователю
                if ($form_obj->send_notice && !$issetErrors) {

                    $mail = new phpmailer();
                    $mail->From = $this->parse($form_obj->notice_sender_address);
                    $mail->FromName = $this->parse($form_obj->notice_sender_name);

                    $mail->AddAddress(system::POST('email'));
                    $mail->WordWrap = 50;
                    $mail->IsHTML(true);

                    $mail->Subject = $this->parse($form_obj->notice_subject);
                    $mail->Body = $this->parse($form_obj->notice_template);

                    $mail->Send();
                }


                // Показываем результат
                if (system::isAjax()) {

                    if ($form_obj->msg != '')
                        $text = $form_obj->msg;
                    else
                        $text = lang::get('FEEDBACK_MSG_1');

                    echo json_encode(array('field' => 0, 'msg' => strip_tags($text)));

                } else
    		        system::redirect('/feedback/ok/'.$form_obj->id);


            } else {

                // Произошли ошибки

                if (system::isAjax()) {

                    echo json_encode($answer);

                } else {

                    system::savePostToSession();

                    system::saveError('feedback', $answer);

                    if (empty($_POST['back_url']))
		        	    $_POST['back_url'] = '/structure/map';

    		        system::redirect($_POST['back_url'], true);
                }
            }

            system::stop();

        } else system::stop();
    }

    private function parse($val) {
        return page::parse(str_replace(array('{', '}'), '%', $val));
    }

    function okAction(){

        if ($form_obj = ormObjects::get(system::url(2))) {

            page::globalVar('h1', lang::get('FEEDBACK_TITLE'));
            page::globalVar('title', lang::get('FEEDBACK_TITLE'));

            if ($form_obj->msg != '')
                return $form_obj->msg;
            else
                return lang::get('FEEDBACK_MSG_1');
        }

        system::redirect('/');
    }

}

?>