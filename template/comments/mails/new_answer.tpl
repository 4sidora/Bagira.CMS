<?php

$TEMPLATE['subject'] = <<<END
Ответ на ваш комментарий
END;

$TEMPLATE['frame'] = <<<END
Здравствуйте, %name%. <br /><br />

Пользователь %username% ответил на ваш комментарий на тему
"<a href="http://%domain%%page.url%" target="_blank">%page.name%</a>":<br />

<i>%comment%</i><br /><br />

<a href="http://%domain%%page.url%#comment%comment_id%" target="_blank">Ответить</a><br /><br />

Текст вашего комментария:<br />
<i>%old_comment%</i><br /><br />

С уважением,  <br />
администрация сайта %site_name%
END;

?>