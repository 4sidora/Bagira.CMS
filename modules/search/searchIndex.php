<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Основной класс модуля "Поиск". Производит индексирование страниц сайта и
	выдачу результатов поиска. Все методы являются статическими.
*/

class searchIndex extends searchRanking {

    static private $words = array();

    /**
	* @return array(
	            	'count' => 0,     		// Общее количество страниц
	            	'pages' => array()      // Результат поиска. Список ID страниц с учетом параметра $limit
	            );
	* @param string $words - Поисковой запрос
	* @param int $limit - Максимальное количество страниц в результатах поиска
	* @param int $start_pos - Порядковый номер страницы, с которой начнется вывод результатов
	* @param array $classes_filter - Список ORM-классов, по которым ведется поиск
	* @desc Формирует список страниц удовлетворяющих поисковой фразе.
	*/
    static function find($words, $limit = 10, $start_pos = 0, $classes_filter = array()) {

    	if (!empty($words)) {

            // Разбиваем запрос на отдельные слова
    		$tmp = self::splitString($words);

            $q_words = '';
            foreach($tmp as $word) {

            	$word = self::morphGetRoot($word);

            	if (!empty($word) && (strlen($word) > 2  || is_numeric($word))) {
                 	$or = (empty($q_words)) ? '' : ' OR ';
                 	$q_words .= $or.'w_name Like "'.$word.'%"';
                }
            }

            // Генерируем sql-запрос
            if (!empty($q_words)) {

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
                $sql = 'SELECT count(sr_obj_id) FROM (SELECT sr_obj_id
	            		FROM <<search_words>>, <<search_rankings>>, <<rights>>, <<objects>>, <<pages>>
	            		WHERE w_id = sr_word_id and
	            			sr_lang_id = "'.languages::curId().'" and
	            			sr_domain_id = "'.domains::curId().'" and
	            			sr_obj_id = r_obj_id and
	            			r_state '.$state.' and
	            			(
	            				r_group_id is NULL or
	            				r_group_id = "'.user::get('id').'"
	            				'.$groups.'
	            			) and
	            			('.$q_words.') and
	            			sr_obj_id = o_id and
	            			o_id = p_obj_id and
	            			active = 1 '.$cf.'
	            		GROUP BY sr_obj_id) t;';

                $count = db::q($sql, value);

                // Получаем список объектов
                if ($count > 0) {

		            $sql = 'SELECT sr_obj_id page_id, SUM(sr_rank) rank
		            		FROM <<search_words>>, <<search_rankings>>, <<rights>>, <<objects>>, <<pages>>
		            		WHERE w_id = sr_word_id and
		            			sr_lang_id = "'.languages::curId().'" and
		            			sr_domain_id = "'.domains::curId().'" and
		            			sr_obj_id = r_obj_id and
		            			r_state '.$state.' and
		            			(
		            				r_group_id is NULL or
		            				r_group_id = "'.user::get('id').'"
		            				'.$groups.'
		            			) and
		            			('.$q_words.') and
		            			sr_obj_id = o_id and
		            			o_id = p_obj_id and
		            			active = 1 '.$cf.'
		            		GROUP BY sr_obj_id
		            		ORDER BY SUM(sr_rank) DESC';

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

    // Вернет количество уникальных слов
    static function getCountWords() {
    	return db::q('SELECT count(w_id) FROM <<search_words>>;', value);
    }

    // Вернет количество проиндексированных страниц
    static function getCountPages() {
    	return db::q('SELECT count(sr_obj_id)
    				  FROM (SELECT sr_obj_id
		    				FROM <<search_rankings>>
		    				WHERE sr_lang_id = "'.languages::curId().'" and
					    	  	sr_domain_id = "'.domains::curId().'"
					    	GROUP BY sr_obj_id) t;', value);
    }

    // Очищает индекс поиска
	static function clear() {

    	db::q('DELETE FROM <<search_rankings>>
    		   WHERE sr_lang_id = "'.languages::curId().'" and
			    	 sr_domain_id = "'.domains::curId().'";');

		db::q('DELETE FROM <<search_words>>
		       WHERE NOT EXISTS (
		       		SELECT * FROM <<search_rankings>>
					WHERE sr_word_id = w_id
				);');

		reg::setKey(ormPages::getPrefix('search').'/index_date', '');
 	}

    // Очищает индекс поиска для указанной страницы
    static function delIndexForPage($page_id) {

    	if (system::checkVar($page_id, isInt))
    		db::q('DELETE FROM <<search_rankings>> WHERE sr_obj_id = "'.$page_id.'";');

    }


    /**
	* @return null
	* @param ormPage $page - страница для индексации
	* @desc Проводит индексацию страницы, предварительно удалив старые
			данные индексации и проверив возможность такой индексации.
	*/
    static function autoIndex($page) {

    	if (reg::getKey(ormPages::getPrefix('search').'/auto_index'))

    		if (is_a($page, 'ormPage')) {

	            // Если есть, удаляем старый индекс страницы
	            self::delIndexForPage($page->id);

    			self::indexPage($page);
    		}
    }

    // Индексирует указанную страницу
 	static function indexPage($page) {

    	if (is_a($page, 'ormPage')) {

            if ($page->in_search) {

                /*
                	Пробегаем по всем полям, данные которых участвуют в поиске. Разбиваем их содержимое
                	на отдельные словоформы и ранжируем в соотвествии с настройками.
                */
                self::parseContent($page->name, 'name');
	            $fields = $page->getClass()->loadFields();
	            while(list($fname, $field) = each($fields))
	            	if ($field['f_search'])
	            		self::parseContent($page->__get($fname), $fname);

                // Сохраняем все найденные слова в БД
	            while(list($word, $rank) = each(self::$words)) {

			        $word_id = self::getWordId(self::morphGetRoot($word));

			        db::q('INSERT INTO <<search_rankings>>
			        		SET sr_word_id = "'.$word_id.'",
			        			sr_rank = "'.$rank.'",
			        			sr_obj_id = "'.$page->id.'",
			        			sr_lang_id = "'.languages::curId().'",
			        			sr_domain_id = "'.domains::curId().'";');
	            }
            }
    	}
 	}

    // Разбивает указанный контент на слова и ранжирует их в соотвествии с настройками класса
 	static private function parseContent($content, $field) {

    	$words = self::splitString($content);
        $rank = (isset(self::$rank[$field])) ? self::$rank[$field] : self::$rank['content'];

        foreach($words as $word)
        	if (strlen($word) > 2 || is_numeric($word))
        		if (isset(self::$words[$word]))
        			self::$words[$word] += $rank;
        		else
        			self::$words[$word] = $rank;
    }

    // Проверяет имеется ли в индексе указанное слово. Если слово не найдено, оно добавляется в индекс.
    static private function getWordId($word) {

    	$word_id = db::q('SELECT w_id FROM <<search_words>> WHERE w_name = "'.$word.'";', value);

    	if (empty($word_id))
            $word_id = db::q('INSERT INTO <<search_words>> SET w_name = "'.$word.'";');

    	return $word_id;
    }

    /**
	* @return array - Список слов
	* @param string $str - произвольный текст
	* @desc Разбивает указанный текст на отдельные слова, убирая из него не нужные символы
	*/
 	static function splitString($str) {

        $to_space = Array("&nbsp;", "&quote;", "«", "»", "-", ".", ",", "?", ":", ";", "%", ")", "(", "/", 0x171, 0x187, "<", ">", "'", '"');

        $str = str_replace(">", "> ", $str);
        $str = str_replace('\\', " ", $str);

        $str = strip_tags($str);
        $str = str_replace($to_space, " ", $str);
        $tmp = explode(" ", $str);

        $res = Array();
        foreach($tmp as $v) {
                $v = trim($v);
                if(strlen($v) <= 1) continue;
                $res[] = $v;
        }

        return $res;
	}

    /**
	* @return string - основа слова
	* @param string $word - слово
	* @desc Находит основу указанного слова, основываясь на правилах русского языка.
	*/
	static function morphGetRoot($word) {

		$suf = Array();

		$suf[] = 'ование';
		$suf[] = 'ельный';
		$suf[] = 'енный';
		$suf[] = 'ировать';
		$suf[] = 'овая';
		$suf[] = 'ого';
		$suf[] = 'ему';
		$suf[] = 'ный';
		$suf[] = 'ями';
		$suf[] = 'чно';
		$suf[] = 'ах';
		$suf[] = 'ях';
		$suf[] = 'ей';
		$suf[] = 'ях';
		$suf[] = 'ом';
		$suf[] = 'ий';
		$suf[] = 'ые';
		$suf[] = 'ие';
		$suf[] = 'ый';
		$suf[] = 'ий';
		$suf[] = 'ам';
		$suf[] = 'ах';
		$suf[] = 'ми';
		$suf[] = 'ям';
		$suf[] = 'та';
		$suf[] = 'ов';

		$suf[] = 'ок';

		$suf[] = 'сичк'; //женский вариант

		$suf[] = 'ик'; //ум-ласкать
		$suf[] = 'ки'; //ум-ласкать
		$suf[] = 'ка';

		$suf[] = 'ся';        //глагол - возвр.
		$suf[] = 'ть';        //глагол - инфинитив
		$suf[] = 'но';

		$suf[] = 'ец';
        $suf[] = 'ая';
		$suf[] = 'у';
		$suf[] = 'и';
		$suf[] = 'ы';
		$suf[] = 'е';
		$suf[] = 'а';
		$suf[] = 'я';
		$suf[] = 'ь';
		$suf[] = 'о';
		$suf[] = 'ю';
		$suf[] = 'ч';

		$rsuf = Array();
		$rsuf[] = 'ир';
		$rsuf[] = 'ов';
		$rsuf[] = 'ев';
		$rsuf[] = 'и';

        $min_word_l = 3;

        $tsuf = $suf;
        $sz = sizeof($tsuf);
        for($i = 0; $i < $sz; $i++) {
                $csuf = $tsuf[$i];
                $suf_l = strlen($csuf);
                $word_l = strlen($word);

                if( ($word_l - $suf_l) <= $min_word_l)
                        continue;

                if(substr($word, $word_l-$suf_l, $suf_l) == $csuf) {
                        $word = substr($word, 0, $word_l-$suf_l);

                }
        }

        $tsuf = $rsuf;
        $sz = sizeof($tsuf);
        for($i = 0; $i < $sz; $i++) {
                $csuf = $tsuf[$i];
                $suf_l = strlen($csuf);
                $word_l = strlen($word);

                if( ($word_l - $suf_l) <= $min_word_l)
                        continue;

                if(substr($word, $word_l-$suf_l, $suf_l) == $csuf) {
                        $word = substr($word, 0, $word_l-$suf_l);
                }
        }

        return $word;
	}


}

?>