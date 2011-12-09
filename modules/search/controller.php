<?php

class controller {

    public function defAction() {

		page::globalVar('h1', lang::get('SEARCH_H1'));
		page::globalVar('title', lang::get('SEARCH_H1'));

		$templ_file = '/search/default.tpl';
		$TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
			return page::errorNotFound('search.getResult', $templ_file);

		if (!empty($_SESSION['search_words']) && empty($_POST['words']))
			$_POST['words'] = $_SESSION['search_words'];

        if (isset($_POST['classes']))
         	$_SESSION['search_classes'] = $_POST['classes'];

		if (!empty($_SESSION['search_classes']) && !isset($_POST['classes']))
			$_POST['classes'] = $_SESSION['search_classes'];

		if (!empty($_POST['words'])) {

            $words = $_POST['words'];
            $_SESSION['search_words'] = $_POST['words'];

            // Получаем список классов
            $classes_filter = array();
            if (!empty($_POST['classes'])) {
            	$tmp = explode(",", $_POST['classes']);
		        $classes_filter = Array();
		        foreach($tmp as $v) {
		            $v = trim($v);
		        	if (!empty($v))
		            	$classes_filter[] = $v;
		        }
            }

            $max_count = reg::getKey(ormPages::getPrefix('search').'/max_count');
            $start = (system::getCurrentNavNum() - 1) * $max_count;

			$result = searchIndex::find($_POST['words'], $max_count, $start, $classes_filter);

            $list = '';
            $fields_arr = array();

            if (!empty($result)) {
                $page_count = ceil($result['count'] / $max_count);
                while(list($num, $val) = each($result['pages'])) {

                    if ($page = ormPages::get($val['page_id'])) {

                        page::assign('obj.num', $num + $start + 1);
                        page::assign('obj.url', $page->_url);
                        page::assign('obj.rank', $val['rank']);

                        $cn = $page->getClass()->getSName();
                        $block_name = (isset($TEMPLATE['list_'.$cn])) ? 'list_'.$cn : 'list';

                        if (!isset($fields_arr[$block_name])) {
                            $fields_arr[$block_name] = array();
                            $fields = page::getFields('obj', $TEMPLATE[$block_name]);
                            if (isset($fields['obj']))
                                while(list($key, $val) = each($fields['obj']))
                                    if ($val != 'url' && $val != 'class' && $val != 'num' && $val != 'rank')
                                        $fields_arr[$block_name][$val] = $val;
                        }

                        reset($fields_arr[$block_name]);
                        while(list($key, $val) = each($fields_arr[$block_name]))
                            page::assign('obj.'.$key, $page->__get($key));
                            
                        $list .= page::parse($TEMPLATE[$block_name]);
                    }
                }
            } else $page_count = 0;

			page::assign('count_page', $page_count);

		} else $words = $list = '';

        if (!empty($list)) {

	        page::assign('list', $list);
	        page::fParse('list', $TEMPLATE['frame_list']);

        } else page::fParse('list', $TEMPLATE['not_found']);

        page::assign('words', $words);

		return page::parse($TEMPLATE['frame']);

 	}


    public function tagAction() {


        $templ_file = '/search/gettag.tpl';
		$TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
			return page::errorNotFound('search.getTag', $templ_file);

        if (system::issetUrl(2) && ($tag = tags::infoTag(system::url(2)))) {

            $msg = lang::get('SEARCH_H1_TAGS').' "'.$tag['name'].'"';
            page::globalVar('h1', $msg);
            page::globalVar('title', $msg);


            // Получаем список классов
            $classes_filter = array();
            if (!empty($_POST['classes'])) {
            	$tmp = explode(",", $_POST['classes']);
		        $classes_filter = Array();
		        foreach($tmp as $v) {
		            $v = trim($v);
		        	if (!empty($v))
		            	$classes_filter[] = $v;
		        }
            }

            $max_count = reg::getKey(ormPages::getPrefix('search').'/max_count');
            $start = (system::getCurrentNavNum() - 1) * $max_count;

			$result = tags::find(array($tag['id']), $max_count, $start, $classes_filter);

            $list = '';
			while(list($num, $val) = each($result['pages'])) {

				if ($page = ormPages::get($val['page_id'])) {

					page::assign('obj.id', $page->id);
					page::assign('obj.num', $num + $start + 1);
                    page::assign('obj.name', $page->name);
                    page::assign('obj.url', $page->_url);
                    //page::assign('obj.content', $page->content);

                    $cn = $page->getClass()->getSName();
                    $block_name = (isset($TEMPLATE['list_'.$cn])) ? 'list_'.$cn : 'list';

					$list .= page::parse($TEMPLATE[$block_name]);
				}
			}

			page::assign('count_page', ceil($result['count'] / $max_count));

        } else {
            $list = '';
            $msg = lang::get('SEARCH_H1_TAGS2');
            page::globalVar('h1', $msg);
            page::globalVar('title', $msg);
        }

        if (!empty($list)) {

	        page::assign('list', $list);
	        page::fParse('list', $TEMPLATE['frame_list']);

        } else page::fParse('list', $TEMPLATE['not_found']);

		return page::parse($TEMPLATE['frame']);

    }
}

?>