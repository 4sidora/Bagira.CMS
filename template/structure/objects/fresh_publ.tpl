<?php

$TEMPLATE['frame_list'] = <<<END
<h4>Свежие статьи</h4>
<ul>
    %list%
</ul>
<small><a href="/article" title="Все статьи">Все статьи</a></small>

  
END;

$TEMPLATE['list_active'] = <<<END
<li><a href="%obj.url%" title="%obj.name%">%obj.name%</a></li>
END;

$TEMPLATE['list'] = <<<END
<li><a href="%obj.url%" title="%obj.name%">%obj.name%</a></li>
END;



?>