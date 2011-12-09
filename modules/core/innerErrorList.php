<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Абстрактный класс для реализации механизма обработки ошибок
*/

abstract class innerErrorList {

	private $innerErrorList = array();

    /**
	* @return null
	* @param string $errorNum - Системный номер ошибки
	* @param string $errorText - Текст ошибки
	* @param string $errorField - Поле из за которого произошла ошибка
	* @param string $parram -
	* @desc Добавляет информацию о новой ошибке
	*/
    protected function newError($errorNum, $errorText, $errorField = '', $parram = ''){
    	$this->innerErrorList[$errorNum] = array(
    				'code' => $errorNum,
    				'text' => $errorText,
    				'field' => $errorField,
    				'parram' => $parram
    	);
    }

    // Вернет массив содержащий ошибки
    public function getErrorList(){
    	return $this->innerErrorList;
    }

    /**
	* @return string
	* @param string $sepor - Разделитель для ошибок
	* @desc Вернет тексты ошибок в виде строки
	*/
    public function getErrorListText($sepor = '<br /><br />'){
    	$tmp = '';
    	reset($this->innerErrorList);
	    while(list($key, $error) = each($this->innerErrorList))
            $tmp .= $error['text'].$sepor;
     	return $tmp;
    }

    // Вернет в виде массива информацию о поле которое вызвало ошибку
    public function getErrorFields(){

    	$fields = array('select' => '', 'focus' => '');
    	reset($this->innerErrorList);
	    while(list($key, $error) = each($this->innerErrorList))
	        if (!empty($error['field']))
	        	if (empty($fields['select'])) {
	            	$fields['select'] .= '#'.$error['field'];
	            	$fields['focus'] = $error['field'];
	            } else
	            	$fields['select'] .= ', #'.$error['field'];
        return $fields;
    }

    /**
	* @return int
	* @param int $error_code - Номер ошибки. Если указан, вернет количество ошибок именно с этим номером
	* @desc Возвращает количество ошибок
	*/
    public function issetErrors($error_code = 0){
    	if (empty($error_code))
    		return count($this->innerErrorList);
    	else
    		return isset($this->innerErrorList[$error_code]);

    }

}

?>