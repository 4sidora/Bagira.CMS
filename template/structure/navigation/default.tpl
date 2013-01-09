<?php

$TEMPLATE['frame'] = <<<END
<div class="clear"></div>
<div class="pagenav">
    <a href="%first_page%" title="" class="navarrow">←</a>
    %left_block%
    
    <ul>
      %list%
   </ul>  
   
   %right_block%
   <a href="%last_page%" title="" class="navarrow">→</a>
</div>
END;

$TEMPLATE['list'] = <<<END
<li><a href="%page_url%" title="%page_num%">%page_num%</a></li>
END;

$TEMPLATE['list_active'] = <<<END
<li><a href="%page_url%" title="%page_num%" class="selected">%page_num%</a></li>
END;

$TEMPLATE['left_block'] = <<<END
<a href="%previous_page%" title="Предыдущая" class="previously">Предыдущая</a>
END;

$TEMPLATE['right_block'] = <<<END
<a href="%next_page%" title="Следующая" class="next">Следующая</a>
END;

?>