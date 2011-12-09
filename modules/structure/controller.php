<?php

class controller {

	public function defAction() {

        $content = '';

        if (!system::issetUrl(0) || system::url(0) == 'structure') {

            // Параметров нет, загружаем домашнюю страницу
        	$content = $this->parsePageContent(ormPages::getHomePage());

        } else {

            // Загружаем обычную страницу
        	$page_id = ormPages::getPageIdByUrl(system::getCurrentUrl());

        	if (!empty($page_id)) {

            	$content = $this->parsePageContent(ormPages::get($page_id));

        	}
 		}

		return $content;
 	}

 	private function parsePageContent($page) {

		if (is_a($page, 'ormPage') && $page->active) {

            page::assign('page_id', $page->id);

            $pages = ormPages::getActiveId();
            while (list($num, $id) = each($pages))
            	page::assign('page_id'.$num, $id);

            page::globalVar('h1', $page->h1);
         	page::globalVar('title', $page->title);

         	if ($page->keywords)
         		page::globalVar('keywords', $page->keywords);

         	if ($page->description)
         		page::globalVar('description', $page->description);

         	if ($page->template2_id != 0)
            	$content = page::macros('structure')->objView($page->id);
         	else
         		$content = $page->content;

            $templ_name = ($templ = templates::get($page->template_id)) ? $templ->getFile() : 'default';

            system::$defTemplate = '/structure/'.$templ_name.'.tpl';

         	if (empty($content))
         		$content = '&nbsp;';

         	return $content;
		}
 	}

 	// Вывод карты сайта
	public function mapAction() {
		page::globalVar('h1', lang::get('SITE_MAP'));
        page::globalVar('title', lang::get('SITE_MAP'));
		return page::macros('structure')->menu('map');
 	}


    public function link_counterAction() {

        if ($obj = ormPages::get(system::url(2))) {

            $value = $obj->__get(system::url(3));

            if (!empty($value) && file_exists(ROOT_DIR.$value)) {

                // Увеличиваем счетчик скачиваний
                if (!isset($_SESSION['counter'.md5(system::url(2).system::url(3))])) {

                    $field_counter = system::url(3).'_download';
                    $obj->__set($field_counter, $obj->__get($field_counter) + 1);
                    $obj->save();

                    $_SESSION['counter'.md5(system::url(2).system::url(3))] = 1;
                }

                system::redirect($value);
            }
        }

        return ormPages::get404();
    }

    // Изменение рейтинга (звездочки) для любых страниц системы имеющих поле "rate" && !isset($_SESSION['change_rate'][$page->id])
    public function change_rateAction() {

        if ($page = ormPages::get(system::url(2))){

            if (!isset($_SESSION['change_rate'][$page->id]) && $page->getClass()->issetField('rate')) {

                // Высчитываем новый рейтинг
                $rate = system::checkVar(system::url(3), isInt);
                if ($rate > 5) $rate = 5; else if ($rate < 1) $rate = 1;
                $new_rate = ($page->rate != 0) ? ($page->rate + $rate) / 2 : $rate;

                // Сохраняем
                $page->rate = $new_rate;
                $page->save();

                $_SESSION['change_rate'][$page->id] = 1;
                
                echo json_encode(array('error' => 0, 'new_rate' => $new_rate));
                system::stop();
            }
        }

        echo json_encode(array('error' => 1, 'new_rate' => 0));
        system::stop();
    }


    // Вывод RSS
	public function rssAction() {

        if (!system::issetUrl(2)) {

            page::globalVar('h1', lang::get('RSS_TITLE'));
            page::globalVar('title', lang::get('RSS_TITLE'));

            return page::macros('structure')->rssList();

        } else {

            header("content-type: text/xml; charset=UTF-8");
            echo page::macros('structure')->rss(system::url(2));
            system::stop();
        }
 	}

}

?>