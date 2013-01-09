<?php

$TEMPLATE['frame_list'] = <<<END

<h4>Похожие публикации</h4>
<ul>
    %list%
</ul>


END;

$TEMPLATE['list'] = <<<END
    <li><a href="%obj.url%" title="%obj.name%">%obj.name%</a></li>
END;


?>