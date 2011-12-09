<?php

$TEMPLATE['frame'] = <<<END

<script language="javascript">

jQuery(document).ready(function() {

    var rules = [];
	rules.push("required,count_mails,%text.3%");
	rules.push("digits_only,count_mails,%text.4%");
	rules.push("required,count_mails_day,%text.5%");
    rules.push("digits_only,count_mails_day,%text.6%");

	$("#changeForm").RSV({
 		customErrorHandler: ShowMessageRVS,
        rules: rules
    });
});

</script>

<div class="onetabs">
    <div class="ins" style="padding-left:20px;">

	<form id="changeForm" name="changeForm" action="" method="post" enctype="multipart/form-data">

		    %text.1%
		    <input name="count_mails" type="text" value="%count_mails%" class="inputfield_m" size=2>
                                     <br /><br />
			%text.2%
			<input name="count_mails_day" type="text" value="%count_mails_day%" class="inputfield_m" size=4>

	 	 <input name="parram" id="parramForm" type="hidden" value="">
	 	 <input name="right" type="hidden" value="settings_proc">
	</form>

	</div><!-- end ins-->
    <div class="clear"></div>
</div><!-- end tab -->
END;

?>