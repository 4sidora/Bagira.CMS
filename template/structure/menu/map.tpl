<?php

$TEMPLATE[1]['frame'] = <<<END
<ul id="sitemap">
	%list%
</ul>
END;

$TEMPLATE[1]['list'] = <<<END
<li>
	<a href="%obj.url%" %obj.target%>%obj.name%</a>
	%sub_menu%
</li>
END;

$TEMPLATE[1]['list_active'] = <<<END
<li>
	<a href="%obj.url%" %obj.target%><b>%obj.name%</b></a>
	%sub_menu%
</li>
END;






?>