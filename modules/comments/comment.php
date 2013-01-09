<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

    Класс для создания и изменения свойств комментария.
	
*/

class comment extends innerErrorList  {

    private $id;

    private $curprop = array();
    private $newprop = array();

    function __construct($obj_id = '') {

        if (is_array($obj_id) && isset($obj_id['c_id'])) {

            $this->id = $obj_id['c_id'];
            $this->curprop = $obj_id;

        } else {

            $obj_id = system::checkVar($obj_id, isInt);

            if (!empty($obj_id)) {

                // Читаем данные объекта
                $data = db::q('SELECT * FROM <<comments>> WHERE c_id = "'.$obj_id.'";', record);

                if (!empty($data)) {
                    $this->id = $data['c_id'];
                    $this->curprop = $data;
                }
            }
        }
    }

    // Вернет ID комментария
    public function id() {
        return $this->id;
    }

    // Вернет ID комментария, к которому прикреплен комментарий
    public function getParentId() {
        if (isset($this->curprop['c_parent_id']))
            return $this->curprop['c_parent_id'];
        else
            return 0;
    }

    // Устанавливает к какому комментарию необходимо прикрепить текущий.
    public function setParentId($comment_id) {

        $comment_id = system::checkVar($comment_id, isInt);

        if (empty($this->id) && !empty($comment_id)) {

            $obj_id = db::q('SELECT c_obj_id FROM <<comments>> WHERE c_id="'.$comment_id.'";', value);

            if ($obj_id) {
                $this->newprop['c_obj_id'] = $obj_id;
                $this->newprop['c_parent_id'] = $comment_id;
            } else
                $this->newError(100, 'Указанный комментарий не существует. Родитель не выбран.');
        }

    }

    // Вернет ID ORM объекта, к которому прикреплен комментарий
    public function getObjId() {
        if (isset($this->curprop['c_obj_id']))
            return $this->curprop['c_obj_id'];
        else
            return 0;
    }

    // Устанавливает к какому ORM объекту прикреплен комментарий
    public function setObjId($obj_id) {

        if (empty($this->id) && !empty($obj_id))
            if ($obj = ormPages::get($obj_id))
                $this->newprop['c_obj_id'] = $obj->id;
            else
                $this->newError(100, 'Указанного объекта не существует.');
    }


    // Вернет 1 - если комментарий проверен, 0 - если не проверен
    public function isActive() {
        if (isset($this->curprop['c_active']))
            return $this->curprop['c_active'];
        else
            return 0;
    }

    // Изменяет активность комментария
    public function setActive($active) {
        $this->newprop['c_active'] = system::checkVar($active, isBool);
    }

    // Вернет ID пользователя, который оставил данный комментарий
    public function getUserId() {
        if (isset($this->curprop['c_user_id']))
            return $this->curprop['c_user_id'];
        else
            return 0;
    }

    // Вернет имя пользователя
    public function getUserName() {
        if (isset($this->curprop['c_username']))
            return $this->curprop['c_username'];
        else
            return '';
    }

    // Изменяет имя пользователя
    public function setUserName($name) {

        $name = system::checkVar($name, isString);

        if (!empty($name))
            $this->newprop['c_username'] = $name;
    }

    // Вернет e-mail пользователя
    public function getEmail() {
        if (isset($this->curprop['c_email']))
            return $this->curprop['c_email'];
        else
            return '';
    }

    // Изменяет e-mail пользователя
    public function setEmail($email) {

        $email = system::checkVar($email, isEmail);

        if (!empty($email))
            $this->newprop['c_email'] = $email;
        else
            $this->newError(100, 'Е-mail указан в не правильном формате!');
    }

    public function isSendMail() {
        if (isset($this->curprop['c_send_email']))
            return $this->curprop['c_send_email'];
        else
            return 0;
    }

    // Устанавливает e-mail пользователя подписанным на все новые комментарии ветки
    public function setSendEmail($bool) {
        $this->newprop['c_send_email'] = system::checkVar($bool, isBool);
    }

    // Вернет текст комментария
    public function getText() {
        if (isset($this->curprop['c_text']))
            return $this->curprop['c_text'];
        else
            return '';
    }

    // Изменяет текст комментария
    public function setText($text) {
        $tmp = system::checkVar($text, isText, reg::getKey('/comments/text_length'));
        $this->newprop['c_text'] = trim(nl2br(strip_tags($tmp)));
    }

    // Вернет текст комментария
    public function getParram() {
        if (isset($this->curprop['c_parram']))
            return $this->curprop['c_parram'];
        else
            return '';
    }

    // Изменяет текст комментария
    public function setParram($text) {
        $this->newprop['c_parram'] = system::checkVar($text, isString);
    }


    // Вернет рейтинг комментария
    public function getRate() {
        if (isset($this->curprop['c_rate']))
            return $this->curprop['c_rate'];
        else
            return 0;
    }

    // Повышает рейтинг комментария
    public function rateUp() {
        $this->newprop['c_rate'] = $this->getRate() + 1;
    }

    // Понижает рейтинг комментария
    public function rateDown() {
        $this->newprop['c_rate'] = $this->getRate() - 1;
    }

    // Вернет дату создания комментария
    public function getPublDate() {
        if (isset($this->curprop['c_publ_date']))
            return $this->curprop['c_publ_date'];
        else
            return '';
    }


    private function clearCache() {
        cache::delete('comments'.$this->getObjId());
        cache::delete('count_comments'.$this->getObjId());
    }

    // Сохранит измененные данные
    public function save() {

        if (!$this->issetErrors()) {

            if (!empty($this->id)) {

                return $this->changeObject();

            } else {
                $this->setActive(!reg::getKey('/comments/com_moderation'));
                return $this->createObject();    
            }
        }

        return false;
    }

    private function changeObject() {

        $sql = '';
        if (!empty($this->newprop)) {

            reset($this->newprop);
            while(list($fname, $value) = each($this->newprop)) {
                if (!empty($sql)) $sql .= ', ';
                $sql .= $fname.' = "'.$value.'"';
            }

        } else
            return $this->id;



        $id = db::q('UPDATE <<comments>>
                    SET '.$sql.'
                    WHERE c_id = "'.$this->id.'";');

        if ($id !== false) {

            reset($this->newprop);
            while(list($fname, $value) = each($this->newprop))
                $this->curprop[$fname] = $value;
            
            $this->clearCache();

            return $this->id;
        }

        return false;
    }

    private function createObject() {

        if (empty($this->newprop['c_obj_id'])) {
            $this->newError(100, 'Необходимо указать ID объекта к которому будет привязан комментарий!');
            return false;
        }

        if (empty($this->newprop['c_username'])) {
            $this->newError(100, 'Необходимо указать имя пользователя оставившего комментарий!');
            return false;
        }

        if (empty($this->newprop['c_email'])) {
            $this->newError(100, 'Необходимо указать E-mail пользователя оставившего комментарий!');
            return false;
        }

        if (empty($this->newprop['c_text'])) {
            $this->newError(100, 'Необходимо указать текст комментария!');
            return false;
        }

        $sql = '';
        if (!empty($this->newprop)) {

            reset($this->newprop);
            while(list($fname, $value) = each($this->newprop))
                $sql .= ', '.$fname.' = "'.$value.'"';

        } 

        if (!user::isGuest())
            $sql .= ', c_user_id = "'.user::get('id').'"'; 

        $id = db::q('INSERT INTO <<comments>> SET c_publ_date = "'.date('Y-m-d H:i:s').'" '.$sql.';');

        if ($id) {

            $this->id = $id;
            $this->curprop = $this->newprop;

            // Отправка уведомлений о новом комментарии
            $this->sendEmails();

            $this->clearCache();

            return $this->id;
        }


        return false;
    }


    // Отправка уведомлений о новом комментарии
    private function sendEmails() {

        $last_email = '';
        
        // Информация о новом комментарии
        page::assign('username', $this->getUserName());
        page::assign('comment', $this->getText());
        page::assign('comment_id', $this->id());

        // Информация о странице
        if ($page = ormPages::get($this->getObjId())) {
            page::assign('page.id', $page->id);
            page::assign('page.url', $page->url);
            page::assign('page.name', $page->name);
        }

        // Отправляем письмо автору предыдущего коммента
        if ($this->getParentId() != 0) {
            $old_comment = new comment($this->getParentId());
            if ($old_comment->getEmail() != $this->getEmail()) {
                $last_email = $old_comment->getEmail();
                page::assign('old_comment', $old_comment->getText());
                page::assign('name', $old_comment->getUserName());
                system::sendMail('/comments/mails/new_answer.tpl', $last_email);
            }
        }

        // Отправляем письма всем подписавщимся на уведомления
        $sql = 'SELECT c_username, c_email FROM <<comments>>
                WHERE c_obj_id = "'.$this->getObjId().'" and
                      c_send_email = 1 and
                      c_id <> "'.$this->id().'"
                GROUP BY c_email;';

        $list = db::q($sql, records);

        if (!empty($list)) {
            while(list($key, $val) = each($list))
                if ($val['c_email'] != $last_email && $val['c_email'] != $this->getEmail()) {
                    page::assign('name', $val['c_username']);
                    page::assign('index', str_replace(array('.', '@'), array('_', '__'), $val['c_email']));
                    system::sendMail('/comments/mails/new_comment.tpl', $val['c_email']);
                }
        }

    }

    // Удаляем комментарий
    function delete() {

        if (!empty($this->id)) {

            $ret = db::q('DELETE FROM <<comments>> WHERE c_id = "'.$this->id.'";');

            if ($ret) {
               $this->clearCache();
               return true;
            } else
                $this->newError(100, 'Произошла ошибка при удалении комментария!');

        }

        return false;
    }



}

?>