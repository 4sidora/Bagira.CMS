<?php

$TEMPLATE['frame'] = <<<END

<div id="feedback">
    <h2>Отзыв о товаре</h2>
    <a href="#" title="" class="cross"></a>

    <form action="%pre_lang%/feedback/send_message" method="post" id="reviewForm">

        <label for="name">Ваше имя</label>
        <input type="text" id="name" name="name" value="%feedback.name%"/>
        <div class="clear"></div>

        <label for="email">Ваш эл.ящик</label>
        <input type="text" id="email" name="email" value="%feedback.email%"/>
        <div class="clear"></div>

        <span>Насколько вы довольны покупкой?</span>
        <div id="reviewRate"></div>
        <div class="clear"></div>

        <h4>Комментарий</h4>
        <p>Напишите, пожалуйста, ваши впечатления от покупки. Подробно опишите все достоинства и недостатки товара. Короткие, несодержательные и отзывы с нецензурными выражениями опубликованы не будут.</p>
        <textarea cols="auto" rows="0" id="content" name="content"></textarea>

        <div class="captcha">
            <label for="random_image">Введите код</label>
            <img src="/core/random_image" id="captcha"  alt="captcha" width="120" height="30"/>
            <input id="random_image" maxlength="4" name="random_image"/>
        </div>

        <input type="hidden" name="section_id" value="%page_id%">
        <input type="hidden" name="user_rate" id="rate" value="3">
        <input name="form_id" type="hidden" value="1745">
        <input name="back_url" type="hidden" value="%current_url_pn%">


        <button>Оставить отзыв</button>
        <div class="clear"></div>
    </form>
</div>

<script type="text/javascript" src="/css_js/feedback/review.js"></script>

END;

?>