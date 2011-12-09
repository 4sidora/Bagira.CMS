<?php

$TEMPLATE['frame'] = <<<END

<script language="javascript">

jQuery(document).ready(function() {

    var rules = [];

	rules.push("required,title_prefix,%text.6%");

	$("#changeForm").RSV({
          customErrorHandler: ShowMessageRVS,
          rules: rules
    });

    $("#title_prefix").focus();

});

</script>

<form id="changeForm" name="changeForm" action="" method="post" enctype="multipart/form-data">


<div id="tabs">

	<ul>
    	<li><a href="#tabs-main">%text.10%</a><ins></ins></li>
    	<li><a href="#tabs-page_tpl">%text.11%</a><ins></ins></li>
    	<li><a href="#tabs-obj_tpl">%text.14%</a><ins></ins></li>
    	<li><a href="#tabs-view">%text.15%</a><ins></ins></li>
    </ul>

<div id="tabs-main">

    <div class="ins">

		<div class="fieldBox fBox_line">
	 		<span class="fbox_label"><label for="title_prefix" class="chek" title=""><b></b>%text.1%</label></span>
	   		<input class="input" type="text" name="title_prefix" id="title_prefix" value="%title_prefix%">
	   		<div class="clear"></div>
	 	</div>

	 	<div class="fieldBox fBox_line">
	 		<span class="fbox_label"><label for="keywords" class="" title=""><b></b>%text.2%</label></span>
	   		<input class="input" type="text" name="keywords" id="keywords" value="%keywords%">
	   		<div class="clear"></div>
	 	</div>


		<div class="fieldBox fBox_line">
	 		<span class="fbox_label"><label for="description" class="" title=""><b></b>%text.3%</label></span>
	   		<input class="input" type="text" name="description" id="description" value="%description%">
	   		<div class="clear"></div>
	 	</div>

	 	<div class="fieldBox fBox_line" style="width:700px;">
	 		<span class="fbox_label"><label for="" class="" title=""><b></b></label></span>
	   		%view_as_tree%
	   		<div class="clear"></div>
	 	</div>

	    <div class="fieldBox fBox_line" style="width:700px;">
	 		<span class="fbox_label"><label for="" class="" title=""><b></b></label></span>
	   		%no_view_no_edit%
	   		<div class="clear"></div>
	 	</div>

        <div class="fieldBox fBox_line" style="width:700px;">
	 		<span class="fbox_label"><label for="" class="" title=""><b></b></label></span>
	   		%auto_index%
	   		<div class="clear"></div>
	 	</div>



	    <div class="otstup"></div>
	    <div class="otstup"></div>

	    <div class="fieldBox fBox_line">
	    	<span class="fbox_label"><label for="" class=""><b></b>%text.5%</label></span>
	        <textarea name="robots" wrap="off" class="maxtext">%robots%</textarea>
	        <div class="clear"></div>
	    </div>


    </div><!-- end ins-->
	<div class="clear"></div>
</div>

<div id="tabs-page_tpl">

    <div class="ins" style="padding-left:20px;padding-bottom:20px;">
    	%page_tpl%
    </div><!-- end ins-->

	<div class="clear"></div>
</div>

<div id="tabs-obj_tpl">
    <div class="ins" style="padding-left:20px;padding-bottom:20px;">
    	%obj_tpl%
    </div><!-- end ins-->

	<div class="clear"></div>
</div>

<div id="tabs-view">

    <div class="ins" style="padding-left:20px;">
	    %text.16% <br /><br />
	    %no_view_classes%

	    <br clear="all" /><br /><br />

	    %text.17% <br /><br />
	    %no_edit_classes%
	    <div class="otstup"></div>

    </div><!-- end ins-->

	<div class="clear"></div>
</div>


</div>

 	<input name="right" type="hidden" value="settings_proc">
 	<input name="parram" id="parramForm" type="hidden" value="">
</form>

END;



?>