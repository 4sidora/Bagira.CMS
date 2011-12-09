<?php


$TEMPLATE['main'] = <<<END

<link type="text/css" rel="stylesheet" href="/css_mpanel/tree/style%style_prefix%.css">
<link type="text/css" rel="stylesheet" href="/css_mpanel/tree/tree%style_prefix%.css">

<script type="text/javascript" src="/css_mpanel/tree/jquery.tree.js"></script>
<script type="text/javascript" src="/css_mpanel/tree/tree.js"></script>

<div id="root_id" class="hide">%root_id%</div>
<div id="act_link" class="hide">%main_url%%act_link%</div>
<div id="remove_link" class="hide">%main_url%%remove_link%</div>
<div id="load_link" class="hide">%main_url%%load_link%</div>

<div id="isDragged" class="hide">%isDragged%</div>
<div id="isChangeActive" class="hide">%isChangeActive%</div>
<div id="isShowRight" class="hide">%isShowRight%</div>
<div id="isEditable" class="hide">%isEditable%</div>

<input id="del_title" type="hidden" value="%del_title%" />
<input id="del_text" type="hidden" value="%del_text%" />

<div style="width:%width%px;overflow:hidden;">

   <div id="edits" style="left:%left%px;width:%width2%px;">
   		%rights%
   		<div id="delZagl" class="zaglushka" style="display:none;width:%zagl_width%px;">&nbsp;</div>
   </div>

   <div id="basic_html" style="margin-left:10px;">

      %frame_items%

   </div>

</div>
   <div id="hint"></div>
END;

$TEMPLATE['frame_items'] = <<<END
  <ul style="width:100%;">
     %items%
  </ul>
END;
                      // border:1px solid blue;
$TEMPLATE['items'] = <<<END
<li id="phtml_%item.id%_%item.parent_id%" class="main_line%close%" name="%item.id%" rel="%item.parent_id%">


    <input type="hidden" id="get_url_%item.id%_%item.parent_id%" value="%obj.url%">
    <div class="active_div">
         <img name="%item.active%" class="activate" src="%obj.ico%" border=0>
    </div>
    <a href="%main_url%%item.url%" id="aline_%item.id%%item.parent_id%" class="main_link">%item.name% <i>%item.notice%</i></a>
    <div id="aline2_%item.id%%item.parent_id%" class="addit_line%plus_table%"></div>

    %sub_items%
</li>
END;



$TEMPLATE['item_right'] = <<<END
<a href="" name="%url%" class="ledit%hide_in_root%" title="%title%">
   <div class="header_tree">
      <font class="%image_style%">%title%</font>
   </div>
</a>
END;

$TEMPLATE['item_right_url'] = <<<END
<a href="" class="ledit%hide_in_root%" title="%title%" target="_blank" rel="getUrl">
   <div class="header_tree">
      <font class="%image_style%">%title%</font>
   </div>
</a>
END;

$TEMPLATE['item_right_del'] = <<<END
<a href="#" name="%url%" class="ledit%hide_in_root%" id="delButton" title="%title%">
   <div class="header_tree">
      <font class="%image_style%">%title%</font>
   </div>
</a>
END;

$TEMPLATE['item_right_list'] = <<<END
<a href="" name="%url%" class="ledit%hide_in_root%" title="%title%">
   <div class="header_tree2">
      <font class="%image_style%">%title%</font>
   </div>
</a>
%tree_list%
<span class="tree_list " name="%list_id%"></span>
END;

$TEMPLATE['tree_list'] = <<<END
<div style="position:relative;float:left;">
	<div id="%list_id%" class="popdiv_tree_list"><b></b>
		%list_html%
	</div>
</div>
END;

$TEMPLATE['item_right_null'] = <<<END
<div class="zaglushka">&nbsp;</div>
END;

?>