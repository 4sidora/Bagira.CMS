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
           <a href="%obj.url%" title="%obj.name%">%obj.name%</a>
       </div>
       <b class="newsb2">&nbsp;</b>
       <b class="newsb1">&nbsp;</b>
    </div>
    %sub_menu%
</li>        
END;

$TEMPLATE[1]['list'] = <<<END
<li><a href="%obj.url%" title="%obj.name%">%obj.name%</a></li>
END;


$TEMPLATE[2]['frame'] = <<<END
<ul class="submenu">
    %list%
</ul>          
END;

$TEMPLATE[2]['list_active'] = <<<END
<li class="selected"><a href="%obj.url%" title="%obj.name%">%obj.name%</a></li>        
END;

$TEMPLATE[2]['list'] = <<<END
<li><a href="%obj.url%" title="%obj.name%">%obj.name%</a></li>
END;


?>