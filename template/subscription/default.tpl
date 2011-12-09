<?php

$TEMPLATE['frame'] = <<<END
<form id="subscribeForm" action="/subscription/subscribe" method="post">

    <div class="subscribe">
        <h4><b>Подписка на рассылку</b></h4>

        %list%

        <label for="emailSubscr">Ваша электроная почта</label><br/>
        <input name="email" id="emailSubscr" class="subscribe_text" type="text"/>
        <button>Подписаться</button>

        <input name="back_url" type="hidden" value="%current_url%">
    </div>

</form>
END;

$TEMPLATE['list'] = <<<END
    <input name="subscribes[%obj.id%]" id="subscription%obj.id%" type="checkbox" value="%obj.id%">
    <label for="subscription%obj.id%">%obj.name%</label><br/>
END;

$TEMPLATE['empty'] = <<<END

Подписка на рассылку пока не доступна.

END;

?>