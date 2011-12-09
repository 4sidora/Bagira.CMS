<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

    Класс для работы с Memcache. Наследник класса defCache.

*/
 
class mcCache extends defCache {

    private $servers= array(
        array(
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 1,
        )
    /*  ,
        array(
            'host' => 'server2',
            'port' => 11211,
            'weight' => 40,
        )*/
    );

    private $cache;


    public function __construct($servers = '') {

		if(!empty($servers))
			$this->servers = $servers;

        $this->cache = new Memcache();

        if (count($this->servers) > 0)
            foreach($this->servers as $server)
                $this->cache->addServer($server['host'], $server['port'], true, $server['weight']);
        
	}

    protected function getValue($key) {

        return $this->cache->get($key);
    }

    protected function setValue($key, $value, $ttl) {

		return $this->cache->set($key, $value, 0, $ttl);
	}

    protected function addValue($key, $value, $ttl){

        return $this->cache->add($key, $value, $ttl);
	}

	protected function deleteValue($key) {

		return $this->cache->delete($key, 0);
	}

	protected function flushValues() {

        return $this->cache->flush();
	}

}
