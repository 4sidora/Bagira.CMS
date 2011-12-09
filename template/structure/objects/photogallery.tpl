<?php

$TEMPLATE['frame'] = <<<END
 <div class="albumwrapper">
%list%
<div class="clear"></div> 
</div>


END;

$TEMPLATE['frame_list'] = <<<END
%list%
END;



$TEMPLATE['list'] = <<<END
<div class="album">
    <div class="imgwrap">
        <span>
              <a href="%obj.url%" title="%obj.name%">%structure.getProperty(image, %obj.id%, photogallery)%</a>
        </span>
    </div>
    <div class="title">
        <a href="%obj.url%" title="%obj.name%">%obj.name%</a>
        <div class="shadow"></div>
    </div>    
    <small>Фотографий %structure.objCount(%obj.id%)%</small>
</div>
END;

?>