<?php

$TEMPLATE['frame'] = <<<END

<script language="javascript">

jQuery(document).ready(function() {

    var rules = [];
    rules.push("required,class_name,%text.5%");
    rules.push("required,sname,%text.6%");

    $("#changeForm").RSV({
         customErrorHandler: ShowMessageRVS,
         rules: rules
    });

    $("#class_name").live('change', function(){
    	Translit("#class_name", "#sname");
    });

    $("#class_name").focus();

});

</script>


<div class="onetabs">
    <div class="ins">

<form id="changeForm" name="changeForm" action="" method="post">

	<div class="fieldBox">
 		<label for="class_name" class="chek" title=""><b></b>%text.1%</label>
   		<input class="input" type="text" name="class_name" id="class_name" value="%obj.class_name%">
 	</div>

 	<div class="fieldBox">
 		<label for="sname" class="chek" title=""><b></b>%text.2%</label>
   		<input class="input" type="text" name="sname" id="sname" value="%obj.sname%">
 	</div>

    %page_fields%

 	<div class="otstup"></div>



 	<div class="fieldBox">
    	<label for="" class="" ><b></b></label>
     	%is_list%
    </div>

    <div class="fieldBox">
    	<label for="" class="" ><b></b></label>
     	%system%
    </div>





 	<input name="parram" id="parramForm" type="hidden" value="">
 	<input name="right" type="hidden" value="%right%">
 	<input name="obj_id" type="hidden" value="%obj.id%">
</form>

    </div><!-- end ins-->
    <div class="clear"></div>
</div><!-- end tab -->



%fields%

END;

$TEMPLATE['page_fields'] = <<<END

	<div class="fieldBox">
 		<label for="text" class="dotted" title="%text.10%"><b></b>%text.8%</label>
   		<input class="input" type="text" name="text" id="text" value="%obj.text%">
 	</div>

 	<div class="fieldBox">
 		<label for="class_list" class="dotted" title="%text.11%"><b></b>%text.9%</label>
   		%class_list%
 	</div>

	<div class="fieldBox">
		<label for="template_list1" class="dotted" title="%text.13%"><b></b>%text.12%</label>
		%template_list1%
	</div>

	<div class="fieldBox">
		<label for="template_list2" class="dotted" title="%text.15%"><b></b>%text.14%</label>
		%template_list2%
	</div>

		

		
END;

$TEMPLATE['user_fields'] = <<<END

	<div class="fieldBox">
 		<label for="text" class="dotted" title="%text.10%"><b></b>%text.8%</label>
   		<input class="input" type="text" name="text" id="text" value="%obj.text%">
 	</div>

 	<div class="clear"></div>
END;

?>