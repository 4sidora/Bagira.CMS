<?php

$TEMPLATE['main_button'] = <<<END
<a href="%link%" style="text-decoration: none;"><span class="but_sub"><span class="a">%title%</span><ins></ins></span></a>
END;


$TEMPLATE['main_button_list'] = <<<END

<a href="%link%" style="text-decoration: none;"><span class="but_sub"><span class="a">%title%</span></span></a>
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