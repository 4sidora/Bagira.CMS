<?php

$TEMPLATE['frame'] = <<<END
<select id="%selbox.id%" name="%selbox.name%"  class="input_min_1"  style="width:%selbox.size%px;"%selbox.java%>%selbox.items%</select>
END;

$TEMPLATE['item'] = <<<END
<option value="%item.id%"%item.act%>%item.name%</option>
END;


?>