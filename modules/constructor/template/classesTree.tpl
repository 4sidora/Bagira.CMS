<?php

$TEMPLATE['frame'] = <<<END

<script type="text/javascript" src="/css_mpanel/class_tree.js"></script>
<div class="otstup"></div>

<div style="width: 988px; overflow: hidden;">

%items%

</div>

<div class="otstup"></div>

END;

$TEMPLATE['frame_items'] = <<<END

<ul class="classesTree">%items%</ul>

END;

$TEMPLATE['item'] = <<<END
<li id="li_class_%obj.id%">
	<div id="line_class_%obj.id%" name="%obj.id%">

        <img src="%obj.ico%" border="0">

		<a href="%obj.url%">%obj.name%</a>

		<div> <div id="edits_%obj.id%">%rights%</div> </div>

		<span>&nbsp;&nbsp;&nbsp;%obj.sname%&nbsp;&nbsp;&nbsp;</span>

	</div>

    %subitem%

</li>
END;


$TEMPLATE['right'] = <<<END

	<a style="float:left;" %del_button% name="%obj.id%" href="%right.url%/%obj.id%">
	   <div class="header_tree">
	      <font class="%right.class%">%right.title%</font>
	   </div>
	</a>

END;

$TEMPLATE['empty_right'] = <<<END

	<div style="display:block;float:left;width:28px;height:20px;">&nbsp;</div>

END;


?>