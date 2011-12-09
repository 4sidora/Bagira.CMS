<?php



$TEMPLATE['frame'] = <<<END

     %list%

END;


$TEMPLATE['frame_list'] = <<<END
<div id="gallerywrap">
  <b class="newsb1">&nbsp;</b>
  <b class="newsb2">&nbsp;</b>
    <div id="gallery">
%list%
    </div>
   <b class="newsb2">&nbsp;</b>
   <b class="newsb1">&nbsp;</b>
</div>
END;



$TEMPLATE['list'] = <<<END
<a href="%obj.image%" title="%obj.name%">%structure.getProperty(image, %obj.id%, photolistsmall)%</a>
END;

?>