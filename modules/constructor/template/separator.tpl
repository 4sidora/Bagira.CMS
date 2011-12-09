<?php

$TEMPLATE['frame'] = <<<END
<form id="ajaxSeparForm" class="popupForm">

	<div class="field_box">
	    <label>%text.17% </label>
	    <input class="input" type="text" name="fname" id="fname" value="%obj.fname%" />
	</div>

	<div class="field_box">
        <label> %text.18% - <span id="max_size_span"><span></label>
        <div class="otstup"></div>
        <div id="slider-range"></div>
        <input type="hidden" name="max_size" id="max_size" value="%obj.max_size%">
	</div>

 	<input name="right" type="hidden" value="%right%">
 	<input name="obj_id" type="hidden" value="%obj.id%">

</form>
END;

?>