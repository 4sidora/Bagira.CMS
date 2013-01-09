<?php

$TEMPLATE['frame_list'] = <<<END
    <div class="similar">

            <h4>Похожие товары</h4>

            %list%

    </div>
<div class="clear"></div>
END;



$TEMPLATE['list'] = <<<END

                <div class="similargoods">
                    %structure.getProperty(image, %obj.id%, basket_img)%
                    <div class="content">
                     	 <a href="%obj.url%" title="%obj.name%">%obj.name%</a><br/>
                         <div class="rating" rel="%obj.rate%" name="%obj.id%" id="rating%obj.id%"></div>
                         <a title="%obj.name%" class="price"><span>%obj.price%</span>
                             <b style="font-size:10px;">Р</b>
                             <span class="rubl" style="color:#fff;">−</span></a>
                    </div>
                </div>

END;



?>