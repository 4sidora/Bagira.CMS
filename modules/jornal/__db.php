<?php

class __db {

	public function view() {


		ui::addLeftButton('Системный журнал', 'system_view');
        ui::addLeftButton('Журнал БД', 'db_view');

        function getState($val) {
        	if ($val == info)
        		return 'info';
        	else if ($val == error)
        		return 'error';
        	else if ($val == warning)
        		return 'warning';
        }

        function getEditUser($val, $obj) {

        	if (user::issetRight('user_upd', 'users'))
        		return '<a href="'.system::au().'/users/user_upd/'.$obj['rev_user_id'].'" target="_blank">'.$val.'</a>';
        	else
        		return $val;
        }


        $count = db::q('SELECT count(rev_id) count FROM <<revue>>, <<objects>> WHERE rev_obj_id = o_id;', value);
        $max_count = uiTable::getMaxCount();
        if(uiTable::getCurPage() != 1) {
		    $niz = (empty($start_pos)) ? uiTable::getCurPage() * $max_count - $max_count : 0;
			$limit = ' LIMIT '.$niz.', '.$max_count;
		} else $limit = ' LIMIT '.$max_count;


        $mas = db::q('SELECT rev_state, rev_user, rev_user_id, rev_datetime,
        			concat(rev_message, " <b>", o_name, "</b>") rev_msg, rev_ip
		        	FROM <<revue>>, <<objects>>
		        	WHERE rev_obj_id = o_id
		        	ORDER BY rev_datetime DESC '.$limit, records);

         /*
           Сделать фильтры:
           	- важность
           	- пользователь
           	- тип данных
           	- периуд времени
           	- IP
         */
        $table = new uiTable($mas, $count);

        $table->addColumn('rev_state', 'Важность', 0, false, false, 'getState');
        $table->addColumn('rev_user', 'Пользователь', 0, false, false, 'getEditUser');
        $table->addColumn('rev_msg', 'Действие', 400);
        $table->addColumn('rev_datetime', 'Дата / Время', 0, false, false, 'viewDateTime2');
        $table->addColumn('rev_ip', 'IP');

        $table->emptyText('В журнале нет записей!');

        return $table->getHTML();
 	}

}

?>