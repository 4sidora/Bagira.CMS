<?php

$TEMPLATE['frame'] = <<<END

<form action="/search" method="post">
	
    <input type="text" name="words" class="bigsearch"  value="%words%"/>
    	<button class="searchbutton">Найти</button>
	<input name="classes" type="hidden" value="">
</form>

 <div class="clear"></div>
	%list%

END;

$TEMPLATE['frame_list'] = <<<END
	%list%

%structure.navigation(%count_page%, 4, search)%

END;


$TEMPLATE['list'] = <<<END
<div class="searchresult">
    <a href="%obj.url%" title="%obj.name%">%obj.name%</a><br/><br/>
</div>
END;

$TEMPLATE['list_goods'] = <<<END

        <div class="similargoods searchresult">
            <div class="similarwrap">
                  <b class="newsb1">&nbsp;</b>
                  <b class="newsb2">&nbsp;</b>
                    <div class="similargoodsimg">
                        <a href="%obj.url%">%structure.getProperty(image, %obj.id%, photolistsmall)%</a>
                    </div>
                   <b class="newsb2">&nbsp;</b>
                   <b class="newsb1">&nbsp;</b>
            </div>
            <div class="content">
            	 <a href="%obj.url%" title="">%obj.name%</a><br/>
                 <div class="rating" rel="%obj.rate%" name="%obj.id%" id="search%obj.id%"></div>
                 <a title="" class="price"><span>%obj.price%</span> <b style="font-size:10px;">Р</b><span class="rubl" style="color:#fff;">−</span></a>
                 <div class="clear"></div>
                  Возможные цвета корпуса — %obj._color%; Мощность — %obj.moshchnost% кВт;
                    Вес — %obj.ves_netto% кг; Гаратия — %obj._garantiya%; Страна производитель — %obj._strana_proizvoditel%;
            </div>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>

END;

$TEMPLATE['list_faq'] = <<<END
<div class="searchresult">
	<a href="%obj.url%" title="%obj.name%">Вопрос от посетителя %obj.name%</a> <br /> <br />
</div>
END;

$TEMPLATE['not_found'] = <<<END
<div class="searchresult">
 По вашему запросу ничего не найдено. Повторите поиск изменив условия.
</div>
END;

?>