<?php

$TEMPLATE['frame'] = <<<END
<form id="ajaxGroupForm" class="popupForm">



	<div class="field_box">
		%text.1%
		<input class="input" type="text" name="group_name" id="group_name" value="%obj.group_name%">
	</div>

	<div class="field_box">
		<label>%text.2%</label>
		<input class="input" type="text" name="group_sname" id="group_sname" value="%obj.group_sname%">
	</div>

    <div class="clear"></div>

		%group_view%  <br />
        %group_system%

 		<input name="right" type="hidden" value="%right%">
 		<input name="obj_id" type="hidden" value="%obj.id%">

</form>
END;

?>