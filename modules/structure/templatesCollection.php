<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Статический класс коллекция для работы с шаблонами сайта.
*/

class templates {

    private static $templ = array();
    private static $templ_obj = array();
    private static $templ_rel = array();


    // Вернет все шаблоны для текущей языковой версии и текущего домена. Метод с кеширование результатов.
	static function getAll(){

		if (empty(self::$templ)) {
			$mas =  db::q('SELECT *, t_id id FROM <<template>>
	        			  WHERE t_lang_id = "'.languages::curId().'" and
	        					t_domain_id = "'.domains::curId().'";', records);
			self::$templ = array();
			while(list($key, $templ) = each($mas)) {
				self::$templ[$templ['t_id']] = $templ;
				self::$templ_rel[$templ['t_file']] = $templ['t_id'];
			}
		}
		return self::$templ;
	}

    /**
	* @return array
	* @param boolean $type - Назначение шаблонов:
			0	-	шаблон для страниц
			1	-	шаблон для объектов
	* @desc Вернет все шаблоны для текущей языковой версии и текущего домена. Метод без кеширования результатов.
	*/
	static function getByDestination($type = 0, $spec_name = false){

		$type = ($type) ? 1 : 0;
		$select = ($spec_name) ? 't_id id, concat(t_name, " (", t_file, ")") name' : '*, t_id id';
		return db::q('SELECT '.$select.' FROM <<template>>
        			  WHERE t_type = "'.$type.'" and
        			  		t_lang_id = "'.languages::curId().'" and
        					t_domain_id = "'.domains::curId().'"
        			  ORDER BY t_name;', records);

	}

    /**
	* @return object
	* @param string $val - ID или имя файла шаблона
	* @desc Вернет указанный шаблон, как объект класса template
	*/
	static function get($val){
		self::getAll();

		if (!is_numeric($val) && isset(self::$templ_rel[$val]))
        	$val = self::$templ_rel[$val];

        if (isset(self::$templ[$val])) {

		    if (!isset(self::$templ_obj[$val]))
             	self::$templ_obj[$val] = new template(self::$templ[$val]);

			return self::$templ_obj[$val];
		}
	}

    /**
	* @return integer -	ID шаблона
	* @param integer $obj_id - ID раздела сайта
	* @desc Определяет популярный (часто используемый) шаблон в указанном разделе
	*/
	static function getPopularForSection($obj_id){

        $sql = (empty($obj_id)) ? ' r_parent_id is NULL and ' : ' r_parent_id = "'.$obj_id.'" and ';
        
    	$mas = db::q('SELECT template_id, template2_id
    				FROM <<pages>>, <<objects>>, <<rels>>
    				WHERE '.$sql.'
	        			  r_field_id is NULL and
	        			  r_children_id = o_id and
	        			  p_obj_id = o_id and
	        			  o_to_trash = 0
	        		GROUP BY o_id;', records);

        if (count($mas) > 0)

        	return array(
        		self::getPopTemplate($mas, 'template_id'),
        		self::getPopTemplate($mas, 'template2_id')
        	);

        else if (!empty($obj_id))

            return array(
        		ormPages::get($obj_id)->__get('template_id'),
        		ormPages::get($obj_id)->__get('template2_id')
        	);

        else
            return array(0, 0);
    }

    private static function getPopTemplate($mas, $field){

    	$max_templ = array();
       	while(list($key, $val) = each($mas)){
        	if (isset($max_templ[$val[$field]]))
        		$max_templ[$val[$field]] ++;
        	else
        		$max_templ[$val[$field]] = 1;
       	}

        $max_count = 0;
        $templ_id = 0;
        while(list($templ, $count) = each($max_templ)){
        	if ($count > $max_count) {
        		$max_count = $count;
        		$templ_id = $templ;
        	}
        }

	    return $templ_id;
	}

}

?>