<?php

$TEMPLATE['frame_add'] = <<<END

<script language="javascript">

jQuery(document).ready(function() {

	$("#addNewFile%sname%").click(function(){
		$("#fileList%sname%").append('<div class="fileListBlockAdd"><input name="file_list_%sname%[]" type="file"><a href="#" onClick="$(this).parent().remove();return false;">удалить</a></div>');

		return false;	});

});

</script>

    <div id="fileList%sname%"></div>

    <a id="addNewFile%sname%" href="#">Прикрепить файл</a>


END;



$TEMPLATE['frame_view'] = <<<END

 %files%

END;


$TEMPLATE['files'] = <<<END

<div class="fileListBlock">

  <a href="%file.url%" target="_blank">
          <img src="/ajex-filemanager/skin/light/ext/%file.ext%.gif" border=0>
  </a>

  <div>
          <a href="%file.url%" target="_blank">%file.name%</a>
          <br /> %file.size%
  </div>

</div>

END;








?>