<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Вспомогательный класс, содержит в себе методы обработки значений различных типов.
*/

abstract class uiTableFunctions {

    /**
 	* @return Результат выполнения
  	* @param string $value - Значение для обработки
  	* @param ormObject $obj - Экзепляр ormObject или строка ассоциативного массива
  	* @param string $function - PHP-функция которая будет использоваться для обработки значений
  	* @desc Вызывает php-функцию для обработки значения
 	*/
	protected function exeFunction($value, $obj, $function) {

    	if ($function == 'count') {

       		// Просто количество
         	$value = count($value);

       	} else if (method_exists($this, $function)) {

       	    // Вызываем встроенные методы класса
   			$value = call_user_func(array($this, $function), $value, $obj);

   		}else if (function_exists($function)) {

   		    // Вызываем пользовательскую функцию
       		$value = call_user_func_array($function, array($value, $obj));

        } else $value = lang::get('FUNCT_NOT_FOUND').$function.'()';

        return $value;
	}

    /**
 	* @return Результат выполнения
  	* @param ormObject $obj - Экзепляр ormObject
  	* @param string $field - Имя обрабатываемого поля
  	* @param integer $field_type - Тип ORM-поля
  	* @param string $value - Значение для обработки
  	* @param string $function - PHP-функция для обработки значения, если есть
  	* @desc Обрабатываем значение
 	*/
    protected function processValue($obj, $field, $field_type, $value, $function) {

        // Получаем данные класса
        if ($field == 'class_id') {
			$value = $obj->getClass()->id();
        }  elseif ($field == 'class') {
			$value = $obj->getClass()->getSName();
        }  elseif ($field == 'class_name') {
			$value = $obj->getClass()->getName();
        }

        // Обрабатываем значение
    	if (!empty($function)) {

        	$value = $this->exeFunction($value, $obj, $function);

       	} else if ($field == 'parents') {

			// Получаем список родителей
			$value = $this->viewParents($value, $obj);

        }  else if ($field == 'children') {

            // Получаем список подобъектов
			$value = $this->viewChildren($value, $obj);

        } else if (!empty($field_type)) {

        	if ($field_type == 20) {
         		$value = $this->viewUrl($value, $obj);
         	} else if ($field_type == 25) {
         		$value = $this->viewDate($value, $obj);
         	} else if ($field_type == 30) {
         		$value = $this->viewTime($value, $obj);
         	} else if ($field_type == 32) {
         		$value = $this->viewDateTime($value, $obj);
         	} else if ($field_type == 50) {
         		$value = $this->viewBool($value, $obj);
         	} else if ($field_type == 70 || $field_type == 80 || $field_type == 85) {
         		$value = $this->viewFile($value, $obj);
         	} else if ($field_type == 73) {
         		$value = $this->viewFiles($value, $obj);
         	} else if ($field_type == 75) {
         		$value = $this->viewImage($value, $obj);
         	} else if ($field_type == 90) {
         		$value = $this->viewList($value, $obj);
         	} else if ($field_type == 95) {
         		$value = $this->viewListMore($value, $obj);
         	}
        }

        return $value;
    }

	// -	-	-	-	Функции отображения различных типов данных 	- 		-		-		-		-		-		-		-


    //
    protected function notNull($val, $obj) {

    	if (empty($val))
            return '';

    	return $val;
    }

    // Тип данных URL
    protected function cutText($val, $obj) {

    	$len = 300;

    	if (strlen($val) > $len)
            $val = substr($val, 0, $len).'...';

    	return $val;
    }

    // Тип данных URL
    protected function viewUrl($val, $obj) {
    	if (!empty($val))
    		return '<a href="'.$val.'" target="_blank">'.$val.'</a>';
    }

    // Тип данных Дата и Время
    protected function viewDateTime($val, $obj) {
    	if ($val == '0000-00-00 00:00:00')
        	return '?';
        else
        	return date('d.m.Y H:i', strtotime($val));
    }

    protected function viewDateTime2($val, $obj) {
    	if ($val == '0000-00-00 00:00:00')
        	return '?';
        else
        	return date('d.m.Y H:i:s', strtotime($val));
    }

    // Тип данных Дата
    protected function viewDate($val, $obj) {
    	if ($val == '0000-00-00' || $val == '0000-00-00 00:00:00')
        	return '?';
        else
        	return date('d.m.Y', strtotime($val));
    }

    // Тип данных Время
    protected function viewTime($val, $obj) {
    	if ($val == '00:00:00' || $val == '0000-00-00' || $val == '0000-00-00 00:00:00')
        	return '?';
        else
        	return date('H:i:s', strtotime($val));
    }

    // Тип данных Флажек
    protected function viewBool($val, $obj) {
    	return ($val) ? lang::get('TABLE_YES') : lang::get('TABLE_NO');
    }

    // Тип данных Файл, Видео, Флеш
    protected function viewFile($val, $obj) {
    	if (!empty($val))
    		return '<a href="'.$val.'" target="_blank">'.$val.'</a>';
    }

    // Тип данных Список файлов
    protected function viewFiles($val, $obj) {
    	$files = '';

    	if (!empty($val)) {
	        $vals = explode(';', $val);
	        while (list($key, $val) = each($vals))
	        	if (file_exists(ROOT_DIR.$val)){
		        	$pre = (empty($key)) ? '' : '<br />';
		            $files .= $pre.'<a href="'.$val.'" target="_blank">'.$val.'</a>';
		        }
        }

    	return $files;
    }

    // Тип данных Изображение
    protected function viewImage($val, $obj) {
    	if (!empty($val) && file_exists(ROOT_DIR.$val)) {
    		if ($this->style == table) {
    			return '<a href="#" onclick="$.prettyPhoto.open(\''.$val.'\');">
    				<img src="/css_mpanel/images/lupa.gif" width="16" height="16" border="0"></a>';
    		}
    	}
    }

    // Тип данных Список
    protected function viewList($val, $obj) {
    	if (!empty($val))
    		return ormObjects::get($val)->name;
    }

    // Тип данных Множ. Список
    protected function viewListMore($val, $obj) {
    	if (!empty($val)) {
    	    $names = '';
    		while (list($key, $id) = each($val)) {
		        $pre = (empty($key)) ? '' : ', ';
		        $names .= $pre.ormObjects::get($id)->name;
		    }
    		return $names;
    	}
    }


    // Выводит список родителей
    protected function viewParents($val, $obj) {
    	if (!empty($val)) {
    	    $names = '';
    		while (list($key, $obj) = each($val)) {
		        $pre = (empty($names)) ? '' : ', ';
		        $names .= $pre.$obj['parent_name'];
		    }
    		return $names;
    	}
    }

    // выводит список подобъектов
    protected function viewChildren($val, $obj) {
    	if (!empty($val)) {
    	    $names = '';
    		while (list($key, $id) = each($val)) {
		        $pre = (empty($key)) ? '' : ', ';
		        $names .= $pre.ormObjects::get($id)->name;
		    }
    		return $names;
    	}
    }


}

?>