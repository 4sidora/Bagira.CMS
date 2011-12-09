<?php

$TEMPLATE['main_button'] = <<<END
<span class="but_sub"><a href="%link%">%title%</a><ins></ins></span>
END;


$TEMPLATE['main_button_list'] = <<<END

<span class="but_sub addp"><a href="%link%">%title%</a></span>
<div class="addcategor">
    <a class="add" id="btn_%list_id%%down%"><span></span></a>
    <div class="newhidden">
            %list%
    </div>
</div>

END;

$TEMPLATE['back_button'] = <<<END
<span class="but_cancel"> %title2% &nbsp; <a href="%link%">%title%</a></span>
END;

$TEMPLATE['back_button2'] = <<<END
<span class="but_cancel"><a href="%link%">%title%</a></span>
END;

?>