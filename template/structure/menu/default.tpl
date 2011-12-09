<?php

$TEMPLATE[1]['frame'] = <<<END
<div id="ulwrapper">
	<table id="nav" cellpadding="0" cellspacing="0">
		<tr>
            %list%
         </tr>
    </table>     
</div> 
<div class="clear"></div>
END;

$TEMPLATE[1]['list_active'] = <<<END
<td class="selected"><div><a href="%obj.url%" title="%obj.name%">%obj.name%</a><b>&nbsp;</b></div></td>
END;

$TEMPLATE[1]['list'] = <<<END
<td><a href="%obj.url%" title="%obj.name%">%obj.name%</a><b>&nbsp;</b></td>
END;



?>