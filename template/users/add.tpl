<?php

$TEMPLATE['frame'] = <<<END

    <script type="text/javascript" src="/css_js/users/add.js"></script>


    <div id="error_msg" style="display:none;">%error_msg%</div>
    <div id="error_field" style="display:none;">%error_field%</div>

    <div class="registration">
        <form id="addUserForm" action="/users/add_proc" method="post" enctype="multipart/form-data">


                            <div class="marker">
                                <label for="login">E-mail</label>
                                <input class="input" type="text" id="login" name="login" value="%obj.login%"/>
                                <div class="image"></div>
                                <div class="clear"></div>
                            </div>

                            <div class="marker">
                                <label for="password_reg">Пароль</label>
                                <input class="input" type="password" id="password_reg" name="password" value=""/>
                                <div class="image"></div>
                                <div class="clear"></div>
                            </div>
                            <div class="marker">
                                <label for="password_reg2">Пароль еще раз</label>
                                <input class="input" type="password" id="password_reg2" name="password2" value=""/>
                                <div class="image"></div>

                                <div class="clear"></div>
                            </div>

                            <br /><br />

                            <div class="marker">
                                <label for="name">Имя</label>
                                <input class="input" type="text" id="name" name="name" value="%obj.name%"/>
                                <div class="image"></div>
                                <div class="clear"></div>
                            </div>

                            <div class="marker">
                                <label for="surname">Фамилия</label>
                                <input class="input" type="text" id="surname" name="surname" value="%obj.surname%"/>
                                <div class="image"></div>
                                <div class="clear"></div>
                            </div>

                            <div class="photo_wrap">
                                <label for="avatara">Ваше фото</label>
                                <input class="input" type="hidden" id="avatara2" name="avatara" value="" />
                                <input type="file" id="avatara" name="file_avatara" onChange="$('#avatara2').val(this.value);"/>
                            </div>

                            <div class="clear"></div>

                            <div class="confirm">
                                <input type="checkbox" id="confirm" name="confirm" value="1" %checked%/>
                                <label for="confirm">Согласен с <a href="/offer" target="_blank">условиями регистрации</a></label>
                            </div>

            <br/><br/><br/>

            <div class="clear"></div>





                            <div class="marker">
                                <label for="captcha">Что на картинке?</label>

                                <div style="float:left;margin-right:10px;">
                                <input class="captcha" type="text" id="captcha"  maxlength="4" name="random_image"/>
                                </div>
                                <div style="float:left;">   <img src="/core/random_image" alt=""/></div>
                                <div class="image"></div>
                                <div class="clear"></div>
                            </div>
<br/>

	                        <button>Регистрация</button>

            <input name="back_url" type="hidden" value="%current_url_pn%" />

        </form>

    </div>

END;

?>