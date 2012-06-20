<?php

$TEMPLATE[1]['frame'] = <<<END
<ul class="menu">
    %list%
</ul>   
END;

$TEMPLATE[1]['list_active'] = <<<END
	<li class="selected">
        <div class="selectedwrap">
           <b class="newsb1">&nbsp;</b>
           <b class="newsb2">&nbsp;</b>
                <div class="swrap">
                    <a href="%obj.url%" title="%obj.name%" class="img" %obj.target%>%structure.getProperty(image, %obj.id%, photogallery_min)%</a>
                    <div class="title">
                        <a href="%obj.url%" title="%obj.name%" class="title" %obj.target%>%obj.name%</a><br/>
                        <small>Фотографий %structure.objCount(%obj.id%)%</small>
                    </div>    
                    <div class="clear"></div>
                </div>    
           <b class="newsb2">&nbsp;</b>
           <b class="newsb1">&nbsp;</b>
        </div>
    </li>
END;

$TEMPLATE[1]['list'] = <<<END
    <li>
        <a href="%obj.url%" title="%obj.name%" class="img" %obj.target%>%structure.getProperty(image, %obj.id%, photogallery_min)%</a>
        <div class="title">
            <a href="%obj.url%" title="%obj.name%" class="title" %obj.target%>%obj.name%</a><br/>
            <small>Фотографий %structure.objCount(%obj.id%)%</small>
        </div>    
        <div class="clear"></div>
    </li>
END;



?>