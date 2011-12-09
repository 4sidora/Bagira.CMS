<?php

$TEMPLATE['frame'] = <<<END


<div class="thumb">
 	<div class="thumbnumbers">
        %left_block%

    	%list%
    	%right_block%
  	</div>
</div>
<div class="clear"></div>
END;

$TEMPLATE['list'] = <<<END
<a href="%page_url%">%page_num%</a>
END;

$TEMPLATE['list_active'] = <<<END
<a href="#">%page_num%</a>
END;

$TEMPLATE['left_block'] = <<<END
<a href="%previous_page%" title="Предыдущая" class="arrow">&larr;</a>
END;

$TEMPLATE['right_block'] = <<<END
<a href="%next_page%" title="Следующая" class="arrow">&rarr;</a>
END;










?>