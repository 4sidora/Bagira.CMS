<?php

$TEMPLATE[1]['frame'] = <<<END
<div id="superofferwrap">
   <b class="newsb1">&nbsp;</b>
   <b class="newsb2">&nbsp;</b>
   <ul class="superoffer">
        %list%
   </ul>
   <b class="newsb2">&nbsp;</b>
   <b class="newsb1">&nbsp;</b>
</div>
END;

$TEMPLATE[1]['list_active'] = <<<END
<li class="selected"> <a href="%obj.url%" title="%obj.name%" %obj.target%>%obj.name%</a></li>
END;

$TEMPLATE[1]['list'] = <<<END
<li><a href="%obj.url%" title="%obj.name%" %obj.target%>%obj.name%</a></li>
END;



?>