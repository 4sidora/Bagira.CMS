<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

    Класс для работы с файловым кешем. Наследник класса defCache.

*/
 
class fileCache extends defCache {

    private $cachePath, $cacheFileExt, $cacheDirLevel;

    private $cleaned = false;       //  Если true автоматическое удаление устаревшиих файлов будет отключена.
    private $probability = 0.01;    //  Вероятность автоматического запуска сборщика мусора в процентах



    public function __construct($path = '', $ext = '', $dir_level = 0) {

        // Устанавливаем папку для хранения кеша
        $this->cachePath = $path;

		if(empty($this->cachePath))
			$this->cachePath = CACHE_FILE_PATH;

		if(!is_dir($this->cachePath))
			mkdir($this->cachePath, 0777, true);


        // Устанавливаем расширение файлов
        $this->cacheFileExt = $ext;

        if (empty($this->cacheFileExt))
            $this->cacheFileExt = CACHE_FILE_EXT;

        // Устанавливаем количество уровней вложенности для категорий кэша
        $this->cacheDirLevel = $dir_level;

        if (empty($this->cacheDirLevel))
            $this->cacheDirLevel = CACHE_FILE_DIR_LEVEL;

	}

    protected function getFileName($key) {
        
		if($this->cacheDirLevel > 0) {

			$base = $this->cachePath;

			for($i = 0; $i < $this->cacheDirLevel; $i++) {

				$pre = substr($key, $i+$i, 2);

                if($pre !== false)
					$base .= '/'.$pre;
			}

			return $base.'/'.$key.$this->cacheFileExt;

		} else
			return $this->cachePath.'/'.$key.$this->cacheFileExt;
	}

    protected function getValue($key) {

        $filename = $this->getFileName($key);
        $time = @filemtime($filename);

		if($time > time())
			return file_get_contents($filename);

        if($time > 0)
			@unlink($filename);

		return false;
    }

    protected function setValue($key, $value, $ttl) {

        // Удаляем устаревшие файлы
        if(!$this->cleaned && mt_rand(0, 1000000) < $this->probability * 1000000) {
			$this->clearDir();
			$this->cleaned = true;
		}

		$filename = $this->getFileName($key);
        
		if($this->cacheDirLevel > 0)
			@mkdir(dirname($filename), 0777, true);

		if(@file_put_contents($filename, $value, LOCK_EX) !== false) {

            if($ttl <= 0)
		        $ttl = 31536000;

		    $ttl += time();
            
			@chmod($filename, 0777);
            
			return @touch($filename, $ttl);

		} else
			return false;
	}

    protected function addValue($key, $value, $ttl){

        $filename = $this->getFileName($key);

		if(@filemtime($filename) > time())
			return false;

		return $this->setValue($key, $value, $ttl);
	}

	protected function deleteValue($key) {

		return @unlink($this->getFileName($key));
	}

	protected function flushValues() {
		return $this->clearDir(false);
	}

    protected function clearDir($expiredOnly = true, $path = 0) {

        if(empty($path))
			$path = $this->cachePath;

		if(($handle = opendir($path))===false)
			return;

		while(($file=readdir($handle))!==false) {

            if($file[0]==='.')
				continue;

			$fullPath = $path.'/'.$file;

			if(is_dir($fullPath)) {
                
				$this->clearDir($expiredOnly, $fullPath);

            } else if($expiredOnly && @filemtime($fullPath) < time() || !$expiredOnly) {

				@unlink($fullPath);

            }
		}
        
		closedir($handle);
	}

}
