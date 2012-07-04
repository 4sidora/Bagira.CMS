<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс для работы с ORM-страницами.
	Создание отдельного класса для страниц сайта было необходимо для увеличения
	скорости обработки списков страниц:
		- Построение меню
		- Построение навибара
		- Проверка существования урла
		- и прочее...
	Основное отличие в структуре хранения данных. Часто используемые поля были
	вынесены в отдельную таблицу.

	Нужно учитывать, что с помощью данного класса нельзя работать с ORM-объектами.
	В случае попытки класс выдаст предупреждение.
*/

class ormPage extends ormObject {

    private $no_load = true;
    private $lang_id, $domain_id;

    private $new_rights = Array();
    private $right_for_all = 0;
    private $right_state = 0;
    private $url = '';
    private $_url = '';


    /**
     * @return null
     * @param integer $obj_id - ID объекта, если не указан - дальнейшая работа в режиме добавления.
     * @param boolean $with_rights - Если TRUE, будут учитываться права текущего пользователя.
     * @desc Создание объекта и получение данных.
     */
    public function __construct($obj_id = 0, $with_rights = true) {

        if (!empty($obj_id) && is_numeric($obj_id)) {

            $obj_id = system::checkVar($obj_id, isInt);

            if ($with_rights) {
                $where = ormPages::getSqlForRights();
                $table = ', <<rights>>';
                $select = ', MAX(r_state) r_state';
            } else $where = $table = $select = '';

            $sql = 'SELECT *'.$select.'
        	    		FROM <<objects>>, <<pages>>'.$table.'
        				WHERE o_id = "'.$obj_id.'" and
        					  p_obj_id = o_id'.$where.';';

            $row = db::q($sql, record);

            if ($row !== false && !empty($row)) {

                $this->class = ormClasses::get($row['o_class_id']);

                if ($this->class->isPage()) {

                    $this->id = $obj_id;
                    $this->name = $row['o_name'];
                    $this->create_date = $row['o_create_date'];
                    $this->change_date = $row['o_change_date'];
                    $this->to_trash = $row['o_to_trash'];

                    $this->right_state = (isset($row['r_state'])) ? $row['r_state'] : 0;

                    // Информация о странице
                    while (list($key, $val) = each($row))
                        if (in_array($key, $this->page_fields))
                            $this->cur_prop[$key] = $val;
                } else {
                    $this->newError(44, 'Вы пытаетесь загрузить обычный объект с ID = '.$obj_id.'.
                    		Используйте для этого ormObjects::get();!');
                }

            } else
                $this->newError(45, 'Невозможно загрузить данные ORM-страницы!');

        } else if (is_array($obj_id) && isset($obj_id['o_id']) && isset($obj_id['o_class_id'])) {

            /*
            Загрузка данных в объект из другого источника (ormSelect::getObject(), ormPageCollection::getPageOfSection()).
            Используется для уменьшения дублирующих запросов, например при выборке
            объектов одного класса.
            */

            $this->id = system::checkVar($obj_id['o_id'], isInt);

            $this->class = ormClasses::get($obj_id['o_class_id']);

            if ($this->class->isPage()) {

                if (isset($obj_id['o_name']))
                    $this->name = system::checkVar($obj_id['o_name'], isString);

                if (isset($obj_id['o_create_date']))
                    $this->create_date = $obj_id['o_create_date'];

                if (isset($obj_id['o_change_date']))
                    $this->change_date = $obj_id['o_change_date'];

                if (isset($obj_id['o_to_trash']))
                    $this->to_trash = $obj_id['o_to_trash'];

                if (isset($obj_id['r_state']))
                    $this->right_state = $obj_id['r_state'];

                // Установка значений основных полей
                $this->loadFields();
                while (list($key, $field) = each ($this->fields))
                    if ($field['f_type'] > 1 && $field['f_type'] < 90)
                        $this->cur_prop[$key] = (isset($obj_id[$key])) ? $obj_id[$key] : $this->empty;

                // Информация о странице
                while (list($key, $val) = each ($obj_id))
                    if (in_array($key, $this->page_fields))
                        $this->cur_prop[$key] = $val;


            } else {
                $this->newError(44, 'Вы пытаетесь загрузить обычный объект с ID = '.$obj_id['o_id'].'.
                    		Используйте для этого ormObjects::get();!');
            }

        }
    }

    // Дозагружаем данные уже созданного объекта из указаного массива
    public function supplementData($data) {

        if (!empty($data) && is_array($data)) {

            while (list($key, $val) = each ($data))
                if (in_array($key, $this->page_fields))
                    $this->cur_prop[$key] = $val;

            parent::supplementData($data);
        }
    }



    // Загружаем данные полей
    protected function loadData($prinud = false, $fname = '') {


        if (isset($this->fields[$fname]) && $this->fields[$fname]['f_type'] < 90 &&
            (!isset($this->cur_prop[$fname]) || $this->cur_prop[$fname] == $this->empty) && $this->no_load) {

            if (!in_array($fname, $this->page_fields)) {

                //$prinud = true;
                $this->no_load = false;
                parent::loadData(true, $fname);

            } else if (isset($this->cur_prop[$fname]) && $this->cur_prop[$fname] === $this->empty){

                $sql =  '/* '.$fname.' */
	                SELECT * FROM <<pages>> WHERE p_obj_id = "'.$this->id.'";';

                $tmp = db::q($sql, record);

                if (!empty($tmp))
                    $this->cur_prop = array_merge($this->cur_prop, $tmp);

            }
        }
    }

    // Изменение свойств
    public function __set($name, $value){

        if ($this->isSysField($name)) {

            $this->new_prop[$name] = system::checkVar($value, isInt);

        } else {

            if ($name == 'pseudo_url') {

                // Проверка уникальности Псевдоурла
                $pseudo_url = system::checkVar($value, isPseudoUrl);

                if ($pseudo_url !== false) {

                    $parent_id = $this->getParentId();
                    $s = (empty($this->id)) ? '' : 'p_obj_id <> "'.$this->id.'" and ';
                    $s .= (empty($parent_id)) ? 'r_parent_id is NULL' : 'r_parent_id = "'.$parent_id.'"';

                    $sql = 'SELECT count(p_obj_id) FROM <<pages>>, <<objects>>, <<rels>>
							WHERE pseudo_url = "'.$pseudo_url.'" and
								  lang_id = "'.languages::curId().'" and
								  domain_id = "'.domains::curId().'" and
								  r_children_id = p_obj_id and '.$s.' and
								  o_id = p_obj_id and
								  o_to_trash = 0;';

                    $count = db::q($sql, value);

                    if ($count > 0) {
                        $this->newError(46, 'В текущем разделе уже есть объект с указанным псевдо адресом.', 'pseudo_url');
                        return false;
                    }

                } else {

                    $this->newError(47, 'Неправильно указан псевдо адрес!', 'pseudo_url');
                    return false;
                }

            } else if ($name == 'is_home_page') {

                // Смотрим можно ли поменять свойство "домашняя страница"
                $value = system::checkVar($value, isBool);

                if (!$value && isset($this->cur_prop[$name]) && $this->cur_prop[$name])
                    $value = true;

            }

            return parent::__set($name, $value);
        }
    }

    private function isSysField($name) {
        return ($name == 'template_id' || $name == 'template2_id' || $name == 'lang_id' || $name == 'domain_id');
    }

    public function __get($name) {

        if ($name == 'url' && !empty($this->id)){

            if (empty($this->url))
                $this->url = ormPages::getPageUrlById($this->id);
            //echo $this->id.'|'.$this->url.'<br/>';
            return $this->url;

        } else if ($name == '_url' && !empty($this->id)){

            if (empty($this->_url))
                if ($this->__get('other_link') != '') {

                    $link = $this->__get('other_link');

                    if ($link == '/first_subsection'){


                        $sel = new ormSelect();
                        /// $sel->fields('id');
                        $sel->findInPages();
                        $sel->where('parents', '=', $this->id);
                        $sel->limit(1);

                        if ($subsection = $sel->getObject()) {
                            $this->_url = $subsection->__get('_url');
                        }

                    } else $this->_url = $link;

                } else
                    $this->_url = ormPages::getPageUrlById($this->id);

            return $this->_url;

        } else if ($name == 'first_children_id' && !empty($this->id)){

            $sel = new ormSelect();
            $sel->findInPages();
            $sel->where('parents', '=', $this->id);
            $sel->fields('id');
            $sel->limit(1);
            $order_by = $this->__get('order_by');
            if (!empty($order_by)) {
                $pos = strpos($order_by, ' ');
                if ($pos) {
                    $parram = substr($order_by, $pos + 1);
                    $order_by = substr($order_by, 0, $pos);
                } else $parram = '';
                $sel->orderBy($order_by, $parram);
            }
            return $sel->getObject()->id;

        } else if ($name == 'count_children' && !empty($this->id)){

            return $this->countChildren();

        } else if ($this->isSysField($name) && isset($this->cur_prop[$name]))  {

            return $this->cur_prop[$name];

        } else if ($this->isSysField($name) && isset($this->new_prop[$name])) {

            return $this->new_prop[$name];

        } else {


            return parent::__get($name);

        }
    }

    public function save(){

        if (empty($this->id) && !isset($this->new_prop['template_id'])){

            $this->newError(1000, 'Для страницы необходимо установить шаблон оформления!');
            return false;

        } else if ($this->isEditable()) {

            $tmp = parent::save();

            if ($tmp !== false)
                ormPages::clearCache();
            
            return $tmp;

        } else {
            $this->newError(48, 'Вы не имеете прав для редактирования данной страницы!');
            return false;
        }
    }

    // Изменение объекта
    protected function changeObject(){

        $old_hp = (isset($this->cur_prop['is_home_page'])) ? $this->cur_prop['is_home_page'] : false;

        $ret = parent::changeObject();

        if ($ret !== false) {

            $this->saveRight();

            if (isset($this->new_prop['pseudo_url']) && strlen($this->new_prop['pseudo_url']) > 80)
                $this->new_prop['pseudo_url'] = $this->id;

            $fields = '';
            reset($this->new_prop);
            while (list($key, $value) = each ($this->new_prop))
                if ($this->isPageField($key))
                    $fields .= $this->procValue($key, $value);

            $fields = substr($fields, 0, strlen($fields)-2);

            if (!empty($fields)) {

                $this->setHomePage($old_hp);
                $sql = 'UPDATE <<pages>> SET '.$fields.' WHERE p_obj_id = "'.$this->id.'";';

                if (db::q($sql) !== false){
                    $ret = $this->id;
                    searchIndex::autoIndex($this);
                    system::revue($this, 'Изменил данные страницы', info);
                } else {
                    $ret = false;
                    system::revue($this, 'Произошла ошибка при изменении данных страницы', error);
                }
            }
        }

        return $ret;

    }

    // Создание нового объекта    -		-		-		-		-		-		-		-
    protected function createObject(){

        // Проверяем, если в структуре сайта нет домашней страницы, делаем текущую таковой

        if (isset($this->new_prop['is_home_page']) && !$this->new_prop['is_home_page']) {
            $hp = db::q('SELECT p_obj_id FROM <<pages>>
		        		WHERE lang_id = "'.languages::curId().'" and
				       		  domain_id = "'.domains::curId().'" and
			        		  is_home_page = 1', value);
            $this->new_prop['is_home_page'] = (empty($hp)) ? 1 : 0;
        }

        $ret = parent::createObject();

        if ($ret !== false) {

            $this->saveRight(true);

            if (isset($this->new_prop['pseudo_url']) && strlen($this->new_prop['pseudo_url']) > 80)
                $this->new_prop['pseudo_url'] = $this->id;

            $fields = '';
            reset($this->new_prop);
            while (list($key, $value) = each ($this->new_prop))
                if ($this->isPageField($key))
                    $fields .= $this->procValue($key, $value);

            $fields = substr($fields, 0, strlen($fields)-2);

            if (!empty($fields)) {

                $this->setHomePage(false);
                $fields = ', '.$fields;
                $sql = 'INSERT INTO <<pages>>
		       			SET p_obj_id = "'.$this->id.'",
		       				lang_id = "'.languages::curId().'",
		       				domain_id = "'.domains::curId().'"'.$fields.';';

                if (db::q($sql) !== false){
                    $ret = $this->id;
                    searchIndex::autoIndex($this);
                    ormPages::clearCache();
                    system::revue($this, 'Добавил страницу', info);
                } else $ret = false;

            }
        }

        return $ret;

    }

    protected function procValue($field, $value) {

        if ($this->isSysField($field)) {

            if (empty($value))
                return '`'.$field.'` = Null, ';
            else
                return '`'.$field.'` = "'.$value.'", ';

        } else
            return parent::procValue($field, $value);

    }

    // Если страница помечена домашней, снимаем пометку с предыдущей дом. страницы.
    private function setHomePage($old_hp) {

        if (isset($this->new_prop['is_home_page']) && $this->new_prop['is_home_page'] && !$old_hp) {

            db::q('UPDATE <<pages>> SET is_home_page = 0
	        	 WHERE lang_id = "'.languages::curId().'" and
		       		   domain_id = "'.domains::curId().'" and
	        			is_home_page = 1');
        }
    }


    /**
     * @return null
     * @param integer $group_id - ID группы или пользователя
     * @param integer $state - Состояние доступа:
    0	-	Запрещено
    1	-	Только чтение
    2	-	Чтение и запись
     * @desc Установка прав доступа к странице
     */
    public function setRight($group_id, $state) {
        $this->new_rights[system::checkVar($group_id, isInt)] = system::checkVar($state, isInt);
    }

    /**
     * @return null
     * @param integer $state - Право доступа к странице
     * @desc Установка для всех (групп и пользователей) единого права доступа
     */
    public function setRightForAll($state) {
        $this->right_for_all = system::checkVar($state, isInt);
        $this->right_state = $this->right_for_all;
    }

    // Выставляет права доступа по умолчанию.
    public function clearRight() {
        $this->right_for_all = -1;
    }


    private function saveRight($prinud = false) {

        if (!empty($this->right_for_all) || !empty($this->new_rights) || $prinud)
            db::q('DELETE FROM <<rights>> WHERE r_obj_id = "'.$this->id.'";');

        if (!empty($this->new_rights)) {

            while (list($id, $state) = each ($this->new_rights))
                db::q('INSERT INTO <<rights>> SET r_obj_id = "'.$this->id.'", r_state = "'.$state.'", r_group_id = "'.$id.'";');

        } else if ($this->right_for_all > 0 || $prinud) {

            $state = ($prinud) ? 2 : $this->right_for_all;
            db::q('INSERT INTO <<rights>> SET r_obj_id = "'.$this->id.'", r_state = "'.$state.'";');

        }

        if ($this->right_for_all == -1 || (!empty($this->new_rights) && !isset($this->new_rights[32])))
            db::q('INSERT INTO <<rights>> SET r_obj_id = "'.$this->id.'", r_state = 2, r_group_id = 32;');
    }

    // Вернет TRUE, если страницу можно редактировать
    public function isEditable(){

        if ($this->right_state == 2)
            return true;

        if (!empty($this->id)) {
            //echo $this->id.' = '.$this->right_state.'<br />';
            if ($this->right_state != 2)
                return false;

            $parents = $this->getParents();

            if (!empty($parents)) {

                $ret = false;
                while(list($id, $p) = each($parents)) {
                    $obj = ormPages::get($id);
                    if ($obj instanceof ormPage)
                        $ret = $obj->isEditable();
                }

                if ($ret) $this->right_state = 2;
                return $ret;
            }
        }

        return true;
    }

    // Вернет следующий по списку вложенный объект
    public function getChild($with_trash = false){

        $this->getChildren(-1, $with_trash);

        if (count($this->childr) > 0) {
            if (isset($this->childr[$this->child_num])) {

                $obj = ormPages::get($this->childr[$this->child_num]);

                if (!($obj instanceof ormObject))
                    $obj = ormObjects::get($this->childr[$this->child_num]);

                if ($obj instanceof ormObject) {

                    $this->child_num++;
                    if ($this->child_num > count($this->childr))
                        $this->child_num = 0;

                    return $obj;
                }

            }
        }
    }


    // Вернет true, если у страницы есть подразделы
    public function issetChildren(){
        return ormPages::issetChildren($this->id);
    }

    // Вернет количество подразделов у страницы
    public function countChildren(){
        return ormPages::getCountOfSection($this->id);
    }

    // Изменение родителя объекта
    protected function changeParents($isUpd = true) {

        $ret = true;

        if ((!empty($this->new_parents) || $this->del_parents) && is_array($this->new_parents)) {

            // Удаляем все старые связи
            if ($isUpd && $this->del_parents)
                $ret = db::q('DELETE FROM <<rels>> WHERE r_children_id = "'.$this->id.'" and r_field_id is NULL;');
            $this->del_parents = false;

            // Добавляем новые связи
            reset($this->new_parents);
            while(list($key, $val) = each($this->new_parents)) {

                if (empty($val['parent_id'])){
                    $parent_sql = 'r_parent_id is NULL and';
                    $parent_sql2 = '';
                } else {
                    $parent_sql = 'r_parent_id = "'.$val['parent_id'].'" and';
                    $parent_sql2 = 'r_parent_id = "'.$val['parent_id'].'",';
                }


                if (!$isUpd || empty($val['position'])) {

					// Определяем позицию при добавлении объекта
					$parent = ormPages::get($val['parent_id']);

					if (!empty($val['parent_id']) && $parent->addto) {
						$minmax = 'MIN';
						$pos = -1;

						// Избавляемся от отрицательных позиций
						db::q('UPDATE <<rels>> r, <<pages>> p
		             			   SET r.r_position = r.r_position + 1
		                           WHERE '.$parent_sql.'
			        				  	 r.r_field_id is NULL and
			        				  r.r_children_id = p.p_obj_id and
			        				  p.lang_id = "'.languages::curId().'" and
		       		   				  p.domain_id = "'.domains::curId().'";');
					} else {
						$minmax = 'MAX';
						$pos = 1;
					}

					$sql = 'SELECT '.$minmax.'(r_position)
	                	    		FROM <<rels>>, <<pages>>
			        				WHERE '.$parent_sql.'
			        					  r_field_id is NULL and
			        					  r_children_id = p_obj_id and
			        					  lang_id = "'.languages::curId().'" and
		       			   				  domain_id = "'.domains::curId().'";';

					$val['position'] = db::q($sql, value) + $pos;

                } else if ($isUpd && !empty($val['position'])) {

                    $parent_sql = 'r.'.$parent_sql;
                    // Изменения при обновлении объекта
                    $old_pos = (isset($this->parents[$val['parent_id']])) ? $this->parents[$val['parent_id']]['position'] : 0;

                    if (empty($old_pos) && !empty($val['position'])) {

                        // Если добавили нового родителя
                        db::q('UPDATE <<rels>> r, <<pages>> p
								   SET r.r_position = r.r_position + 1
			                       WHERE r.r_position >= "'.$val['position'].'" and
			                             '.$parent_sql.'
			        				  	 r.r_field_id is NULL and
			        				  r.r_children_id = p.p_obj_id and
			        				  p.lang_id = "'.languages::curId().'" and
		       		   				  p.domain_id = "'.domains::curId().'";');

                    } else if ($val['position'] < $old_pos) {

                        // Если перенесли ниже по списку
                        db::q('UPDATE <<rels>> r, <<pages>> p
		             			   SET r.r_position = r.r_position + 1
		                           WHERE r.r_position >= "'.$val['position'].'" and
		                                 r.r_position < "'.$old_pos.'" and
		                                 '.$parent_sql.'
			        				  	 r.r_field_id is NULL and
			        				  r.r_children_id = p.p_obj_id and
			        				  p.lang_id = "'.languages::curId().'" and
		       		   				  p.domain_id = "'.domains::curId().'";');

                    } else if ($val['position'] > $old_pos) {

                        // Если перенесли выше по списку
                        db::q('UPDATE <<rels>> r, <<pages>> p
		              			   SET r.r_position = r.r_position - 1
		                           WHERE r.r_position <= "'.$val['position'].'" and
		                                 r.r_position > "'.$old_pos.'" and
		                                 '.$parent_sql.'
			        				  	 r.r_field_id is NULL and
			        				  r.r_children_id = p.p_obj_id and
			        				  p.lang_id = "'.languages::curId().'" and
		       		   				  p.domain_id = "'.domains::curId().'";');
                    }
                }

                if (empty($tmp_parent_id)) {
                    $tmp_parent_id = $val['parent_id'];
                    $tmp_position = $val['position'];
                }

                // Добавляем связь с родителем
                $sql = 'INSERT INTO <<rels>>
							SET '.$parent_sql2.'
								r_position = "'.$val['position'].'",
								r_children_id = "'.$this->id.'";';

                $ret = db::q($sql);
            }
        }

        // Если все нормально, обновляем свойства объекта
        if ($ret === false) {

            $this->newError(43, 'Произошла ошибка при изменении родителя объекта!');

        } else if (is_array($this->new_parents) && !empty($this->new_parents)) {

            $this->parents = $this->new_parents;
            $this->new_parents = array();

            if (!empty($tmp_parent_id)) {
                $this->parent_id = $tmp_parent_id;
                $this->position = $tmp_position;
            }
        }
    }

    // Удаление страницы
    public function delete(){
        if ($this->isEditable()) {
            ormPages::clearCache();
            return parent::delete();
        } else {
            $this->newError(48, 'Вы не имеете прав для удаления данной страницы!');
            return false;
        }
    }

    // Перемещение в корзину
    public function toTrash(){
        if ($this->isEditable()) {
            ormPages::clearCache();
            return parent::toTrash();
        } else {
            $this->newError(48, 'Вы не имеете прав для удаления данной страницы!');
            return false;
        }
    }

    // Загрузка данных страницы из $_POST
    public function loadFromPost() {

        if (isset($_POST['template_id']) && $this->isInheritor('section'))
            $this->__set('template_id', $_POST['template_id']);

        if (isset($_POST['template2_id']) && $this->isInheritor('section'))
            $this->__set('template2_id', $_POST['template2_id']);

        return parent::loadFromPost();
    }

    public function copy($with_child = true, $copyTo = 0) {

        if (!empty($this->id)) {

            $copy = new ormPage();
            $copy->setClass($this->getClass()->id());


            // Перенос данных полей
            $fields = $this->getClass()->loadFields();
            while(list($fname, $field) = each($fields))
                if (!empty($field['f_type']) && $field['f_type'] != 97 && $field['f_relation'] < 2 && $fname != 'pseudo_url' && $fname != 'tags')
                    $copy->__set($fname, $this->__get($fname));

            if (empty($copyTo)) {
                $copy->__set('name', $this->__get('name') . lang::get('copy'));
                $copy->__set('pseudo_url', $this->__get('pseudo_url') . rand(1000, 9999));
            } else
                $copy->__set('pseudo_url', $this->__get('pseudo_url'));

            $copy->__set('template_id', $this->__get('template_id'));
            $copy->__set('template2_id', $this->__get('template2_id'));
            $copy->__set('tags', $this->__get('_tags'));

            // Устанавливаем родителя
            if (empty($copyTo)) {
                $parents = $this->getParents();
                while(list($id, $parent) = each($parents)) // $parent['position']
                    $copy->setNewParent($id);
            } else $copy->setNewParent($copyTo);

            // Права доступа
            $rights = db::q('SELECT r_state, r_group_id FROM <<rights>> WHERE r_obj_id = "'.$this->id.'";', records);
            if (count($rights) === 1 && empty($rights[0]['r_group_id']))
                $copy->setRightForAll($rights[0]['r_state']);
            else
                while (list($key, $right) = each($rights))
                    $copy->setRight($right['r_group_id'], $right['r_state']);

            $copy->save();

            if (!$copy->issetErrors() && $with_child) {

                while($child = $this->getChild())
                    $child->copy(true, $copy->id);

                return true;

            } else if ($copy->issetErrors()) {
                //echo $copy->getErrorListText();
                return false;
            }

        }
    }

    public function delParent($parent_id) {
        if ($this->isEditable()) {
            parent::delParent($parent_id);
        }
    }


    // Восстанавливает страницу из корзины
    public function restore(){

        $restore = parent::restore();

        if ($restore) {

            $parent_id = $this->getParentId();
            $s = (empty($this->id)) ? '' : 'p_obj_id <> "'.$this->id.'" and ';
            $s .= (empty($parent_id)) ? 'r_parent_id is NULL' : 'r_parent_id = "'.$parent_id.'"';

            $sql = 'SELECT count(p_obj_id) FROM <<pages>>, <<objects>>, <<rels>>
							WHERE pseudo_url = "'.$this->__get('pseudo_url').'" and
								  lang_id = "'.languages::curId().'" and
								  domain_id = "'.domains::curId().'" and
								  r_children_id = p_obj_id and '.$s.' and
								  o_id = p_obj_id and
								  o_to_trash = 0;';

            $count = db::q($sql, value);

            if ($count > 0) {

                $pseudo_url = $this->__get('pseudo_url').rand(100, 999);

                db::q('UPDATE <<pages>>
                      SET pseudo_url="'.$pseudo_url.'"
                      WHERE p_obj_id = "'.$this->id.'";');

                return true;
            }
        }

        return false;
    }



}

?>