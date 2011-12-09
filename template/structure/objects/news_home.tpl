<?php

$TEMPLATE['frame'] = <<<END
%list%
END;

$TEMPLATE['frame_list'] = <<<END
<div class="newslist">
    <h2><a href="/news" title="">Новости B Mart</a></h2>
    %list%
    <div class="clear"></div>
</div>    

END;

$TEMPLATE['list'] = <<<END
<div class="news">
    <a href="%obj.url%" title="%obj.name%">%obj.name%</a><br/><br/>
     %obj.notice%
</div>
END;


?>