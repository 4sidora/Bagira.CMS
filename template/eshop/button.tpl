<?php

$TEMPLATE['frame'] = <<<END

    <div class="buy">
        <a href="%pre_lang%/eshop/to-basket/%goods_id%" class="addtocart">купить за</a><br/>
        <div class="clear"></div>
        <span class="summa">%goods_price%</span>
        <b style="font-size:19px;">Р</b><span class="rubl" style="color:#fff;">−</span>
    </div>

END;

$TEMPLATE['empty'] = <<<END


    <!--
    <div class="buy">
        <a title="" class="addtocart" style="font-size:11px !important">в магазине за</a><br/>
        <div class="clear"></div>
        <span class="summa" style="font-size:16px !important;">%goods_price%</span>
        <b style="font-size:17px;">Р</b><span class="rubl" style="color:#fff;">−</span>
    </div>

    -->

<div class="buy">
        <a href="%pre_lang%/eshop/to-basket/%goods_id%" class="addtocart">купить за</a><br/>
        <div class="clear"></div>
        <span class="summa">%goods_price%</span>
        <b style="font-size:19px;">Р</b><span class="rubl" style="color:#fff;">−</span>
    </div>

END;


?>