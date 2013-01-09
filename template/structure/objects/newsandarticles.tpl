<?php

$TEMPLATE['frame_list'] = <<<END
<ul class="menu underlined">
   %list%   
</ul>
END;

$TEMPLATE['list_active'] = <<<END
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
    </li>
END;

$TEMPLATE['list'] = <<<END
<li><a href="%obj.url%" title="%obj.name%">%obj.name%</a></li>
END;


?>