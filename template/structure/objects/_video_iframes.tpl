<?php

$TEMPLATE['youtube'] = <<<END
<iframe width="560" 
		height="315" 
		src="http://www.youtube.com/embed/%video.id%%video.hash%" 
		frameborder="0" allowfullscreen>
</iframe>
END;

$TEMPLATE['vimeo'] = <<<END
<iframe src="http://player.vimeo.com/video/%video.id%?title=0&amp;byline=0&amp;portrait=0&amp;color=e5bd4b" 
		width="500" 
		height="281" 
		frameborder="0" 
		webkitAllowFullScreen mozallowfullscreen allowFullScreen>
</iframe> 
END;

$TEMPLATE['empty'] = <<<END

END;

?>