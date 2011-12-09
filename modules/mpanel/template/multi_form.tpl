<?php


$TEMPLATE['form_frame'] = <<<END
<div class="onetabs">
    <div class="ins" style="padding-left:20px;">

<form id="%form_name%Form" name="%form_name%Form" action="" method="post" enctype="multipart/form-data">

 	%html%

 	<input name="parram" id="parramForm" type="hidden" value="">
 	<input name="right" type="hidden" value="%mright%">
</form>

	</div><!-- end ins-->
    <div class="clear"></div>
</div><!-- end tab -->
END;

$TEMPLATE['frame'] = <<<END
<script language="javascript">
var new_field_%form_name% = '';
jQuery(document).ready(function() {

    new_field_%form_name% = '<div class="el_lines">'+$("#mainlist_%form_name% > .el_lines:last").html()+'</div><div class="clear"></div>';
    $("#mainlist_%form_name% .new").die();
    $("#mainlist_%form_name% .new").live('change', function(){
    	if ($(this).val() != '') {
			var num = parseInt($("#newobjs_%form_name%").val()) + 1;
			var re = /new1/g;
			new_form = new_field_%form_name%.replace(re, 'new'+num);
			$("#mainlist_%form_name%").append(new_form);
			$("#newobjs_%form_name%").val(num);
			$(this).removeClass("new").addClass('oldnew');
        }
    });
});
</script>

<div class="el_lines" style="height:20px;">
     %columns%
     %column_del%
</div>

<div id="mainlist_%form_name%">

    %lines%

</div>
 	<input id="newobjs_%form_name%" type="hidden" value="1">
 	%params%
END;


$TEMPLATE['params'] = <<<END
<input name="params_%form_name%[]" type="hidden" value="%val%">
END;


$TEMPLATE['colums'] = <<<END
<div style="float:left; width:%width%px;"><b>%title%</b></div>
END;

$TEMPLATE['colum_del'] = <<<END
<div style="width:15px; margin-left:14px; float:left;"><b>X</b></div>
END;

// Подсказка
$TEMPLATE['acronym'] = <<<END
	<acronym title="%hint%" style="cursor:help;">%title% </acronym>
END;


$TEMPLATE['lines'] = <<<END
<div class="el_lines">
	%object% %del_check%
</div>
<div class="clear"></div>
END;

$TEMPLATE['del_check'] = <<<END
<div style="width:20px;margin-left:10px;" class="cell_el">
	<input name="delete_%form_name%_%obj_id%" type="checkbox" class="delete_%form_name%" value="1">
</div>
END;


$TEMPLATE['field_frame'] = <<<END
<div style="width:%width%px;height:auto !important;min-height:20px;display:block;" class="cell_el">%content%</div>
END;

$TEMPLATE['field'] = <<<END
<input class="inputfield%new%" type="text" name="obj%form_name%[%obj_id%][%field.sname%]" id="%field.sname%_%obj_id%" value="%field.value%" style="width:%width%px;">
END;

?>