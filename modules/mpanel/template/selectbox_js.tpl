<?php

$TEMPLATE['frame'] = <<<END
<select name="%selbox.name%'+lineCount_%selbox.comp_name%+'"  class="selectbox"  style="width:%selbox.size%px;"%java%>%selbox.items%</select>
END;

$TEMPLATE['item'] = <<<END
<option value="%item.id%"%item.act%>%item.name%</option>
END;


?>