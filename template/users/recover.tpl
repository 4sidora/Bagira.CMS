<?php

$TEMPLATE['frame'] = <<<END

<div id="alert_msg" style="display:none;">%alert_msg%</div>
<div id="alert_field" style="display:none;">%alert_field%</div>


<div class="registration">

        <b>Чтобы сбросить пароль, введите полный адрес электронной почты, используемый Вами для входа в аккаунт.</b>

        <br/><br/>
    <form id="recoverForm" action="%pre_lang%/users/recover_passw" method="post">

        <div class="marker">
                                <label for="login_or_email">Ваш эл.ящик</label>
                                <input class="input" type="text" id="login_or_email" name="login_or_email" value="%login_or_email%"/>
                                <div class="image"></div>
                                <div class="clear"></div>
        </div>


        <div class="marker">
                            	<label for="captcha">Что на картинке</label>

                            <div style="float:left;margin-right:10px;">
                                <input class="captcha" type="text" id="captcha" name="random_image" maxlength="4"/>
                            </div>
                            <div style="float:left;">
                                <img src="/core/random_image" id="captcha_img" style="float:left;" />
                                <a href="#" id="captcha_change"  style="float:left;margin-left:10px;">показать<br/>другую картинку</a>
                            </div>
                        <div class="clear"></div>

         </div>

         <br/>
        <b>Если у вас нет аккаунта на нашем сайте, можете <a href="%pre_lang%/users/add">создать его сейчас</a>.</b>

        <br/><br/>
        
	    <button>Отправить</button>
    </form>
</div>

<script type="text/javascript" src="/css_js/users/recover.js"></script>

END;


?>