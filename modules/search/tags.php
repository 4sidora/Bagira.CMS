<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс для работы с тегами.
*/

class tags {

    static private $tags = array();

    /**
	* @return NULL
	* @param string $str_tags - список тегов разделенных запятой
	* @param int $obj_id - ID ORM объекта
	* @desc Изменяет список тегов для указанного объекта
	*/
    static function changeTags($str_tags, $obj_id) {

        $str_tags = system::checkVar($str_tags, isString);
        $obj_id = system::checkVar($obj_id, isInt);

        // Формируем массив новых тегов
        $new_tags = Array();
        if (!empty($str_tags)) {
            $tmp = explode(",", $str_tags);
            foreach($tmp as $v) {
                $v = trim($v);
                if (!empty($v)) 
                    $new_tags[$v] = 1;
            }
        }

        // Получаем список существующих тегов
        $old_tags = self::getTagsForObject($obj_id);

        // Удаляем все связи с существующими тегами
        db::q('DELETE FROM <<tags_rels>> WHERE tr_obj_id = "'.$obj_id.'";');

        // Перебираем все существующие теги. И удаляем не нужные.
        while(list($num, $tag) = each($old_tags)) {

            if (isset($new_tags[$tag['name']])) {

                db::q('INSERT INTO <<tags_rels>> SET tr_tag_id = "'.$tag['id'].'", tr_obj_id = "'.$obj_id.'";');
                unset($new_tags[$tag['name']]);

            } else {

                if ($tag['count'] == 1) {
                    db::q('DELETE FROM <<tags>> WHERE t_id = "'.$tag['id'].'";');    
                } else {
                    db::q('UPDATE <<tags>> SET t_count = t_count - 1 WHERE t_id = "'.$tag['id'].'";');
                }
            }
        }

        // Добавляем все новые теги
        if (!empty($new_tags))
            while(list($tag, $num) = each($new_tags)) {
                $tag_id = self::getTag($tag);
                db::q('INSERT INTO <<tags_rels>> SET tr_tag_id = "'.$tag_id.'", tr_obj_id = "'.$obj_id.'";');
            }
    }



    static function infoTag($tag_id) {

        $tag = db::q('SELECT t_id id, t_name name, t_count count FROM <<tags>> WHERE t_id = "'.$tag_id.'";', record);

        if ($tag)
            return $tag;
    }

    // Вернет ID указанного тега. Если тега нет в БД, создаст его.
    private static function getTag($tag) {

        $tag_id = db::q('SELECT t_id FROM <<tags>> WHERE t_name = "'.$tag.'";', value);

        if (!$tag_id) 
            $tag_id = db::q('INSERT INTO <<tags>> SET t_name = "'.$tag.'", t_count = "1";');
        else
            db::q('UPDATE <<tags>> SET t_count = t_count + 1 WHERE t_id = "'.$tag_id.'";');

        return $tag_id;
    }
       
    // Вернет список тегов, для указанного объекта
    static function getTagsForObject($obj_id) {

        if (!isset(self::$tags[$obj_id]))
            
            self::$tags[$obj_id] = db::q('
                SELECT t_id id, t_name name, t_count count
                FROM <<tags>>, <<tags_rels>>
                WHERE t_id = tr_tag_id and tr_obj_id = "'.$obj_id.'"
                ORDER BY t_id ;
            ', records, 0);

        return self::$tags[$obj_id];
    }

    // Вернет список всех частоиспользуемых тегов
    static function getTags($limit = 100) {

        $limit = system::checkVar($limit, isInt);

        if (empty($limit))
            $limit = 100;

        $tags = db::q('SELECT t_id id, t_name name, t_count count  FROM <<tags>>, <<tags_rels>>
                        WHERE t_id = tr_tag_id and tr_obj_id = "'.$obj_id.'"
                        ORDER BY t_id LIMIT '.$limit.';', records, 0);

        return $tags;


    }

    /**
	* @return array(
	            	'count' => 0,     		// Общее количество страниц
	            	'pages' => array()      // Результат поиска. Список ID страниц с учетом параметра $limit
	            );
	* @param array $tags - список ID тегов
	* @param int $limit - Максимальное количество страниц в результатах поиска
	* @param int $start_pos - Порядковый номер страницы, с которой начнется вывод результатов
	* @param array $classes_filter - Список ORM-классов, по которым ведется поиск
	* @desc Формирует список страниц имеющих указанные теги.
	*/
    static function find($tags, $limit = 10, $start_pos = 0, $classes_filter = array()) {

        if (!empty($tags)) {

            $q_tags = '';
            foreach($tags as $id) {
                $or = (empty($q_tags)) ? '' : ' AND ';
                $q_tags .= $or.'t_id = "'.$id.'"';
            }

            // Генерируем sql-запрос
            if (!empty($q_tags)) {


                // Устанавливаем видимость объектов с учестом прав доступа пользователя
                $state = (system::$isAdmin && reg::getKey(ormPages::getPrefix().'/no_view_no_edit')) ? '= 2' : '> 0';
                $groups = '';
		      	$m = user::getGroups();
		       	while(list($key, $val) = each($m))
		        	$groups .= ' or r_group_id = "'.$key.'"';

		   		// Формируем фильтр по классам
	            $cf = '';
	            if (!empty($classes_filter) && is_array($classes_filter)) {

	            	while(list($num, $class) = each($classes_filter))
	            		if ($c = ormClasses::get($class)) {
		            		$or = (empty($cf)) ? '' : ' OR ';
		              		$cf .= $or.'o_class_id = "'.$c->id().'"';
	              		}

	            	if (!empty($cf))
	            		$cf = 'and ('.$cf.')';
	            }

                // Получаем количество объектов
                $sql = 'SELECT count(tr_obj_id) FROM (SELECT tr_obj_id
	            		FROM <<tags>>, <<tags_rels>>, <<rights>>, <<objects>>, <<pages>>
	            		WHERE t_id = tr_tag_id and
	            			tr_obj_id = r_obj_id and
	            			r_state '.$state.' and
	            			(
	            				r_group_id is NULL or
	            				r_group_id = "'.user::get('id').'"
	            				'.$groups.'
	            			) and
	            			('.$q_tags.') and
	            			tr_obj_id = o_id and
	            			o_id = p_obj_id and
	            			active = 1 '.$cf.'
	            		GROUP BY tr_obj_id) t;';

                $count = db::q($sql, value);

                // Получаем список объектов
                if ($count > 0) {

		            $sql = 'SELECT tr_obj_id page_id
		            		FROM <<tags>>, <<tags_rels>>, <<rights>>, <<objects>>, <<pages>>
		            		WHERE  t_id = tr_tag_id and
	            			    tr_obj_id = r_obj_id and
		            			r_state '.$state.' and
		            			(
		            				r_group_id is NULL or
		            				r_group_id = "'.user::get('id').'"
		            				'.$groups.'
		            			) and
		            			('.$q_tags.') and
		            			tr_obj_id = o_id and
		            			o_id = p_obj_id and
		            			active = 1 '.$cf.'
		            		GROUP BY tr_obj_id';

		            if (empty($start_pos))
		            	$sql .= ' LIMIT '.$limit.';';
		          	else
		          		$sql .= ' LIMIT '.$start_pos.', '.$limit.';';

		            $mas = db::q($sql, records);

	            } else $mas = array();

	            return array(
	            	'count' => $count,
	            	'pages' => $mas
	            );
            }

        }
    }

}

?>