<?php

$TEMPLATE['frame'] = <<<END

%structure.filter(%page_id%)%

%list%

END;

$TEMPLATE['frame_list'] = <<<END

%list%

%structure.navigation(%count_page%)%

END;



$TEMPLATE['list_category'] = <<<END

<div class="item">
    <div class="itemimgwrap">
        <b class="newsb1">&nbsp;</b>
        <b class="newsb2">&nbsp;</b>
        <div class="itemimg">
            <a href="%obj.url%" title="%obj.name%">
                %structure.getProperty(image, %obj.id%, element)%</a>
        </div>
        <b class="newsb2">&nbsp;</b>
        <b class="newsb1">&nbsp;</b>
    </div>
    <div class="content">
        <h3><a href="%obj.url%" title="%obj.name%">%obj.name%</a></h3>
        <div class="clear"></div>
    </div>
</div>

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
        <a href="%structure.getObjURL(%obj.parent_id%)%">
            %structure.getProperty(name, %obj.parent_id%)%
        </a>
        <h3><a href="%obj.url%" title="%obj.name%">%obj.name%</a></h3>
        Возможные цвета корпуса — %obj._color%; Мощность — %obj.moshchnost% кВт;
        Вес — %obj.ves_netto% кг; Гаратия — %obj._garantiya%; Страна производитель — %obj._strana_proizvoditel%;
        <div class="clear"></div>
     <a  title="" class="buy addtocart">в магазине за</a>
    <div class="clear"></div>
    <a title="%obj.url%" class="price addtocart"><span class="summa">%obj.price%</span> <b style="font-size:19px;">Р</b><span class="rubl" style="color:#fff;">−</span></a>
    <div class="clear"></div>

    </div>  
</div>
END;



$TEMPLATE['frame_goods'] = <<<END

    <div id="leftcolumn">
        <div id="aboutmore">
            <div class="image" >
                <span class="newtabcell">%structure.getProperty(image, %obj.id%, photo_list_max)%</span>
            </div>
            %structure.objList(%obj.id% photo, photo_list)%
        </div>
        <div class="clear"></div>
    </div>


    <div id="rightcolumn">

        <div class="buy">
            <a title="" class="addtocart" style="font-size:11px !important">в магазине за</a><br/>
            <div class="clear"></div>
            <span class="summa" style="font-size:16px !important;">%obj.price%</span>
            <b style="font-size:17px;">Р</b><span class="rubl" style="color:#fff;">−</span>
        </div>

        Рейтинг<br/>
        <div class="readonly" rel="%obj.rate%"></div>
        
        Оценить товар<br/>
        <div class="rating" rel="0" name="%obj.id%"></div>



        <a href="/adresa-i-kontakty" title="">Где купить в офлайне</a><br/>
    </div>
    <div class="clear"></div>


    <ul class="viewsmenu">
        <li class="selected"><a href="#" rel="main">Описание товара</a></li>
        <li><a href="#" rel="property">Технические характеристики</a></li>
        <li><a href="#" rel="reviews">Отзывы (<span>%structure.objCount(%obj.id%, review, 1)%</span>)</a></li>
    </ul>
    <div class="clear"></div>



    <div id="content_main" class="contentwrapper">
        <div class="leftcolumn">
            %obj.content%
        </div>
        <div class="rightcolumn">

            <a href="%print_url%" target="_blank" class="print">Распечатать страницу</a><br/>

            <a href="/adresa-i-kontakty" title="">Руководство пользователя</a><br/>
            <a href="/faq" title="">Задать вопрос о товаре</a><br/>
            <a href="/oplata-i-dostavka" title="">Информация о гарантии</a><br/>
            <a href="/oplata-i-dostavka" title="">Способы оплаты и доставки</a><br/>
        </div>
        <div class="clear"></div>
    </div>

    <div id="content_property" class="contentwrapper hidedcontent">
        <div class="leftcolumn">

            %structure.fieldList(%obj.id%, property)%

            Характеристики и комплектация могут быть изменены фирмой-производителем без предварительного уведомления.

        </div>

        <div class="rightcolumn">

            <a href="%print_url%" target="_blank" class="print">Распечатать страницу</a><br/>

            <a href="/adresa-i-kontakty" title="">Руководство пользователя</a><br/>
            <a href="/faq" title="">Задать вопрос о товаре</a><br/>
            <a href="/oplata-i-dostavka" title="">Информация о гарантии</a><br/>
            <a href="/oplata-i-dostavka" title="">Способы оплаты и доставки</a><br/>
        </div>
        <div class="clear"></div>
    </div>

    <div id="content_reviews" class="contentwrapper hidedcontent">

        %structure.objList(%obj.id% review, review)%

        <div class="clear"></div>
    </div>


    %structure.getPropertyList(related_products, %obj.id%, similar)%
    <br/><br/>
    %structure.getPropertyList(accessories, %obj.id%, similar2)%


END;
//%eshop.button(%obj.id%)%

$TEMPLATE['print_frame_goods'] = <<<END

<div id="leftcolumn">

    <div id="aboutmore">
        <div class="image">
            <img src="%core.resize(%obj.image%, stRateably, 150)%" alt=""/>
        </div>

        <div class="price">
            %obj.price% Р
        </div>
    </div>

    <div class="clear"></div>

    <div class="contentwrapper">

        <h4 class="noborder">Описание товара</h4>
        %obj.content%

        <h4 class="noborder">Технические характеристики</h4>
        %structure.fieldList(%obj.id%, property)%

        Характеристики и комплектация могут быть изменены фирмой-производителем без предварительного уведомления
        
    </div>
</div>



END;

?>