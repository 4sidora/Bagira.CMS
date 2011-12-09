<?php


$TEMPLATE['main'] = <<<END
<link type="text/css" rel="stylesheet" href="/css_mpanel/fieldsList.css">
<script type="text/javascript" src="/css_mpanel/fieldsList.js"></script>

<div id="divForm" title=""></div>

<div class="otstup_mini"></div>
<div style="margin-left:30px;">%button_new_group%</div>

<div class="otstup_mini"></div>

<ul id="groupsSortable">
	     %frame_items%
</ul>

<div class="otstup"></div>
<div class="otstup"></div>
END;



$TEMPLATE['groups'] = <<<END

<li id="group_%item.id%" name="%item.id%">
	<div class="title">

		<span class="%sh%">%item.name% (%item.sname%)</span>

		<div class="groupEdits">
			%item.right%
		</div>

	</div>

        %sub_items%
</li>
END;


$TEMPLATE['frame_items'] = <<<END

<ul class="fieldsSortable" id="fgroup_%item.id%" name="%item.id%">
     %items%
</ul>
END;

$TEMPLATE['field_edit'] = <<<END
<li id="field_%item.id%" name="%item.id%">

	<div class="titl">
		<i>%star%</i> <a href="javascript:%item.url%" class="%sh%">%item.name%</a>
	</div>

	<div class="infoBlock">
	    <span><div class="%sh%">%item.sname%</div></span>
	    <span><div class="%sh%">%item.type%</div></span>
	    <div class="edits">%item.right%</div>
	</div>
</li>
END;

$TEMPLATE['field_not_edit'] = <<<END
<li id="field_%item.id%" name="%item.id%">

	<div class="titl">
		<i>%star%</i> <div class="%sh%">%item.name%</div>
	</div>

	<div class="infoBlock">
	    <span><div class="%sh%">%item.sname%</div></span>
	    <span><div class="%sh%">%item.type%</div></span>
	</div>
</li>
END;


$TEMPLATE['separator'] = <<<END

<li id="field_%item.id%" name="%item.id%">

	<div class="titl">
		<div class="%sh%">%item.name%</div>
	</div>
	<div class="infoBlock">
        <span><div class="%sh%">- - - - -</div></span>
	    <span><div class="%sh%">- - - - -</div></span>
		<div class="edits">%item.right%</div>
	</div>
</li>

END;


$TEMPLATE['upd_separator'] = <<<END
	<div class="titl">
		<div class="%sh%">%item.name%</div>
	</div>
	<div class="infoBlock">
        <span><div class="%sh%">- - - - -</div></span>
	    <span><div class="%sh%">- - - - -</div></span>
		<div class="edits">%item.right%</div>
	</div>
END;

$TEMPLATE['upd_field_edit'] = <<<END
	<div class="titl">
		<i>%star%</i> <a href="javascript:%item.url%" class="%sh%">%item.name%</a>
	</div>
	<div class="infoBlock">
	    <span><div class="%sh%">%item.sname%</div></span>
	    <span><div class="%sh%">%item.type%</div></span>
	    <div class="edits">%item.right%</div>
	</div>
END;

$TEMPLATE['upd_field_not_edit'] = <<<END
	<div class="titl">
		<i>%star%</i> <div class="%sh%">%item.name%</div>
	</div>
	<div class="infoBlock">
	    <span><div class="%sh%">%item.sname%</div></span>
	    <span><div class="%sh%">%item.type%</div></span>
	</div>
END;

$TEMPLATE['new_group'] = <<<END
<li id="group_%item.id%" name="%item.id%">
	<div class="title">
		<span class="%sh%">%item.name% (%item.sname%)</span>
		<div class="groupEdits">%item.right%</div>
	</div>
    <ul class="fieldsSortable" id="fgroup_%item.id%" name="%item.id%"></ul>
</li>
END;

$TEMPLATE['upd_group'] = <<<END
		<span class="%sh%">%item.name% (%item.sname%)</span>
		<div class="groupEdits">%item.right%</div>
END;


$TEMPLATE['item_right'] = <<<END
<a href="%right_url%" class="ledit">
   <div class="header_tree">
      <font class="%image_style%">%title%</font>
   </div>
</a>
END;

$TEMPLATE['item_right_java'] = <<<END
<a href="javascript:%right_url%" class="ledit">
   <div class="header_tree">
      <font class="%image_style%">%title%</font>
   </div>
</a>
END;

$TEMPLATE['item_right_null'] = <<<END
<div class="zaglushka">&nbsp;</div>
END;


?>