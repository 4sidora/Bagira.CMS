<?php

$TEMPLATE['frame'] = <<<END
%list%
END;

$TEMPLATE['frame_list'] = <<<END
%list%
END;

$TEMPLATE['list_goods'] = <<<END
<div class="item">
    <div class="itemimgwrap">
        <b class="newsb1">&nbsp;</b>
        <b class="newsb2">&nbsp;</b>
        <div class="itemimg">
            <a href="%obj.url%" title="%obj.name%">%structure.getProperty(image, %obj.id%, element)%</a>
        </div>
        <b class="newsb2">&nbsp;</b>
        <b class="newsb1">&nbsp;</b>
    </div> 
    <div class="content">
    <a href="%structure.getObjURL(%obj.parent_id%)%" title="">%structure.getProperty(name, %obj.parent_id%)%</a>
        <h3><a href="%obj.url%" title="%obj.name%">%obj.name%</a></h3>
        Возможные цвета корпуса — %obj._color%; Мощность — %obj.moshchnost% кВт;
        Вес — %obj.ves_netto% кг; Гаратия — %obj._garantiya%; Страна производитель — %obj._strana_proizvoditel%;
     <a  title="" class="buy addtocart">в магазине за</a>
    <div class="clear"></div>
    <a title="" class="price addtocart"><span class="summa">%obj.price%</span> <b style="font-size:19px;">Р</b><span class="rubl" style="color:#fff;">−</span></a>
    <div class="clear"></div>
    </div>  
</div>
END;

?>