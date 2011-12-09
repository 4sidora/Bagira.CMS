<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

    Класс для получения чисел прописью и склонения числительных. Работает только с положительными числами. Класс является статическим.

    Порядок чисел не больше 999 миллионов. С миллиардами не работает.

    Алгоритм найден в Интернете, автор не известен...
*/

class ruNumbers {

    static private $N0 = 'ноль';

    static private $Ne0 = array(
        0 => array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть',
                   'семь', 'восемь', 'девять', 'десять', 'одиннадцать',
                   'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать',
                   'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать'),
        1 => array('', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть',
                   'семь', 'восемь', 'девять', 'десять', 'одиннадцать',
                   'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать',
                   'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать')
    );

    static private $Ne1 = array('', 'десять', 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');

    static private $Ne2 = array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');

    static private $Ne3 = array(1 => 'тысяча', 2 => 'тысячи', 5 => 'тысяч');

    static private $Ne6 = array(1 => 'миллион', 2 => 'миллиона', 5 => 'миллионов');



    /**
     * @return string
     * @param int $number - Число
     * @param array $titles_array - Массив со списком существительных для правильного склонения
     * @param boolean $female - Если true - склоняет использую женский род
     * @desc Вернет число прописью
     */
    static function get($number, $titles_array = 0, $female = false)
    {

        if (($number < 0) || ($number >= 1e9) || !is_numeric($number))
            return false; // Аргумент должен быть неотрицательным целым числом, не превышающим 1 миллион

        if ($number == 0)
            $ret = self::$N0;
        else
            $ret = preg_replace(array('/s+/', '/\s$/'), array(' ', ''), self::num1e9($number, $female));

        if (!empty($titles_array) && is_array($titles_array))
            $ret = $ret .' '. $titles_array[ self::getDeclNum($number)];

        return $ret;
    }

    /**
     * @return string
     * @param int $number - Число
     * @param array $titles_array - Массив со списком существительных для правильного склонения
     * @desc Правильно склоняет числительные
     */
    static function decl($number, $titles_array) {
        return $number.' '.$titles_array[ self::getDeclNum($number) ];
    }


    /**
     * @return int
     * @param int $number - Число
     * @desc Вернет номер склонения
    форма склонения слова, существительное с числительным склоняется
    одним из трех способов: 1 миллион, 2 миллиона, 5 миллионов
     */
    static function getDeclNum($number)
    {

        $n100 = $number % 100;
        $n10 = $number % 10;

        if (($n100 > 10) && ($n100 < 20))
            return 5;
        else if ($n10 == 1)
            return 1;
        else if (($n10 >= 2) && ($n10 <= 4))
            return 2;
        else
            return 5;

    }


    private static function num1e9($i, $female)
    {

        if ($i < 1e6)
            return self::num1e6($i, $female);
        else
            return self::num1000(intval($i / 1e6), false) . ' ' . self::$Ne6[self::num_125(intval($i / 1e6))] . ' ' . self::num1e6($i % 1e6, $female);
    }

    private static function num1e6($i, $female)
    {

        if ($i < 1000)
            return self::num1000($i, $female);
        else
            return self::num1000(intval($i / 1000), true) . ' ' . self::$Ne3[self::num_125(intval($i / 1000))] . ' ' . self::num1000($i % 1000, $female);
    }

    private static function num1000($i, $female)
    {

        if ($i < 100)
            return self::num100($i, $female);
        else
            return self::$Ne2[intval($i / 100)] . (($i % 100) ? (' ' . self::num100($i % 100, $female)) : '');

    }

    private static function num100($i, $female)
    {

        $gender = $female ? 1 : 0;

        if ($i < 20)
            return self::$Ne0[$gender][$i];
        else
            return self::$Ne1[intval($i / 10)] . (($i % 10) ? (' ' . self::$Ne0[$gender][$i % 10]) : '');

    }

}

?>