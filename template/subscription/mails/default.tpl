<?php

$TEMPLATE['name'] = 'Стандартный шаблон для писем';


$TEMPLATE['hello'] = <<<END
    Здравствуйте!<br /><br />
END;

$TEMPLATE['hello_username'] = <<<END
    Доброго дня, %user_name%!<br /><br />
END;


$TEMPLATE['frame'] = <<<END
	<h1>%release.name%</h1>

    %hello%

	%release.message%

    %list%

<p><br/></p>

<p>
---<br/>
С наилучшими пожеланиями!<br/>

<hr/>
<small>
    Если вы хотите отписаться от рассылки новостей, нажмите на
    <a href="http://%domain_name%/subscription/unsubscribe/%subscribe.id%/%user.id%">эту ссылку</a>.     <br/>
    Если письмо отображается не корректно, <a href="http://%domain_name%/subscription/view/%release.id%">нажмите сюда</a>.

</small>
END;

$TEMPLATE['frame_list'] = <<<END

    <br /><br />
	%list%

END;

$TEMPLATE['list'] = <<<END

    <b>%obj.name%</b> <br />  <br />

END;

$TEMPLATE['list'] = <<<END

    <a href="%obj.url%" target="_blank">%obj.name%</a> |
    <b>%core.fdate(d.m.Y, %obj.publ_date%)%</b>

    <br clear="all"/>  <br />

    %obj.notice%   <br clear="all"/><br />

END;




?>