<?php

$TEMPLATE['frame'] = <<<END

<input type="hidden" id="%sid%" name="%sname%" value="%value%" onChange="changeFileField(this);" />

<div class="selectfile" id="selectfile_%sid%" style="%sh_selblock%">
	<input id="file_%sid%" name="file_%sname%" type="file" style="width:150px;" value="" onChange="$('#%sid%').val(this.value);" />

    <br/>
    <span class="link" id="selectButton_%sid%" onclick="showElFinder('%sid%');">%text.change%</span>
</div>

<span class="filelist %file.ext%" id="filelist_%sid%" style="%sh_fileblock%">
	<a class="link" %link_type% title="%value%" rel="1">%text.view%</a>

    <a class="down dmini" href="%value%" target="_blank" title="%text.download%" style="%sh_dmini%"></a>
	<a class="del" href="" onClick="clearFileField('%sid%');return false;" title="%text.replace_hint%"></a>

	<div class="clear"></div>
    <span>%file.size%</span>
</span>

END;








?>