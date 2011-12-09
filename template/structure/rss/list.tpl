<?php

$TEMPLATE['frame_list'] = <<<END

<a href="/structure/rss/all">Все ленты</a>
<br /><br />
%list%

END;

$TEMPLATE['list'] = <<<END
<a href="%obj.rss_url%" title="%obj.name%">%obj.name%</a>
<br /><br />
END;

?>