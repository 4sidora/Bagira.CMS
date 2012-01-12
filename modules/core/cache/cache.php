<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

    Статический класс для работы к кешем. Система использует этот класс по умолчанию.

    В зависимости от настроек находящихся в файле "/config-cache.php", используется один из
    драйверов (файловый или memcache).


*/
 
class cache {

    static private $class = null;


    static private function isInit(){

        if (!CACHE_ENABLE)
            return false;
        else if (self::$class === null) {

            eval('self::$class = new '.CACHE_DRIVER.'Cache();');

            return self::$class instanceof defCache;

        } else
            return true;
    }     

    /**
     * @return mixed
     * @param string $id - Индификатор значения
     * @desc Вернет значение которое находится в кеше
     */
    static function get($id) {

        if (self::isInit())
            return self::$class->get($id);
        else
            return false;
    }

    /**
     * @return boolean
     * @param string $id - Индификатор значения
     * @param mixed $value - Значение
     * @param int $ttl - Время жизни кеша
     * @desc Изменяет указанное значение в кеше
     */
    static function set($id, $value, $ttl = CACHE_DEFAULT_TTL) {

        if (self::isInit()) 
            return self::$class->set($id, $value, $ttl);
        else
            return false;

    }

    /**
     * @return boolean
     * @param string $id - Индификатор значения
     * @param mixed $value - Значение
     * @param int $ttl - Время жизни кеша
     * @desc Добавляет указанное значение в кеш
     */
    static function add($id, $value, $ttl = CACHE_DEFAULT_TTL) {

        if (self::isInit())
            return self::$class->add($id, $value, $ttl);
        else
            return false;

    }

    /**
     * @return boolean
     * @param string $id - Индификатор значения
     * @desc Удаляет указанное значение из кеша
     */
    static function delete($id) {

        if (self::isInit())
            return self::$class->delete($id);
        else
            return false;

    }

    /**
     * @return boolean
     * @desc Очищает все содержимое кеша
     */
    static function flush() {

        if (self::isInit())
            return self::$class->flush();
        else
            return false;

    }

    /**
     * @return boolean
     * @param string $id - Индификатор значения
     * @desc Проверяет существует ли указанное значение
     */
    static function exists($id) {

        if (self::isInit())
            return self::$class->exists($id);
        else
            return false;

    }

}
