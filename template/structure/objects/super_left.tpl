<?php

$TEMPLATE['frame'] = <<<END

	%list%

END;

$TEMPLATE['frame_list'] = <<<END
<ul>	
%list%
</ul>

END;


$TEMPLATE['list_goods'] = <<<END
<li class="minio_%obj.num%" >
    %structure.getProperty(image, %obj.id%, super_left)%
    <a href="%obj.url%" title="%obj.name%">%obj.name%<span>&nbsp;</span><ins>&nbsp;</ins></a>
    <div class="arrow"></div>
    <div class="arrowtail"></div>
</li>
END;


?>