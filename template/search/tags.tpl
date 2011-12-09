<?php

$TEMPLATE['frame'] = <<<END

<div class="tegs">
    <span>Теги :</span>
    <div>%list%</div>
    <div class="clear"></div>
</div>

END;

$TEMPLATE['list'] = <<<END
<a href="%obj.url%">%obj.name%</a>
END;

$TEMPLATE['separator'] = <<<END
, &nbsp;
END;

$TEMPLATE['empty'] = <<<END

END;


?>