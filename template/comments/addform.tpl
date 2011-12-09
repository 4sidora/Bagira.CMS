<?php

$TEMPLATE['frame'] = <<<END

<form action="%pre_lang%/comments/add" method="post" id="commentForm">

    <label for="comment_text" class="commtarea">Ваш комментарий</label><br/>
    <textarea cols="auto" rows="0" id="comment_text" name="text"></textarea>
                                
    <label for="comment_email">Ваша электронная почта</label>
    <input type="text" id="comment_email" name="email" value="%email%"/>


    <div class="clear"></div>
    <label for="comment_username">Имя</label>
    <input type="text" id="comment_username" name="username" value="%username%"/>

    %capcha%

    <input type="checkbox" id="comment_send" name="send_email" value="1"/>
    <label for="comment_send">Следить за комментариями</label>

    <div class="clear"></div>
    <button>Комментировать</button>

    <input type="hidden" name="obj_id" value="%page_id%">
    <input type="hidden" name="parent_id" id="comment_parent_id" value="0">
    <input type="hidden" name="back_url" value="%current_url_pn%">


</form>

END;

$TEMPLATE['capcha'] = <<<END
    <div class="clear"></div>
    <div class="captcha">
        <label for="random_image">Введите код</label>
        <img src="/core/random_image" id="captcha"  alt="captcha" width="120" height="30"/>
        <input id="random_image" maxlength="4" name="random_image"/>
    </div>
END;

$TEMPLATE['no_auth'] = <<<END

Чтобы оставить комментарий вам нужно авторизоваться или <a href="%pre_lang%/users/add">зарегистрироваться</a>.

END;

?>