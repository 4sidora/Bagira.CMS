<?php

$TEMPLATE['frame'] = <<<END

<div class="filterABC">
    %list%

    <span class="reset" rel="all">ВСЕ</span>
    <div class="clear"></div>
</div>

    <form id="filterForm" name="filterForm" action="%structure.getObjUrl(%section_id%)%" method="post">
        <input type="hidden" name="content" id="filter_abc" value="">
        <input type="hidden" name="content_FILTER_ABC" value="1">
        <input name="target" type="hidden" value="%target%"> 
    </form>

END;

$TEMPLATE['list'] = <<<END
    <span>%symbol%</span>
END;

$TEMPLATE['list_active'] = <<<END
    <span><b>%symbol%</b></span>
END;


?>