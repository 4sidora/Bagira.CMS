<?php

class votingMacros {

    /**
	* @return HTML
	* @param int $obj_id - ID голосования.
	* @param string $templ_name - Шаблон оформления.
	* @desc МАКРОС: Выводит указанное голосование.
	*/
    public function objView($obj_id, $templ_name = 'default') {


        $html = '';
        $id = (is_array($obj_id)) ? $obj_id['o_id'] : $obj_id;
        $ses = (empty($_SESSION['voting_'.$id])) ? '1' : '0';
        $key = 'vote'.$id.$ses;
        $ckey = md5(serialize($templ_name));


        if (($data = cache::get($key)) && isset($data[$ckey])) {

            $html = $data[$ckey];

        } else {

            if (!is_array($templ_name)) {
                $templ_file = '/voting/'.$templ_name.'.tpl';
                $TEMPLATE = page::getTemplate($templ_file);
            } else $TEMPLATE = $templ_name;

            if (!is_array($TEMPLATE))
                return page::errorNotFound('voting.viewVote', $templ_file);

            if (is_array($obj_id))
                $obj = ormPages::get($obj_id);
            else
                $obj = ormPages::get($obj_id, 'vote');

            if (is_a($obj, 'ormPage')) {

                $all_count = 0;
                $result = (!empty($_SESSION['voting_'.$obj->id]) || $obj->close) ? '_result' : '';

                $fields = page::getFields('obj', $TEMPLATE['vote']);

                // Выводим список вариантов ответов
                if (isset($fields['mono']) && in_array('answers', $fields['mono'])) {

                    $list = '';
                    $num = $max = 0;

                    $sel = new ormSelect('answer');
                    $sel->fields('name, count');
                    $sel->where('parents', '=', $obj->id);

                    // Находим самый популярный ответ
                    while($answ = $sel->getObject()) {
                        if ($max < $answ->count)
                            $max = $answ->count;
                        $all_count += $answ->count;
                    }

                    $pr = $all_count / 100;
                    if ($pr == 0) $pr = 1;

                    // Выводим список вариантов ответов
                    $sel->reset();
                    while($answ = $sel->getObject()) {

                        $num ++;
                        page::assign('obj.num', $num);
                        page::assign('class-first', ($num == 1) ? 'first' : '');
                        page::assign('class-last', ($num == $sel->getObjectCount()) ? 'last' : '');
                        page::assign('class-odd', ($num % 2 == 0) ? 'odd' : '');
                        page::assign('class-even', ($num % 2 != 0) ? 'even' : '');
                        page::assign('class-third', ($num % 3 == 0) ? 'third' : '');

                        page::assign('obj.id', $answ->id);
                        page::assign('obj.parent_id', $obj->id);
                        page::assign('obj.name', $answ->name);
                        page::assign('obj.count', $answ->count);

                        $procent = round($answ->count / $pr, 2);
                        page::assign('obj.percent', $procent);
                        page::assign('obj.per1', round($procent));
                        page::assign('obj.per2', 100 - round($procent));

                        $best = ($answ->count == $max) ? 'best' : '';
                        page::assign('class-best', $best);

                        $list .= page::parse($TEMPLATE['answer'.$result]);
                    }

                    page::assign('answers', $list);
                    page::assign('type', (($obj->multiselect) ? 'checkbox' : 'radio'));

                }

                // Парсим поля страницы
                if (isset($fields['obj']))
                    while(list($num, $name) = each($fields['obj']))
                        page::assign('obj.'.$name, $obj->__get($name));

                page::assign('obj.count', $all_count);

                $html = page::parse($TEMPLATE['vote'.$result]);
            }

            // Записываем в кэш
            $data[$ckey] = $html;
            cache::set($key, $data);
        }

        return $html;
    }


    /**
	* @return HTML
	* @param int $obj_id - ID раздела, из которого необходимо вывести список голосований.
     *                      Если вывести список всех голосований на сайте, укажите "all".
	* @param string $templ_name - Шаблон оформления.
    * @param int $max_count - Максимальное количество элементов в списке.
	* @desc МАКРОС: Выводит список голосований из указанного раздела. 
	*/
    public function objList($obj_id = 'all', $templ_name = 'default', $max_count = 10) {

        $templ_file = '/voting/'.$templ_name.'.tpl';
        $TEMPLATE = page::getTemplate($templ_file);

	    if (!is_array($TEMPLATE))
	    	return page::errorNotFound('voting.objList', $templ_file);

        $key = 'vote-list'.$obj_id.$templ_name.$max_count;

        if (!($data = cache::get($key))) {

            if ($obj_id != 'all') {
                $info = ormPages::getSectionByPath($obj_id);

                if ($info['section'] === false)
                    return '';

                if (is_a($info['section'], 'ormPage')) {
                    $section = $info['section'];
                    $obj_id = $section->id;
                }
            }

            // Получаем список голосований
            $sel = new ormSelect('vote');
            $sel->findInPages();
            $sel->where('active', '=', 1);
            $sel->where('view_in_menu', '=', 1);
            $sel->orderBy('publ_date', desc);
            $sel->limit($max_count);

            if (is_numeric($obj_id))
                $sel->where('parents', '=', $obj_id);

            // Узнаем какие поля объектов будут участвовать в выборке
            $fields_str = 'close, multiselect';
            $fields = page::getFields('obj', $TEMPLATE['vote_result'].$TEMPLATE['vote']);
            if (isset($fields['obj']))
                while(list($okey, $val) = each($fields['obj']))
                    if ($val != 'url' && $val != 'class' && $val != 'num')
                        $fields_str .= (empty($fields_str)) ? $val : ', '.$val;
            $sel->fields($fields_str);

            $data = $sel->getData();
            // Записываем в кэш
            cache::set($key, $data);
        }

        // Выводим список на страницу
      	$list = '';
        foreach ($data as $obj)
           	$list .= $this->objView($obj, $TEMPLATE);

        page::assign('list', $list);

        return page::parse($TEMPLATE['frame']);
    }


}

?>