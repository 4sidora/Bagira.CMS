<?php

/*

	%text.5%
		<input name="nds" type="text" value="%nds%" class="inputfield_m" size=2>%
*/

$TEMPLATE['frame'] = <<<END


<div class="onetabs">
    <div class="ins" style="padding-left:20px;">

	<form id="changeForm" name="changeForm" action="" method="post" enctype="multipart/form-data">

        %only_reg%
           <br /><br />

        %check_count%
        <input name="min_count" type="text" value="%min_count%" class="inputfield_m" size=2>
           <br /><br />

        %dubl_to_email% (%base_email%)
            <br /><br />

		%text.4%
		%fisrt_state%
            <br />



	 	<input name="parram" id="parramForm" type="hidden" value="">
	 	<input name="right" type="hidden" value="settings_proc">
	</form>

	</div><!-- end ins-->
    <div class="clear"></div>
</div><!-- end tab -->
END;

?>