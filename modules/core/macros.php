<?php

class coreMacros {

    /**
	* @return stirng - Путь до изображения
	* @param string $file_name - Исходное изображение
	* @param CONST $scale_type - Способ масштабирования рисунка, одна из трех констант
				stRateably	-	Масштабирование с учетом пропорций, относительно $width или $height
				stSquare    - 	Обрезать по квадрату со стороной $width
				stInSquare  - 	Вписать в квадрат со стороной $width
	* @param int $width - Ширина конечного изображения, если == 0 не учитывается
	* @param int $height - Высота конечного изображения, если == 0 не учитывается
	* @param string $watermark - Способ наложения водяного знака. Одно из нескольких значений:
				0 		- 	Водяной знак не накладывается
				1-9 	-	Водяной знак накладывается в одну из 9 позиций квадрата (см. документацию)
	* @desc МАКРОС: При необходимости масштабирует изображение под заданные параметры и
					возвращает путь до кешированного файла.
	*/
 	public function resize($file_name, $scale_type, $width = 0, $height = 0, $watermark = 0) {

            if (!empty($file_name)) {

	            $scale = (!is_numeric($scale_type)) ? constant($scale_type) : $scale_type;

				$dir = '/cache/img/'.$scale_type.'_'.$width.'x'.$height.'_'.$watermark;
	            $new_file = $dir.'/'.system::fileName($file_name);

				if (!file_exists(ROOT_DIR.$new_file)) {

	                if (!is_dir(ROOT_DIR.$dir)) @mkdir(ROOT_DIR.$dir, 0777);

					$img = new resizer($file_name, $scale, $width, $height);

					if (is_numeric($watermark) && $watermark > 0)
						$img->setWatermark(reg::getKey('/core/watermark'), $watermark);

					$img->save(ROOT_DIR.$new_file);
	            }

	            if (file_exists(ROOT_DIR.$new_file))
	            	return $new_file;
            }
 	}


    /**
	* @return stirng - дата и время в указанном формате.
	* @param string $format - Формат вывода, по аналогии с PHP-функцией date()
	* @param string $time - Дата и Время в формате TIMESTAMP или текстовом. Если 0, используется текущее время.
	* @desc МАКРОС: Выводит указанную дату и время в заданном формате, аналог PHP-функции date()
	*/
 	function fdate($format = 'd.m.Y', $time = 0) {
	  	if (is_string($time) && !is_numeric($time))
	  		$time = strtotime($time);
	  	return date($format, $time);
	}

    /**
	* @return stirng - Название месяца.
	* @param string $time - Дата и Время в формате TIMESTAMP или текстовом. Если 0, используется текущее время.
    * @param Int $type - Формат вывода названия месяца от 1 до 3
	* @desc МАКРОС: Вернет название месяца на русском языке
	*/
	function rus_mounth($time = 0, $type = 3) {

		if (is_string($time) && !is_numeric($time))
	  		$time = strtotime($time);

        $months = lang::get('MOUNTH', $type);

	  	return $months[date("m", $time)];
	}

    /**
	* @return stirng - Название дня недели.
	* @param string $time - Дата и Время в формате TIMESTAMP или текстовом. Если 0, используется текущее время.
    * @param Int $type - Формат вывода названия дня недели от 1 до 3
	* @desc МАКРОС: Вернет название дня недели на русском языке
	*/
    function rus_weekday($time = 0, $type = 1) {

		if (is_string($time) && !is_numeric($time))
	  		$time = strtotime($time);

        $months = lang::get('DAY', $type);

	  	return $months[date("N", $time)];
	}

    /**
	* @return string
	* @param string $file_name - Путь к файлу
	* @desc МАКРОС: Вернет расширение указанного файла
	*/
	function fileExt($file_name) {
	  	return system::fileExt($file_name);
	}

    /**
	* @return string
	* @param string $file_name - Путь к файлу
	* @desc МАКРОС: Вернет размер в килобайтах для указанного файла
	*/
    function fileSize($file_name) {
	  	if (file_exists(ROOT_DIR.$file_name))
	  		return ceil(filesize(ROOT_DIR.$file_name) / 10240) / 100;
	 	else
	 		return 0;
	}

    /**
	* @return string
	* @param string $url - URL для обработки
	* @desc МАКРОС: Обрезает указанный урл на один уровень с конца, используется для формирования ссылки назад
	*/
	function preUrl($url) {
	  	return system::preUrl($url);
	}

    /**
	* @return string
	* @param string $num - Номер части урла
	* @desc МАКРОС: Вернет указанную часть текущего урла страницы
	*/
    function url($num) {
	  	return system::url($num);
	}

	/**
	* @param string $url - Ссылка на любой ресурс.
	* @desc МАКРОС: Делает редирект на указанный URL.
	*/
 	public function redirect($url) {
		system::redirect($url);
 	}


    /**
	* @return stirng - Контент
	* @param string $templ_name - имя шаблона
	* @desc МАКРОС: Возвращает пропарсенный шаблон из папки /template/structure
	*/
	function include_templ($templ_name) {

		$site_prefix = (domains::curId() == 1 && languages::curId() == 1) ? '' : '/__'.str_replace('.', '_', domains::curDomain()->getName()).'_'.languages::curPrefix();
		$file = TEMPL_DIR.$site_prefix.'/structure/'.$templ_name.'.tpl';

		if (!file_exists($file))
		     return str_replace('%name%', $templ_name, 'Указанный шаблон (%name%.tpl) не найден!');
		else {

		     $file_tpl = implode('', file($file));
		     return page::parse($file_tpl);

		}
	}

    /**
	* @return HTML
	* @param int(string) $section - ID объекта, подразделы которой будут выводиться в списке
						 или системное имя класса, объекты которого нужно вывести
	* @param string $templ_name - Шаблон оформления по которому будет строится список
	* @param int $max_count - Максимальное количество элементов в списке
	* @param string $order_by - Способ сортировки элементов списка. SQL-подобный синтаксис, например: "name DESC".
	* @param int $start_pos - Номер элемента по порядку с которого будет выводиться список.
	* @desc МАКРОС: Выводит список объектов.
	*/
 	public function objList($section, $TEMPLATE = 'default', $max_count = 0, $order_by = 0, $start_pos = 0) {

        $list = '';

        // Определяем источник данных: ID, имя класса, путь, объект ormPage
        $independent = (is_a($section, 'ormObject')) ? false : true;
        $class_name = $class_frame = '';

        if ($independent) {

            if (!is_numeric($section)) {
                $pos = strpos($section, ' ');
                if ($pos) {
                    $class_name = substr($section, $pos + 1);
                    $section = substr($section, 0, $pos);
                } else {
                    $class_name = $section;
                    $section = -1;
                }
            }
        }

        // Если нужно, подгружаем файл шаблона
        if (!is_array($TEMPLATE)) {
	        $templ_file = '/core/objects/'.$TEMPLATE.'.tpl';
	        $TEMPLATE = page::getTemplate($templ_file);

		    if (!is_array($TEMPLATE))
		    	return page::errorNotFound('core.objList', $templ_file);
        }

        // Формируем выборку объектов
	    $sel = new ormSelect($class_name);

	    if ($section >= 0) {
            page::assign('parent_id', $section);
            $sel->where('parents', '=', $section);
	 	}

        // Сортировка списка
        if (!empty($order_by)) {
            $pos = strpos($order_by, ' ');
            if ($pos) {
            	$parram = substr($order_by, $pos + 1);
            	$order_by = substr($order_by, 0, $pos);
            } else $parram = '';
        	$sel->orderBy($order_by, $parram);
        } else $sel->orderBy(position, asc);

        $class_list = $sel->getClassesList();

        if (!empty($class_list)) {

	        // Узнаем какие поля объектов будут участвовать в выборке
	        $fields_str = '';
	        $fields = page::getFields('obj', $TEMPLATE, $class_list, $class_frame);
	        if (isset($fields['obj']))
		        while(list($key, $val) = each($fields['obj']))
		        	if ($val != 'url' && $val != 'class' && $val != 'num')
		        		$fields_str .= (empty($fields_str)) ? $val : ', '.$val;
	        $sel->fields($fields_str);

            // Количество элементов и постраничная навигация
            if (!empty($max_count))
		        if (isset($fields['funct']) && in_array('structure.navigation', $fields['funct'])) {
			        $count_page = ceil($sel->getCount() / $max_count);
			        page::assign('count_page', $count_page);
			        if(system::getCurrentNavNum() != 0) {
			        	$niz = (empty($start_pos)) ? system::getCurrentNavNum() * $max_count - $max_count : $start_pos;
			        	$sel->limit($niz, $max_count);
			       	} else $sel->limit($max_count);
	            } else if (!empty($start_pos)) {
                	$sel->limit($start_pos, $max_count);
	            } else $sel->limit($max_count);

            // Формируем список
	        while($obj = $sel->getObject()) {

	            // Парсим поля страницы
		        if (isset($fields['obj_all'])) {
		            reset($fields['obj_all']);
		        	while(list($num, $name) = each($fields['obj_all']))
		            	page::assign('obj.'.$name, $obj->__get($name));
	            }

	            $class = $obj->getClass()->getSName();
	            $num = $sel->getObjectNum() + 1;

                page::assign('obj.num', $num);
                page::assign('obj.class', $class);
	            page::assign('class-first', ($num == 1) ? 'first' : '');
	            page::assign('class-last', ($num == $sel->getObjectCount()) ? 'last' : '');
	            page::assign('class-odd', ($num % 2 == 0) ? 'odd' : '');
	            page::assign('class-even', ($num % 2 != 0) ? 'even' : '');
                page::assign('class-third', ($num % 3 == 0) ? 'third' : '');

                if ($num === 1)
                	page::assign('first_children_id', $obj->id);
                page::assign('last_children_id', $obj->id);

	            if (isset($TEMPLATE['list_'.$class]))
	            	$templ = 'list_'.$class;
	            else if (isset($TEMPLATE['list']))
            		$templ = 'list';
                else $templ = '';

	            if (isset($TEMPLATE[$templ])) {
					if ($num > 1 && isset($TEMPLATE['separator']))
						$list .= $TEMPLATE['separator'];
				    $list .= page::parse($TEMPLATE[$templ]);
	            }
	    	}
    	}

    	if (!empty($list) && $independent) {
            page::assign('list', $list);
            if (isset($TEMPLATE['frame_list']))
        		$list = page::parse($TEMPLATE['frame_list']);
        	else
        		$list = page::errorBlock('core.objList', $templ_file, 'frame_list');
    	}

    	return $list;
 	}

}

?>