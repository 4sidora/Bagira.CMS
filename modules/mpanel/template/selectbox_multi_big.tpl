<?php

$TEMPLATE['frame'] = <<<END
<select id="%selbox.id%" name="%selbox.name%[]" class="selectbox234" style="width:%selbox.size%px;height:200px;"%selbox.java% multiple="multiple" size="4">%selbox.items%</select>
END;

$TEMPLATE['item'] = <<<END
<option value="%item.id%"%item.act%>%item.name%</option>
END;


?>