<?php

$TEMPLATE['frame'] = <<<END

<script language="javascript">

jQuery(document).ready(function() {

    var rules = [];
	rules.push("required,emails,"+LangArray['SUBSCR_MSG_1']);
	rules.push("required,subscribes,"+LangArray['SUBSCR_MSG_2']);

	$("#changeForm").RSV({
 		customErrorHandler: ShowMessageRVS,
        rules: rules
    });
});

</script>

<div class="onetabs">
    <div class="ins" style="padding-left:20px;">

	<form id="changeForm" name="changeForm" action="" method="post" enctype="multipart/form-data">


    <div class="fieldBox">
        <label for="emails" class="dotted chek" title="%text.1%"><b></b>%text.2%</label>
        <textarea name="emails" wrap="off" style="width:95%;height:196px;"></textarea>
    </div>

    <div class="fieldBox">
        <label for="subscribes" class="dotted chek" title="%text.3%"><b></b>%text.4%</label>
        %subscribes%
    </div>

	 	 <input name="parram" id="parramForm" type="hidden" value="">
	 	 <input name="right" type="hidden" value="user_proc_addlist">
	 	 <input name="parent_id" type="hidden" value="%parent_id%">
	</form>

	</div><!-- end ins-->
    <div class="clear"></div>
</div><!-- end tab -->
END;

?>