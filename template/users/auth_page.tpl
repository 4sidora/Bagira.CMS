<?php

$TEMPLATE['frame_form'] = <<<END

<div class="registration">
    <b>Проверьте раскладку клавиатуры, не нажата ли клавиша «Caps Lock», и
попробуйте ввести логин и пароль еще раз.</b>
                    <form id="authFormError" action="%pre_lang%/users/auth" method="post">
                    		<div class="marker">
                                <label for="login_er">Ваш эл.ящик</label>
                                <input class="input" type="text" id="login_er" name="login" tabindex="1" value="%login%"/>
                                <div class="clear"></div>
                            </div>
                            <div class="marker">
                                <label for="password_er">Пароль</label>
                                <input class="input" type="password" id="password_er" name="passw" tabindex="2"/>
                                <div class="clear"></div>
                            </div>
                            <b>
                                Если у вас нет аккаунта на нашем сайте, можете
                                <a href="%pre_lang%/users/add">создать его сейчас</a>.<br/>
                                <a href="%pre_lang%/users/recover">Забыли пароль</a>?
                            </b>
	                        <button>Вход</button>
                    </form>
                </div>

END;

$TEMPLATE['auth_error'] = <<<END

END;

?>