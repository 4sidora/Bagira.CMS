<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	Класс для модификации изображений: изменение размеров, накладывание водяного знака.
*/

class resizer {

    private $original_image = '';   		// Оригинальный файл рисунка
    private $scale_type = stRateably;       // Способ масштабирования
    private $quality = '90';				// Качество для jpeg-файлов в процентах
    private $width, $height;				// Ширина, высота рисунка
    private $watermark = array();   		// Оригинальный файл рисунка

    /**
	* @return null
	* @param string $file_name -  Путь к файлу изображения
	* @param const $scale_type - Способ масштабирования рисунка, используйте одну из констант:
			stRateably	-	Пропорциональное масштабирование, учитывается либо $width или $height
			stSquare	-	Обрезать по квадрату со стороной $width
			stInSquare  -	Вписать в квадрат со стороной $width
	* @param integer $width - Требуемая ширина рисунка, если 0 - не учитывается
	* @param integer $height - Требуемая высота рисунка, если 0 - не учитывается
	* @param integer $quality - Качество для jpg-изображений, значение от 1 до 100
	* @desc Конструктор класса. Здесь указываются основные параметры создаваемого изображения.
	*/
    function __construct($file_name, $scale_type, $width = 0, $height = 0, $quality = 90){
    	$this->original_image = ROOT_DIR.$file_name;
    	$this->scale_type = $scale_type;
    	$this->width = $width;
    	$this->height = $height;
    	$this->quality = $quality;
    }

    /**
	* @return null
	* @param string $file - PNG-файл с полупрозрачным рисунком
	* @param integer $position - Позиция вставки водяного знака, значение от 1 до 9
	* @desc Накладывает водяной знак на изображение
	*/
    function setWatermark($file, $position = 5){
    	$this->watermark = array(
    		'file' => $file,
    		'position' => $position
    	);
    }

    //
    function setText($text, $x = 0, $y = 0){

    }

    // Сохраняет измененное изображение в указанный файл
    function save($file_name){
        $this->imageResize($file_name);
    }

    // Выводит измененное изображение в браузер
    function toScreen(){
    	$this->imageResize('screen');
    	system::stop();
    }

    // Собственно, функция которая все делает
	private function imageResize($new_file_name) {

		if (!empty($this->original_image) && file_exists($this->original_image))  {

	        switch (substr($this->original_image, -4)) {
	        	case '.gif': $type = 1; break;
	            case '.jpg': $type = 2; break;
	            case 'jpeg': $type = 2; break;
	            case '.png': $type = 3; break;
	        }

	        switch ($type) {
		        case 1: $src = @imagecreatefromgif($this->original_image); break;
	            case 2: $src = @imagecreatefromjpeg($this->original_image); break;
	            case 3: $src = @imagecreatefrompng($this->original_image);break;
	        }

	        if (empty($src))
	        	die('Can not read file "'.$this->original_image.'"');

	        $w_src = imagesx($src);
	        $h_src = imagesy($src);
	        $process = true;

            // Масштабируем только большие рисунки
	        if ((((!empty($this->width) && $this->width > $w_src) || empty($this->width)) &&
	        	((!empty($this->height) && $this->height > $h_src) || empty($this->height)))){

            	$dest = $src;
            	$process = false;
            	$w_dest = $w_src;
	            $h_dest = $h_src;

            // пропорциональное уменьшение рисунка либо по высоте, либо по ширине
			} else if ($this->scale_type == stRateably && (!empty($this->width) || !empty($this->height))) {

                 if (!empty($this->width) && !empty($this->height)) {

                    // Масштабируем по двум размерам сразу (width и height)
                    $tmp = round($h_src /($w_src / $this->width));
                    $tmp2 = round($w_src /($h_src / $this->height));



                    if ($w_src < $h_src)

                    	if ($tmp >= $this->height)
	                    	$ratio = $w_src / $this->width;
	                    else
	                    	$ratio = $h_src / $this->height;

                    else if ($w_src > $h_src)

                    	if ($tmp2 >= $this->width)
	                    	$ratio = $h_src / $this->height;
	                    else
	                    	$ratio = $w_src / $this->width;
                        
                     else $ratio = 1;


                    $w_dest = round($w_src / $ratio);
		            $h_dest = round($h_src / $ratio);

                    $dest_tmp = @imagecreatetruecolor($w_dest, $h_dest) or die("Cannot Initialize new GD image stream 11");
	                $white = imagecolorallocate($dest_tmp, 255, 255, 255);
		            imagefill($dest_tmp, 1, 1, $white);
		            imagecopyresampled($dest_tmp, $src, 0, 0, 0, 0, $w_dest, $h_dest, $w_src, $h_src);

                    $dest = @imagecreatetruecolor($this->width, $this->height) or die("Cannot Initialize new GD image stream 11");

                    imagecopyresampled($dest, $dest_tmp, 0, 0, round(($w_dest - $this->width) / 2), round(($h_dest - $this->height) / 2), $this->width, $this->height, $this->width, $this->height);

                    imagedestroy($dest_tmp);

                } else {

                    // Масштабируем только по одному из размеров
	                if (!empty($this->width))
			            $ratio = $w_src / $this->width;
		         	else if (!empty($this->height))
		         		$ratio = $h_src / $this->height;

		         	$w_dest = round($w_src / $ratio);
		            $h_dest = round($h_src / $ratio);

		            // создаём пустую картинку
		            $dest = @imagecreatetruecolor($w_dest, $h_dest) or die("Cannot Initialize new GD image stream 11");

	              //  imagealphablending($dest, false);
	              //  imagesavealpha($dest, true);
	                $white = imagecolorallocate($dest, 255, 255, 255);
	                //imagecolortransparent($dest, $white);
		            imagefill($dest, 1, 1, $white);
	               // $this->setTransparency($dest, $src);

		            imagecopyresampled($dest, $src, 0, 0, 0, 0, $w_dest, $h_dest, $w_src, $h_src);
	            }

			// получения прямоугольного файла вписанного в квадрат со стороной $this->width
	        } else if ($this->scale_type == stInSquare && !empty($this->width)) {

		        $delitel = ($w_src > $h_src) ? $w_src : $h_src;

	            $ratio = $delitel / $this->width;
	            $w_dest = round($w_src / $ratio);
	            $h_dest = round($h_src / $ratio);

	            // создаём пустую картинку
	            $dest = @imagecreatetruecolor($w_dest, $h_dest) or die("Cannot Initialize new GD image stream 11");
	            imagealphablending($dest, false);
	            /*$white = imagecolorallocate($dest, 255, 255, 255);
	            imagefill($dest, 1, 1, $white);*/
	            imagecopyresampled($dest, $src, 0, 0, 0, 0, $w_dest, $h_dest, $w_src, $h_src);

	        // обрезка фото по квадрату со стороной $this->width
	        } else if ($this->scale_type == stSquare && !empty($this->width)) {

	            // создаём пустую квадратную картинку
	            $dest = @imagecreatetruecolor($this->width, $this->width) or die("Cannot Initialize new GD image stream 12");
	            imagealphablending($dest, false);

	            // вырезаем квадратную серединку по x, если фото горизонтальное
	            if ($w_src > $h_src)
	                imagecopyresampled($dest, $src, 0, 0, round((max($w_src, $h_src)-min($w_src, $h_src))/2), 0, $this->width, $this->width, min($w_src, $h_src), min($w_src, $h_src));

	            // вырезаем квадратную верхушку по y, если фото вертикальное (хотя можно тоже середику)
	            if ($w_src < $h_src)
	                imagecopyresampled($dest, $src, 0, 0, 0, round((max($w_src, $h_src)-min($w_src, $h_src))/2), $this->width, $this->width, min($w_src, $h_src), min($w_src, $h_src));

	            // квадратная картинка масштабируется без вырезок
	            if ($w_src == $h_src)
	                imagecopyresampled($dest, $src, 0, 0, 0, 0, $this->width, $this->width, $w_src, $w_src);

	        }


            // Наложение водяного знака
	        if (!empty($this->watermark) && file_exists(ROOT_DIR.$this->watermark['file']) && system::fileExtIs($this->watermark['file'], array('png'))) {

	        	$logo = imagecreatefrompng(ROOT_DIR.$this->watermark['file']);
                imagealphablending($logo, false);
                imagesavealpha($logo, true);
                $logo_w = imagesx($logo);
				$logo_h = imagesy($logo);
                imagealphablending($dest, true);

                switch ($this->watermark['position']) {
		        	case 1:imagecopy($dest, $logo, 10, 10, 0, 0, $logo_w, $logo_h); break;
		        	case 2:imagecopy($dest, $logo, $w_dest/2-$logo_w/2, 10, 0, 0, $logo_w, $logo_h); break;
		        	case 3:imagecopy($dest, $logo, $w_dest-$logo_w - 10, 10, 0, 0, $logo_w, $logo_h); break;
		        	case 4:imagecopy($dest, $logo, 10, $h_dest/2-$logo_h/2, 0, 0, $logo_w, $logo_h); break;
		        	case 5:imagecopy($dest, $logo, $w_dest/2-$logo_w/2, $h_dest/2-$logo_h/2, 0, 0, $logo_w, $logo_h); break;
		        	case 6:imagecopy($dest, $logo, $w_dest-$logo_w - 10, $h_dest/2-$logo_h/2, 0, 0, $logo_w, $logo_h); break;
		        	case 7:imagecopy($dest, $logo, 10, $h_dest-$logo_h-10, 0, 0, $logo_w, $logo_h); break;
		        	case 8:imagecopy($dest, $logo, $w_dest/2-$logo_w/2, $h_dest-$logo_h-10, 0, 0, $logo_w, $logo_h); break;
		        	case 9:imagecopy($dest, $logo, $w_dest-$logo_w - 10, $h_dest-$logo_h-10, 0, 0, $logo_w, $logo_h); break;
		        }
            }

	        @imagegammacorrect($dest, 1, 1.1);
	        @imageinterlace($dest, 1);

            if ($new_file_name == 'screen')
		        switch ($type) {
		        	case 1:
		        		header("Content-type: image/gif");
		        		imagegif($dest);
		        	break;
		            case 2:
		            	header("Content-type: image/jpeg");
		            	imagejpeg($dest, '', $this->quality);
		            break;
		            case 3:
		            	header("Content-type: image/png");
		            	imagesavealpha($dest, true);
		            	imagepng($dest);
		            break;
		        }
		  	else
		  		switch ($type) {
		        	case 1: imagegif($dest, $new_file_name); break;
		            case 2: imagejpeg($dest, $new_file_name, $this->quality); break;
		            case 3:	imagepng($dest, $new_file_name); break;
		        }


	        imagedestroy($dest);

	        if ($process)
	        	imagedestroy($src);

		};
	}


    function setTransparency($new_image, $image_source) {
        $transparencyIndex = imagecolortransparent($image_source);
        echo $transparencyIndex.'<br /><br />';
        $transparencyColor = array('red' => 0, 'green' => 0, 'blue' => 0);

        if ($transparencyIndex >= 0)
            $transparencyColor = imagecolorsforindex($image_source, $transparencyIndex);

        $transparencyIndex = imagecolorallocate($new_image, $transparencyColor['red'], $transparencyColor['green'], $transparencyColor['blue']);
        imagefill($new_image, 0, 0, $transparencyIndex);
        imagecolortransparent($new_image, $transparencyIndex);
    }
}

?>