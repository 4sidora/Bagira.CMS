<?php

$TEMPLATE['frame'] = <<<END

<input type="hidden" id="%sname%" name="%sname%" value="%value%" onChange="changeFileField(this);" />

<div class="selectfile" id="selectfile_%sname%" style="%sh_selblock%">
	<input id="file_%sname%" name="file_%sname%" type="file" value="" onChange="$('#%sname%').val(this.value);" />
    %text.or%  <span class="link" id="selectButton_%sname%" onclick="showElFinder('%sname%');">%text.change%</span>
</div>

<span class="filelist %file.ext%" id="filelist_%sname%" style="%sh_fileblock%">
	<a class="link" %link_type%>%value%</a>
	<a class="del" href="" onClick="clearFileField('%sname%');return false;"></a>
	<div class="clear"></div>
    <span>%file.size%</span>
    <b><a class="downl" href="%value%" target="_blank">%text.download%</a> %text.or%
    <a href="" id="selectFile_%sname%" onClick="$('#selectfile_%sname%').show();$('#filelist_%sname%').hide();return false;" class="change">%text.replace%</a></b>
</span>

END;








?>