<?php

$TEMPLATE['default'] = <<<END
%value%
END;

$TEMPLATE['img_h1'] = <<<END
 style="background:transparent url(%value%) left center no-repeat;"
END;

$TEMPLATE['image'] = <<<END
    <a href="%obj.url%" title="%obj.name%">
    	<img src="%core.resize(%obj.image%, stRateably, 201, 152)%" alt="%obj.name%"/>
    </a>
END;

$TEMPLATE['news'] = <<<END
<div class="image"> 
    <a href="%obj.url%" title="%obj.name%"> 
            <img src="%core.resize(%obj.image%, stRateably, 0, 99)%" alt="%obj.name%"/>
    </a>
</div>
END;

$TEMPLATE['super_right'] = <<<END
    <a href="%obj.url%" title="%obj.name%"> 
            <img src="%core.resize(%obj.image%, stRateably, 0, 230)%" alt="%obj.name%"/>
    </a>
END;

$TEMPLATE['photo_list_max'] = <<<END
 <img src="%core.resize(%obj.image%, stRateably, 0, 230)%" width="230" alt="%obj.name%"/>
END;

$TEMPLATE['super_left'] = <<<END
 <img src="%core.resize(%obj.image%, stRateably, 0, 20)%" alt="%obj.name%"/>

END;

$TEMPLATE['newsbig'] = <<<END
<br>
<img src="%core.resize(%obj.image%, stRateably, 423)%" alt="%obj.name%"/><br/>
END;

$TEMPLATE['photogallery'] = <<<END
<img src="%core.resize(%obj.image%, stInSquare, 150)%" alt="%obj.name%"/>
END;

$TEMPLATE['photogallery_min'] = <<<END
<img src="%core.resize(%obj.image%, stSquare, 40)%" alt="%obj.name%"/>
END;

$TEMPLATE['element'] = <<<END
<img src="%core.resize(%obj.image%, stInSquare, 150)%" alt="%obj.name%"/>
END;

$TEMPLATE['photolistsmall'] = <<<END
<img src="%core.resize(%obj.image%, stSquare, 35)%" alt="%obj.name%"/>
END;


$TEMPLATE['email'] = <<<END
 <p><em>Электронная почта:</em> <a href="mailto:%obj.email%" title="">%obj.email%</a></p>
END;

$TEMPLATE['phone'] = <<<END
 <p><em>Телефон:</em> %obj.phone%</p>
END;

$TEMPLATE['author'] = <<<END
<h4>Автор</h4>
     %obj.author%<br/><br/>
END;

$TEMPLATE['basket_img'] = <<<END

    <div class="similarwrap">
        <b class="newsb1">&nbsp;</b>
        <b class="newsb2">&nbsp;</b>
        <div class="similargoodsimg">
            <a href="%obj.url%" title="%obj.name%">
                <img src="%core.resize(%obj.image%, stInSquare, 35, 35)%" alt="%obj.name%"/>
            </a>
        </div>
        <b class="newsb2">&nbsp;</b>
        <b class="newsb1">&nbsp;</b>
    </div>
END;

$TEMPLATE['file'] = <<<END
    <a href="%obj.file%" title="%obj.name%">%obj.name%</a><br/>
END;

$TEMPLATE['file_empty'] = <<<END
    <a href="#" title="%obj.name%">%obj.name%</a><br/>
END;

?>