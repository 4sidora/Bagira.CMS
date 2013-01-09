<?php

$TEMPLATE['subject'] = <<<END
Новый комментарий
END;

$TEMPLATE['frame'] = <<<END
Здравствуйте, %name%. <br /><br />

Пользователь %username% оставил новый комментарий на тему
"<a href="http://%domain%%page.url%" target="_blank">%page.name%</a>":<br />

<i>%comment%</i><br /><br />

<a href="http://%domain%%page.url%#comment%comment_id%" target="_blank">Ответить</a><br /><br />

Вы получили это сообщение, так как у вас включено уведомление о новых комментариях.
<a href="http://%domain%/comments/unsubscribe/%index%/%page.id%" target="_blank">Отключить</a><br /><br />

С уважением,  <br />
администрация сайта %site_name%
END;


?>