<?php

class searchMacros {

    /**
	* @return HTML
	* @param int $obj_id - ID раздела, для которого необходимо вывести теги.
	* @param string $templ_name - Шаблон оформления.
	* @desc МАКРОС: Выводит список тегов для указанной страницы.
	*/
    public function tags($obj_id, $templ_name = 'tags') {


        $templ_file = '/search/'.$templ_name.'.tpl';
	    $TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
		    return page::errorNotFound('search.tags', $templ_file);

        $tags = tags::getTagsForObject($obj_id);


        $list = '';
        while (list($num, $tag) = each($tags)) {
            page::assign('obj.id', $tag['id']);
            page::assign('obj.name', $tag['name']);
            page::assign('obj.count', $tag['count']);
            page::assign('obj.url', '/search/tag/'.$tag['id']);

            $list .= (!empty($num)) ? page::parse($TEMPLATE['separator']) : '';
            $list .= page::parse($TEMPLATE['list']);
        }

        if (!empty($list)) {
            page::assign('list', $list);
            return page::parse($TEMPLATE['frame']);
        } else return page::parse($TEMPLATE['empty']);
    }

}

?>