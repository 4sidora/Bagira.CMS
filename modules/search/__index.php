<?php

class __index {

    // �������� �������� ������
	public function defAction() {

        if (file_exists(MODUL_DIR.'/search/template/forms.tpl')) {
        	include(MODUL_DIR.'/search/template/forms.tpl');

	        ui::newButton(lang::get('SEARCH_BTN_INDEX'), "javascript:startIndex();");
	        ui::newButton(lang::get('SEARCH_BTN_CLEAR'), "javascript:sendForm('clear');");

	        page::assign('count_page', searchIndex::getCountPages());
            page::assign('count_words', searchIndex::getCountWords());

            $d = reg::getKey(ormPages::getPrefix('search').'/index_date');
            if (empty($d)) $d = '-';
            page::assign('index_date', $d);

            page::assign('text1', lang::get('SEARCH_TEXT_1'));
            page::assign('text2', lang::get('SEARCH_TEXT_2'));
            page::assign('text3', lang::get('SEARCH_TEXT_3'));
            page::assign('text4', lang::get('SEARCH_TEXT_4'));

	        return page::parse($TEMPLATE['frame']);
        }
 	}

 	// ���������� ���������� ������� �����
	public function proc() {

        if (system::isAjax() && system::issetUrl(2)) {

        	if (system::url(2) == 'start') {

        		searchIndex::clear();
        		reg::setKey(ormPages::getPrefix('search').'/index_date', date('d.m.Y'));

        		$sel = new ormSelect();
        		$sel->fields('name');
        		$sel->findInPages();
        		$sel->where('active', '=', 1);
                $sel->where('in_search', '=', 1);

                echo $sel->getCount();

        	} else if (system::url(2) == 'info') {

            	$data = reg::getKey(ormPages::getPrefix('search').'/index_date');
            	if (empty($data)) $data = '-';

                system::json(
                    array(
                         'pages' => searchIndex::getCountPages(),
                         'words' => searchIndex::getCountWords(),
                         'data' => $data
                    )
                );   

        	} else {

        	    $sel = new ormSelect();
        		$sel->findInPages();
        		$sel->where('active', '=', 1);
                $sel->where('in_search', '=', 1);
                $sel->limit(system::url(2), 1);

                searchIndex::indexPage($sel->getObject());

                echo 'ok';
        	}

        	system::stop();
        }

        if (system::POST('parram') == 'clear')
        	searchIndex::clear();

        system::redirect('/search/index');
 	}

}

?>