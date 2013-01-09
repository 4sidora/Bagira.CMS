<?php

$TEMPLATE['frame'] = <<<END

    <div id="alert_msg" style="display:none;">%alert_msg%</div>
    <div id="alert_field" style="display:none;">%alert_field%</div>

    <div class="registration">

        <p>Привет, %obj.first_name%!</p>

        <p>Для подтверждения регистрации необходимо, чтобы вы приняли условия регистрации, а так же указали свой e-mail.</p>
        <p>Если вы по каким-либо причинам не хотите этого делать, вы можете отказаться от регистрации, просто закрыв это окно.</p>

        <form id="socialAuthForm" action="/users/social_auth_confirm" method="post" enctype="multipart/form-data">

            %email_block%

            %confirm_block%

            <button>Завершить регистрацию</button>
        </form>

    </div>

    <script type="text/javascript" src="/css_js/jquery.js"></script>
    <script type="text/javascript" src="/css_js/users/social_auth_confirm.js"></script>

END;

$TEMPLATE['email'] = <<<END

            <div class="marker">
                <label for="email">E-mail</label>
                <input class="input" type="text" id="email" name="email" value="%obj.email%"/>
                <div class="clear"></div>
            </div>
END;

$TEMPLATE['confirm'] = <<<END

            <div class="clear"></div>

            <div class="confirm">
                <input type="checkbox" id="confirm" name="confirm" value="1"/>
                <label for="confirm">Согласен с <a href="/offer" target="_blank">условиями регистрации</a></label>
            </div>

END;

?>