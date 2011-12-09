<?php

$TEMPLATE['frame'] = <<<END
<select id="%selbox.id%" name="%selbox.name%" alt="%selbox.id%" rel="%selbox.id%">%selbox.items%</select>
END;

$TEMPLATE['item'] = <<<END
<option value="%item.id%"%item.act%>%item.name%</option>
END;


?>