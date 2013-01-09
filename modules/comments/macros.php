<?php

class commentsMacros {


    /**
	* @return HTML
    * @param int $page_id - ID страницы
	* @param string $templ_name - Шаблон оформления
	* @desc МАКРОС: Выводит количество комментариев для указанной страницы
	*/
    public function count($page_id, $templ_name = 'count') {

        $key = 'count_comments'.$page_id;

        if (($data = cache::get($key)) && isset($data[$templ_name])) {

            $html = $data[$templ_name];

        } else {

            $templ_file = '/comments/'.$templ_name.'.tpl';
            $TEMPLATE = page::getTemplate($templ_file);

            if (!is_array($TEMPLATE))
                return page::errorNotFound('comments.count', $templ_file);

            // Получаем список комментариев
            $count = comments::getAllCount($page_id);

            page::assign('obj_id', $page_id);
            page::assign('count', $count);

            if (empty($count) &&  isset($TEMPLATE['empty']))
                $html = page::parse($TEMPLATE['empty']);
            else
                $html = page::parse($TEMPLATE['frame']);

            // Записываем в кэш
            $data[$templ_name] = $html;
            cache::set($key, $data);
        }

        return $html;
    }

    /**
	* @return HTML - Форма добавления комментария
    * @param int $page_id - ID страницы для которой нужно создать форму
	* @param string $templ_name - Шаблон оформления формы
	* @desc МАКРОС: Строит форму отправки комментария
	*/
    public function form($page_id, $templ_name = 'addform') {

        $templ_file = '/comments/'.$templ_name.'.tpl';
	    $TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
		    return page::errorNotFound('comments.form', $templ_file);

        page::assign('obj_id', $page_id);

        // Если запрещенно комментировать гостям, выводим соответствующее сообщение
        if (user::isGuest() && reg::getKey('/comments/only_reg'))
            return page::parse($TEMPLATE['no_auth']);

        // Выводим форму
        if (user::isGuest()) {
            page::fParse('capcha', $TEMPLATE['capcha']);
            page::assign('username', '');
            page::assign('email', '');
        } else {
            page::assign('capcha', '');
            page::assign('username', user::get('name'));
            page::assign('email', user::get('email'));
        }

        return page::parse($TEMPLATE['frame']);

    }

    // Вспомогательный метод для метода tree()
    private function getCommentList(comments $tree, $parent_id, $TEMPLATE) {

        $all_count = $tree->countComments($parent_id);
        $list = '';
        $num = 0;

        while($comment = $tree->getComment($parent_id)) {

            $sub_comments = ($tree->issetComments($parent_id)) ? $this->getCommentList($tree, $comment->id(), $TEMPLATE) : '';

            $num ++;
            page::assign('obj.num', $num);
            page::assign('class-first', ($num == 1) ? 'first' : '');
	        page::assign('class-last', ($num == $all_count) ? 'last' : '');
	        page::assign('class-odd', ($num % 2 == 0) ? 'odd' : '');
	        page::assign('class-even', ($num % 2 != 0) ? 'even' : '');
            page::assign('class-third', ($num % 3 == 0) ? 'third' : '');

            page::assign('obj.id', $comment->id());
            page::assign('obj.parent_id', $comment->getParentId());
            page::assign('obj.user_id', $comment->getUserId());

            page::assign('obj.publ_date', $comment->getPublDate());
            page::assign('obj.username', $comment->getUserName());
            page::assign('obj.email', $comment->getEmail());
            page::assign('obj.text', $comment->getText());
            page::assign('obj.rate', $comment->getRate());

            if (isset($_SESSION['comments_rate'][$comment->id()]))
                page::fParse('rate', $TEMPLATE['rate_change']);
            else
                page::fParse('rate', $TEMPLATE['rate']);

            if (!empty($sub_comments)) {
                page::assign('list', $sub_comments);
                page::fParse('list', $TEMPLATE['frame_list']);
            } else page::assign('list', '');

            $list .= page::parse($TEMPLATE['list']);
        }
        
        return $list;
    }

    /**
	* @return HTML - Дерево комментариев
    * @param int $page_id - ID страницы для которой нужно создать дерево
	* @param string $templ_name - Шаблон оформления дерева
	* @desc МАКРОС: Строит список (дерево) комментариев для указанной страницы
	*/
    public function tree($page_id, $templ_name = 'tree') {

        $key = 'comments'.$page_id;

        if (!($data = cache::get($key))) {

            $templ_file = '/comments/'.$templ_name.'.tpl';
            $TEMPLATE = page::getTemplate($templ_file);

            if (!is_array($TEMPLATE))
                return page::errorNotFound('comments.tree', $templ_file);

            // Получаем список комментариев
            $tree = new comments($page_id);
            $tree->onlyActive(!reg::getKey('/comments/show_noactive'));

            $list = $this->getCommentList($tree, 0, $TEMPLATE);
            page::assign('obj_id', $page_id);

            if (empty($list))
                $data = page::parse($TEMPLATE['empty']);
            else {
                page::assign('list', $list);
                page::assign('count', $tree->getCount());
                $data = page::parse($TEMPLATE['frame']);
            }

            // Записываем в кэш
            cache::set($key, $data);
        }

        return $data;
    }

    /**
	* @return HTML
    * @param int $comment_id - ID комментария
	* @param string $templ_name - Шаблон оформления комментария
	* @desc МАКРОС: Выводит информацию об указанном комментарии
	*/
    public function view($comment_id, $templ_name = 'tree') {

        $templ_file = '/comments/'.$templ_name.'.tpl';
	    $TEMPLATE = page::getTemplate($templ_file);

		if (!is_array($TEMPLATE))
		    return page::errorNotFound('comments.view', $templ_file);

        if ($comment = comments::get($comment_id))
            if ($comment->isActive() || reg::getKey('/comments/show_noactive')) {

                page::assign('obj.id', $comment->id());
                page::assign('obj.parent_id', $comment->getParentId());
                page::assign('obj.user_id', $comment->getUserId());

                page::assign('obj.publ_date', $comment->getPublDate());
                page::assign('obj.username', $comment->getUserName());
                page::assign('obj.email', $comment->getEmail());
                page::assign('obj.text', $comment->getText());
                page::assign('obj.rate', $comment->getRate());

                if (isset($_SESSION['comments_rate'][$comment->id()]))
                    page::fParse('rate', $TEMPLATE['rate_change']);
                else
                    page::fParse('rate', $TEMPLATE['rate']);

                page::assign('list', '');

                return page::parse($TEMPLATE['list']);
            }
    }
}

?>