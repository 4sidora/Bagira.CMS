<?php

$TEMPLATE['frame'] = <<<END
<select class="birthday_mounth" id="%selbox.id%" rel="%selbox.id%" name="%selbox.name%" alt="%selbox.id%" style="width:%selbox.size%px;">%selbox.items%</select>
END;

$TEMPLATE['item'] = <<<END
<option value="%item.id%"%item.act%>%item.name%</option>
END;


?>