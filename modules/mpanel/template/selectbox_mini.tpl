<?php

$TEMPLATE['frame'] = <<<END
<select id="%selbox.id%" name="%selbox.name%"  class="selectbox_filter"  style="width:%selbox.size%px;"%selbox.java%>%selbox.items%</select>
END;

$TEMPLATE['item'] = <<<END
<option value="%item.id%"%item.act%>%item.name%</option>
END;


?>