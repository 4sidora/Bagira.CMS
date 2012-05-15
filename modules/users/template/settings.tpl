<?php

$TEMPLATE['frame'] = <<<END
<form id="changeForm" name="changeForm" action="" method="post" enctype="multipart/form-data">

<div id="tabs">

	<ul>
    	<li><a href="#tabs-main">%text.1%</a><ins></ins></li>
        <li><a href="#tabs-social">%text.36%</a><ins></ins></li>
    </ul>


<div id="tabs-main">
    <div class="ins" style="padding-left:20px;">

	    %reg%

	    <div class="otstup"></div>

	    %activation%

        <div class="otstup"></div>

	    %confirm%

	    <div class="otstup"></div>

        %ask_email%

	    <div class="otstup"></div>
        
	    <!---
	    %text.10%
	   	<input class="input" type="text" name="errorCountCapcha" id="errorCountCapcha" value="%errorCountCapcha%" style="width:20px;">
	   	%text.30%

	    <div class="otstup"></div>
	                 --->
	    %text.29%
	   	<input class="input" type="text" name="errorCountBlock" id="errorCountBlock" value="%errorCountBlock%" style="width:20px;">
	   	%text.30%

	    <div class="otstup"></div>

    </div><!-- end ins-->
	<div class="clear"></div>
</div>

<div id="tabs-social">
    <div class="ins">

        <div class="social-auth-block sh-social-block">
            <div class="fieldBox first">
                %facebook_bool%
                <div class="otstup"></div>
                <a href="https://developers.facebook.com/apps" class="social-fb" target="_blank">%text.37%</a>
            </div>

            <div class="fieldBox slide">
                <label for="facebook_id" class=" chek" title=""><b></b>%text.38%</label>
                <input class="input" type="text" name="facebook_id" id="facebook_id" value="%facebook_id%">
            </div>

            <div class="fieldBox slide">
                <label for="facebook_secret" class=" chek" title=""><b></b>%text.39%</label>
                <input class="input" type="text" name="facebook_secret" id="facebook_secret" value="%facebook_secret%">
            </div>

            <div class="clear"></div>
        </div>

        <div class="social-auth-block sh-social-block">
            <div class="fieldBox first">
                %twitter_bool%
                <div class="otstup"></div>
                <a href="https://dev.twitter.com/apps/new" class="social-tw" target="_blank">%text.37%</a>
            </div>

            <div class="fieldBox slide">
                <label for="twitter_id" class=" chek" title=""><b></b>%text.38%</label>
                <input class="input" type="text" name="twitter_id" id="twitter_id" value="%twitter_id%">
            </div>

            <div class="fieldBox slide">
                <label for="twitter_secret" class=" chek" title=""><b></b>%text.39%</label>
                <input class="input" type="text" name="twitter_secret" id="twitter_secret" value="%twitter_secret%">
            </div>

             <div class="clear"></div>
        </div>



        <div class="social-auth-block sh-social-block">
            <div class="fieldBox first">
                %vk_bool%
                <div class="otstup"></div>
                <a href="http://vk.com/editapp?act=create" class="social-vk" target="_blank">%text.37%</a>
            </div>

            <div class="fieldBox slide">
                <label for="vk_id" class=" chek" title=""><b></b>%text.38%</label>
                <input class="input" type="text" name="vk_id" id="vk_id" value="%vk_id%">
            </div>

            <div class="fieldBox slide">
                <label for="vk_secret" class=" chek" title=""><b></b>%text.39%</label>
                <input class="input" type="text" name="vk_secret" id="vk_secret" value="%vk_secret%">
            </div>

            <div class="clear"></div>
        </div>


        <div class="social-auth-block sh-social-block">
            <div class="fieldBox first ok">
                %ok_bool%
                <div class="otstup"></div>
                <a href="http://dev.odnoklassniki.ru/wiki/pages/viewpage.action?pageId=13992188" class="social-ok" target="_blank">%text.37%</a>
            </div>

            <div class="fieldBox slide">
                <label for="ok_id" class=" chek" title=""><b></b>%text.38%</label>
                <input class="input" type="text" name="ok_id" id="ok_id" value="%ok_id%">
            </div>

            <div class="fieldBox slide">
                <label for="ok_public" class=" chek" title=""><b></b>%text.46%</label>
                <input class="input" type="text" name="ok_public" id="ok_public" value="%ok_public%">
            </div>

            <div class="fieldBox slide">
                <label for="ok_secret" class=" chek" title=""><b></b>%text.39%</label>
                <input class="input" type="text" name="ok_secret" id="ok_secret" value="%ok_secret%">
            </div>

            <div class="clear"></div>
        </div>


        <div class="social-auth-block">
            <div class="fieldBox first">
                %yandex_bool%
            </div>
            <div class="clear"></div>
        </div>


        <div class="fieldBox first">
            %google_bool%
        </div>

        <div class="clear"></div>
    </div><!-- end ins-->
	<div class="clear"></div>
</div>



</div>

 	<input name="parram" id="parramForm" type="hidden" value="">
 	<input name="right" type="hidden" value="settings_proc">
</form>

<script language="javascript" src="/css_mpanel/users_settings.js"></script>

END;


?>