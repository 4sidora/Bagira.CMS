<?php

$TEMPLATE['frame'] = <<<END

<div id="alert_msg" style="display:none;">%alert_msg%</div>
<div id="alert_field" style="display:none;">%alert_field%</div>


 <div class="registration">
        <form  id="passwordForm" action="/users/change_password_proc" method="post" enctype="multipart/form-data">


                            <div class="marker">
                                <label for="current_password">Текущий пароль:</label>
                                <input class="input" id="current_password" type="password" name="current_password" value="" />
                                <div class="image"></div>
                                <div class="clear"></div>
                            </div>

                            <div class="marker">
                                <label for="password">Новый пароль:</label>
                                <input class="input" id="password" type="password" name="password" value=""/>
                                <div class="image"></div>
                                <div class="clear"></div>
                            </div>
                            <div class="marker">
                                <label for="password2">Повторите пароль:</label>
                                <input class="input" id="password2" type="password" name="password2" value=""/>
                                <div class="image"></div>
                                <div class="clear"></div>
                            </div>

            <button style="float:left;">Сохранить</button>


            <a href="/users/edit" class="right_link">Отмена</a><br />

        </form>

 </div>


<script type="text/javascript" src="/css_js/users/changePassword.js"></script>

END;


$TEMPLATE['frame_ok'] = <<<END
<div class="registration">
   Ваш пароль был изменен! Спасибо!
    <br /><br />
    <a href="/users/edit">Вернуться в личный кабинет</a>
</div>
END;





?>