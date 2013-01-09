<?php

$TEMPLATE['frame'] = <<<END

<div id="question">
    <h2>Вопрос о товаре</h2>
    <a href="#" title="" class="cross"></a>

    <form id="faqForm" action="%pre_lang%/feedback/send_message" method="post">

        <label for="name">Имя</label>
        <input type="text" id="name" name="name" value="%feedback.name%"/>
        <div class="clear15"></div>

        <label for="email">Ваш эл. ящик</label>
        <input type="text" id="email" name="email" value="%feedback.email%"/>
        <div class="clear"></div>


        <h4>Ваш вопрос</h4>
        <textarea cols="auto" rows="0" id="content" name="content">%feedback.content%</textarea>

        <div class="captcha">
            <label for="random_image">Введите код</label>
            <img src="/core/random_image" id="captcha"  alt="captcha" width="120" height="30"/>
            <input id="random_image" maxlength="4" name="random_image"/>
        </div>

        <input type="hidden" name="form_id" value="1613">
        <input type="hidden" name="back_url" value="%current_url_pn%">


        <button>Задать вопрос</button>
        <div class="clear"></div>
    </form>

</div>

<script type="text/javascript" src="/css_js/feedback/faq.js"></script>

END;

?>