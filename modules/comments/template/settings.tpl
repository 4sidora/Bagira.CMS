<?php

$TEMPLATE['frame'] = <<<END
<div class="onetabs">
    <div class="ins" style="padding-left:20px;">

	<form id="changeForm" name="changeForm" action="" method="post" enctype="multipart/form-data">

        %only_reg%
           <br /><br />

        %show_noactive%

        <br /><br />
        Максимальная длинна комментария <input class="input" type="text" name="text_length" id="text_length" value="%text_length%" style="width:50px;"> символов.

	 	<input name="parram" id="parramForm" type="hidden" value="">
	 	<input name="right" type="hidden" value="settings_proc">
	</form>

	</div><!-- end ins-->
    <div class="clear"></div>
</div><!-- end tab -->
END;

?>