<?php

$TEMPLATE['frame'] = <<<END

<script language="javascript" src="/css_mpanel/search_index.js"></script>

<div id="progressBar" style="display:none;" title="%text4%">
	<p>
        <div style="display:inline;" id="count_message"></div>  <br /><br />
        <div style="background: #efefef; width:100%; height:20px; border: 1px solid #959a9f;">
        	<div id="probar"  style="background: #e8e3d0; width:0; height:20px;"></div>
        </div>
	</p>
</div>

<div class="onetabs">
    <div class="ins" style="padding-left:20px;">

    <form id="changeForm" action="" method="post">
    	<input name="parram" id="parramForm" type="hidden" value="">
	 	<input name="right" type="hidden" value="index_proc">
	</form>

    <table width="350" height="100">
	    <tr><td>%text1%</td><td id="count_pages">%count_page%</td></tr>
	    <tr><td>%text2%</td><td id="count_words">%count_words%</td></tr>
	    <tr><td>%text3%</td><td id="index_date">%index_date%</td></tr>
    </table>

    </div><!-- end ins-->
    <div class="clear"></div>
</div><!-- end tab -->

END;


$TEMPLATE['settings'] = <<<END
<script language="javascript">

jQuery(document).ready(function() {

    var rules = [];
	rules.push("required,max_count,"+LangArray['SEARCH_ERROR_1']);
	rules.push("digits_only,max_count,"+LangArray['SEARCH_ERROR_2']);

	$("#changeForm").RSV({
 		customErrorHandler: ShowMessageRVS,
        rules: rules
    });
});

</script>

<div class="onetabs">
    <div class="ins" style="padding-left:20px;">

	<form id="changeForm" name="changeForm" action="" method="post" enctype="multipart/form-data">

	     %auto_index%

	     <div class="otstup"></div>

	     %text.count_res% <input name="max_count" class="input" style="width:20px;" type="text" value="%max_count%">

	 	 <input name="parram" id="parramForm" type="hidden" value="">
	 	 <input name="right" type="hidden" value="settings_proc">
	</form>

    </div><!-- end ins-->
    <div class="clear"></div>
</div><!-- end tab -->
END;

?>