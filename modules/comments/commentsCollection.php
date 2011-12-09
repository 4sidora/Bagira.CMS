<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

    Класс коллекция для управления комментариями
	
*/

class comments {

    private $obj_id;
    private $only_active = false;
    private $count = 0;

    private $get_data = false;

    private $data = array();
    private $cycle = array();
    private $comments = array();


    function __construct($obj_id) {
        $this->obj_id = system::checkVar($obj_id, isInt);
    }

    function onlyActive($bool = true) {
        $this->only_active = system::checkVar($bool, isBool);
    }

    //
    private function getData() {

        if (!empty($this->obj_id) && empty($this->data) && !$this->get_data) {

            $where = '';

            if ($this->only_active)
                $where .= ' and c_active = 1';

            $sql = 'SELECT * FROM <<comments>>
                    WHERE c_obj_id = "'.$this->obj_id.'" '.$where.'
                    ORDER BY c_publ_date ASC;';

            $data = db::q($sql, records);

            $this->count = count($data);

            $this->data = array();
            while (list($num, $val) = each($data)) {
                $key = (empty($val['c_parent_id'])) ? 0 : $val['c_parent_id'];
                $this->data[$key][] = $val;
            }

            $this->get_data = true;
        }
    }

    // Скидывает индекс перебора для всего дерева комментариев
    function reset() {
        $this->cycle = array();
    }

    // Скидывает индекс перебора для указанного комментария
    function resetFor($parent_id = 0) {
        $this->cycle[$parent_id] = 0;
    }

    // Вернет общее количество комментариев
    function getCount() {
        return $this->count;
    }

    // Проверяет существуют ли вложенные комментарии для $parent_id
    function countComments($parent_id = 0) {

        $this->getData();

        if ($this->issetComments($parent_id))
            return count($this->data[$parent_id]);
        else
            return 0;
    }

    // Проверяет существуют ли вложенные комментарии для $parent_id
    function issetComments($parent_id = 0) {

        $this->getData();

        if (!empty($this->data))
            return (isset($this->data[$parent_id]));
        else
            return false;
    }

    // Вернет следующий по списку вложеный комментарий для $parent_id
    function getComment($parent_id = 0) {

        if ($this->issetComments($parent_id)) {

                $data = $this->data[$parent_id];

                if (!isset($this->cycle[$parent_id]))
                    $this->cycle[$parent_id] = 0;

                $num = $this->cycle[$parent_id];

                if (isset($data[$num])) {

                    $this->cycle[$parent_id]++;

                    if (!isset($this->comments[$data[$num]['c_id']]))
                        $this->comments[$data[$num]['c_id']] = new comment($data[$num]);
                                                                                                  
                    return $this->comments[$data[$num]['c_id']];
                }
        }

        return false;
    }

    /**
     * @return null
     * @param string $email - E-mail
     * @param integer $obj_id - ID объекта, к которому привязанны комментарии
     * @desc Отпсывает указанный E-mail от получения уведомлений о новых комментариях
     */
    static public function unsubscribe($email, $obj_id) {

        $email = str_replace(array('__', '_'), array('@', '.'), $email);
        $obj_id = system::checkVar($obj_id, isInt);

        db::q('UPDATE <<comments>> SET c_send_email = 0
              WHERE c_email = "'.$email.'" and c_obj_id = "'.$obj_id.'";');
    }

    /**
     * @return comment
     * @param integer $comment_id - ID комментария
     * @desc Вернет указанный комментарий
     */
    static public function get($comment_id) {

        $comment = new comment($comment_id);

        if ($comment->id() == $comment_id)
            return $comment;
        else
            return false;

    }

    /**
     * @return integer
     * @param integer $obj_id - ID объекта
     * @param boolean $only_active - Если true - вернет количество активных (проверенных) комментариев
     * @desc Вернет общее количество комментариев для указанного объекта
     */
    static public function getAllCount($obj_id, $only_active = false) {

        $obj_id = system::checkVar($obj_id, isInt);

        if ($obj_id) {

            $where = ($only_active) ? ' and c_active = 1' : '';
            $count = db::q('SELECT count(c_id) FROM <<comments>> WHERE c_obj_id = "'.$obj_id.'" '.$where.';', value);

            if ($count)
                return $count;
        }

        return 0;
    }



}

?>