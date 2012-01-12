<?php

/*
    Bagira.CMS Copyright 2011
    http://bagira-cms.ru
    http://bagira-cms.com

	
*/

class basket {

    //static private $tags = array();

    /**
	* @return NULL
	* @param int $goods_id - ID товара
	* @param int $count - Количество товаров
	* @desc Добавляет товар в корзину
	*/
    static function addGoods($goods_id, $count) {

        if (!isset($_SESSION['basket']))
            $_SESSION['basket'] = array();

        // Получаем экземпляр товара
        if (is_numeric($goods_id))
            $goods = ormPages::get($goods_id, 'goods');
        else if ($goods_id instanceof ormPage)
            $goods = $goods_id;
        else
            $goods = false;

        // Добавляем в корзину
        if (($goods instanceof ormPage) && $goods->isInheritor('goods')) {

            if (!reg::getKey('/eshop/check_count') || reg::getKey('/eshop/min_count') < $goods->count){
            
                if (isset($_SESSION['basket'][$goods->id]))
                    $_SESSION['basket'][$goods->id]['count'] ++;
                else
                    $_SESSION['basket'][$goods->id] = array(
                        'goods_id' => $goods->id,
                        'cost' => $goods->price,
                        'count' => $count
                    );
            }
        }
    }

    static function changeGoods($goods_id, $count) {

        if (isset($_SESSION['basket'][$goods_id])) {

            $count = system::checkVar($count, isInt);
            $_SESSION['basket'][$goods_id]['count'] = $count;

            return true;
        }

        return false;
    }


    static function delGoods($goods_id) {

        if (isset($_SESSION['basket'][$goods_id])) {
            unset($_SESSION['basket'][$goods_id]);
            return true;
        }

        return false;
    }





    // Удалит все товары из корзины
    static function clear() {
        if (isset($_SESSION['basket']))
            unset($_SESSION['basket']);
    }

    // Вернет количество товаров в корзине
    static function getCount() {
        if (isset($_SESSION['basket']))
            return count($_SESSION['basket']);
        else
            return 0;
    }

    // Вернет стоимость всех товаров в корзине
    static function getTotalCost() {
        if (isset($_SESSION['basket'])) {

            $cost = 0;

            reset($_SESSION['basket']);
            while(list($num, $goods) = each($_SESSION['basket'])) {
                $cost += $goods['cost'] * $goods['count'];
            }

            return $cost;            
        } else
            return 0;
    }

    static function getGoodsData() {

        if (isset($_SESSION['basket']))
            return $_SESSION['basket'];
        else
            return array();
    }





}

?>