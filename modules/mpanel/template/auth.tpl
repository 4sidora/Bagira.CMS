<?php

$TEMPLATE['frame'] = <<<END
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset=utf-8 />
<title>%title%</title>
<link rel="stylesheet" type="text/css" href="/css_mpanel/mpanel.css"/>
<link rel="icon" href="/css_mpanel/i/favicon.ico" type="image/x-icon"/>
<script type="text/javascript" src="/css_mpanel/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="/css_mpanel/auth.js"></script>
<!--[if lt IE 8]><style>.swc1 {text-align: center;}.swc2, .swc3 {vertical-align: middle;}.swc2 {display: inline;_height: 0;	zoom: 1;text-align: left; width:100%;}.swc3 {height: 100%; zoom: 1;}</style><![endif]-->
<style> html, body, .swc0, .swc1{	height: 100%;} </style>
</head>

<body>
<div class="swc0"><div class="swc1"><div class="swc2">
<div class="width_auth"><i></i><ins></ins><b></b><code></code>

<a class="logo" target="_blank" href="http://www.bagira-cms.ru" title="Bagira.CMS" tabindex="5"></a><span class="clear"></span>


<form id="authForm" action="%url%" method="post">

	<label id="logintext" for="auth_login">E-mail</label>
	<input type="text" name="login" id="auth_login" tabindex="1" />

	<label id="passwtext" for="auth_password">Пароль <strong>(<a href="/users/recover" tabindex="5">напомнить</a>)</strong></label>
	<input type="password" name="passw" id="auth_password" tabindex="2" />

    <input type="hidden" name="enter"  value="1" />
    <input type="hidden" id="error"  value="%error%" />

	<div class="clear"></div>

	<button id="send_" tabindex="4" type="submit">Вход</button>

    <div class="remember">
        <input type="checkbox" name="remember_me" value="1" id="remember_me" tabindex="3"><label for="remember_me">Запомнить меня</label>
    </div>

</form>


<div class="clear"></div>
</div><!-- end .width_auth-->
</div><span class="swc3"></span></div></div>

</body>
</html>

END;

$TEMPLATE['frame_no_admin'] = <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>%title%</title>
<META http-equiv=content-type content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="/css_mpanel/mpanel.css"/>
<link rel="icon" href="/css_mpanel/i/favicon.ico" type="image/x-icon"/>
<!--[if lt IE 8]><style>.swc1 {text-align: center;}.swc2, .swc3 {vertical-align: middle;}.swc2 {display: inline;_height: 0;	zoom: 1;text-align: left; width:100%;}.swc3 {height: 100%; zoom: 1;}</style><![endif]-->
<style> html, body, .swc0, .swc1{	height: 100%;} </style>
</head>

<body>
<div class="swc0"><div class="swc1"><div class="swc2">
<div class="width_auth"><i></i><ins></ins><b></b><code></code>

<a class="logo" target="_blank" href="http://www.bagira-cms.ru" title="Bagira.CMS"></a><span class="clear"></span>


    %hello% %user%!

	%big_text% <a href="%exit_url%">%exit_text%</a>.



<div class="clear"></div>
</div><!-- end .width_auth-->
</div><span class="swc3"></span></div></div>

</body>
</html>
END;


?>