<?php

class __list {

	// вывод списка
	public function defAction() {


        function getUserName($val, $obj) {

            $name = $val.' ('.$obj['c_email'].')';

            if (!empty($obj['c_user_id']) && user::issetRight('user_upd', 'users')) {
                $url = system::au().'/users/user_upd/'.$obj['c_user_id'];
                $name = '<a href="'.$url.'" target="_blank">'.$name.'</a>';
            }
            return $name;
        }

        function getCommentText($val, $obj) {
            return strip_tags($val);
        }


        $count = db::q('SELECT count(c_id) count FROM <<comments>>;', value);
        $max_count = uiTable::getMaxCount();
        if(uiTable::getCurPage() != 1) {
		    $niz = (empty($start_pos)) ? uiTable::getCurPage() * $max_count - $max_count : 0;
			$limit = 'LIMIT '.$niz.', '.$max_count;
		} else $limit = 'LIMIT '.$max_count;

        $sql = 'SELECT c_id id, c_active active, c_text, c_username, c_publ_date, c_email, c_user_id
            FROM <<comments>> ORDER BY c_publ_date DESC '.$limit.';';

        $mas = db::q($sql, records);

        $table = new uiTable($mas, $count);
        $table->addColumn('c_text', 'Текст', 500, 0, 1, 'getCommentText');
        $table->addColumn('c_username', 'Пользователь', 200, 0, 0, 'getUserName');
        $table->addColumn('c_publ_date', 'Дата создания', 200, 0, 1, 'viewDateTime');

        $table->defaultRight('comment_upd');
        $table->addRight('comment_upd', 'edit', single);
        $table->addRight('comment_act', 'active', multi);
        $table->addRight('comment_del', 'drop', multi);

        return $table->getHTML();

 	}


}

?>