<?php

$TEMPLATE['frame_form'] = <<<END
<div class="enter whiteshader"><span>Войти в личный кабинет</span></div>

<div id="autorisation">
	<h2>Войти на сайт</h2><br/>
	<a href="#" title="" class="cross"></a>

    <form id="authForm" action="%pre_lang%/users/auth" method="post">
       		<span>&nbsp;</span>
        
            <label for="auth_login">Ваш эл. ящик</label>
            <input type="text" id="auth_login" name="login"/>
            <div class="clear"></div>

            <label for="auth_password">Пароль</label>
            <input type="password" id="auth_password"  name="passw"/>
             <div class="clear"></div>

    		<button>Войти</button>

            <a href="%pre_lang%/users/recover" title="">Забыли пароль?</a>
            <input name="back_url" type="hidden" value="%current_url_pn%" />
    </form>
    Впервые на «B Mart»?<br/>Зарегистрируйтесь прямо сейчас
    <a href="%pre_lang%/users/add" title="" class="button">Регистрация</a>
<div class="clear"></div>
</div>

%auth_error%

END;

$TEMPLATE['frame_account'] = <<<END
<a href="%pre_lang%/users/edit" class="user">%user_name%</a>
<a onclick="document.auth_form.submit()" class="exit">выход</a>

<form name="auth_form" action="%pre_lang%/users/logout" method="post">
	<input name="back_url" type="hidden" value="%current_url_pn%" />
</form>
END;

?>