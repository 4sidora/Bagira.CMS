<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

    Абстрактный класс для работы с кешем. Реализует единных интерфейс для работы различных драверов кэша.
*/
 
abstract class defCache {


    // Генирация уникального ключа в зависимости от текущего сайта
    private function getUniqueKey($key) {
        return md5($_SERVER['HTTP_HOST'].md5($key));
    }

    /**
     * @return mixed
     * @param string $id - Индификатор значения
     * @desc Вернет значение которое находится в кеше
     */
    function get($id) {

       
        $value = $this->getValue($this->getUniqueKey($id));

        if($value !== false)
			return unserialize($value);
	
		return false;
    }

    /**
     * @return boolean
     * @param string $id - Индификатор значения
     * @param mixed $value - Значение
     * @param int $ttl - Время жизни кеша
     * @desc Изменяет указанное значение в кеше
     */
    function set($id, $value, $ttl = 0) {
        
        return $this->setValue($this->getUniqueKey($id), serialize($value), $ttl);
    }

    /**
     * @return boolean
     * @param string $id - Индификатор значения
     * @param mixed $value - Значение
     * @param int $ttl - Время жизни кеша
     * @desc Добавляет указанное значение в кеш
     */
    function add($id, $value, $ttl = 0) {

        return $this->addValue($this->getUniqueKey($id), serialize($value), $ttl);
    }

    /**
     * @return boolean
     * @param string $id - Индификатор значения
     * @desc Удаляет указанное значение из кеша
     */
    function delete($id) {

        return $this->deleteValue($this->getUniqueKey($id));
    }

    /**
     * @return boolean
     * @desc Очищает все содержимое кеша
     */
    function flush() {

        return $this->flushValues();
    }

    /**
     * @return boolean
     * @param string $id - Индификатор значения
     * @desc Проверяет существует ли указанное значение
     */
    function exists($id) {

    }


    //  Методы для реализации доступа к кешу

    protected function getValue($id) {

    }

    protected function setValue($key, $value, $ttl) {

	}

    protected function addValue($key, $value, $ttl){

	}

	protected function deleteValue($key) {

	}

	protected function flushValues() {

	}
}
