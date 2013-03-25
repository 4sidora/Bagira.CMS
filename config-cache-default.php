<?php

    // ОБЩИЕ НАСТРОЙКИ КЭША

  	// Текущее состояние кэша: true - включен, false - выключен
    define('CACHE_ENABLE', false);

    // Текущий драйвер кэша: file - кэш на файлах, mc - memcache
	define('CACHE_DRIVER', 'file');

    // Время жизни кэша в секундах по умолчанию
    define('CACHE_DEFAULT_TTL', 0);

    // Способ генерации ключей. Если true, уникальность ключа кеша зависит от текущего домена.
    // Для мультидоменных сайтов всегда указывайте в значении true.
    define('CACHE_BY_DOMAIN', false);



    // НАСТРОЙКИ ДРАЙВЕРОВ

    // Настройки файлового кэша
	define('CACHE_FILE_PATH', CACHE_DIR.'/files');
    define('CACHE_FILE_EXT', '.tmp');
    define('CACHE_FILE_DIR_LEVEL', 0);


    // Настройки для Memcache находятся в классе /modules/core/cache/mcCache.php
     






?>