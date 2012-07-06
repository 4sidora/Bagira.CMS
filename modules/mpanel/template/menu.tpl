<?php

$TEMPLATE['basic_menu'] = <<<END
<ul>
	%items%

	%sub_menu%
</ul>

END;

$TEMPLATE['item_no_act'] = <<<END
<li><a href="%url%" title="%name%">%name%</a></li>
END;

$TEMPLATE['item_act'] = <<<END
<li><span id="selected"><a href="%url%">%name%</a></span></li>
END;

$TEMPLATE['sub_menu'] = <<<END
<li>
	<a class="combo" href="#" title="">
	<span>%eshe%</span></a>
    <ul class="hidden">
    	%sub_items%
 	</ul>
</li>
END;

$TEMPLATE['sub_item'] = <<<END
<li><a href="%url%">%name%</a></li>
END;

$TEMPLATE['langver'] = <<<END
<li><a href="%url%">%name%</a></li>
END;



?>