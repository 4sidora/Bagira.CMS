<?php

$TEMPLATE['frame'] = <<<END
<script type="text/javascript" src="/css_mpanel/right_list.js"></script>

<div id="right_title"><b>%text.title%</b></div>

<div id="rights_list">

    <div class="right_cb">
		<img src="/css_mpanel/images/compose.gif" width="16" height="16" border="0">
	</div>

    <div class="right_cb">
		<img src="/css_mpanel/images/lupa.gif" width="16" height="16" border="0">
	</div>


	<div class="clear"></div>

	%groups%

	<div id="new_lines"></div>

	<div class="right_cb">&nbsp;</div><div class="right_cb">&nbsp;</div>
	<div class="right_title"><input id="find_user" type="text" value=""></div>

	<div class="otstup"></div>

	<div class="right_cb"><input name="all_edit" type="checkbox"  value="1" id="bigcheck_edit" %checked_edit%></div>
	<div class="right_cb"><input name="all_view" type="checkbox"  value="1" id="bigcheck_view" %checked_view%></div>
	<div class="right_title">%text.all_user%</div>

</div>

END;

$TEMPLATE['group'] = <<<END

<div class="right_cb">
 <input name="edit_right[%group.id%]" type="checkbox" id="cd_edit_%group.id%" value="%group.id%" class="check_edit" %checked_edit%>
</div>
<div class="right_cb">
 <input name="view_right[%group.id%]" type="checkbox" id="cd_view_%group.id%" value="%group.id%" class="check_view" %checked_view%>
</div>
<div class="right_title"><label for="cd_view_%group.id%">%group.name%</label></div>

END;



?>
