<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset=utf-8 />
<title>%title%</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="/css_mpanel/i/favicon.ico" type="image/x-icon"/>
<link href="/css_mpanel/mpanel.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/css_mpanel/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="/css_mpanel/jquery.cookie.js"></script>
<script type="text/javascript" src="/css_mpanel/jquery.rsv.js"></script>

<script type="text/javascript" src="/css_mpanel/jquery.autocomplete-min.js"></script>
<link href="/css_mpanel/jquery.autocomplete.styles.css" rel="stylesheet" type="text/css" />

<link type="text/css" href="/css_mpanel/ui-lightness/jquery-ui-1.7.2.custom.css" rel="stylesheet" />
<script type="text/javascript" src="/css_mpanel/jquery-ui-1.8.13.custom.min.js"></script>
<script src="/css_mpanel/i18n/jquery.ui.datepicker-ru.js"></script>

<link rel="stylesheet" href="/css_mpanel/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
<script src="/css_mpanel/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>

<script type="text/javascript" src="/css_mpanel/lang-ru.js"></script>
<script type="text/javascript" src="/css_mpanel/main.js"></script>

<script type="text/javascript" src="/css_mpanel/ckeditor/ckeditor.js"></script>
</head>


<body>

<input id="selectField" type="hidden" value="%select_field%" />
<input id="focusField" type="hidden" value="%focus_field%" />
<input id="current_url" type="hidden" value="%current_url%" />
<input id="admin_url" type="hidden" value="%admin_url%" />



<div id="baseMessageBox" style="display:none;" title="%mb_title%">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
<div id="baseMessage">%mb_text%</div></p></div>
<div id="baseMessageBox2" style="display:none;" title="%mb_title%"></div>

<div id="objectLinkDiv" title="Выбор страниц" style="display:none;">
	%ldObjectLinks%
	<iframe id="objectLinkTree" src="" frameborder="0" style="width:474px;height:300px;"></iframe>
</div>




<div class="body">
<div class="width_min">

<div class="header">
    <a class="logo" target="_blank" href="http://www.bagira-cms.ru" title="Bagira.CMS"></a>
    %to_site%
    %help_link%


    %mpanel_config%


    <div class="clear"></div>
</div><!-- end .header-->

<div class="container"><div class="cright">

<div class="menu">


    %menu%

    <div class="text">
        %navibar% <span>%header%</span>
        <div class="shaded"></div>
    </div>

    %settings%

</div><!-- end menu-->


<div class="contr">

    <div id="finder"></div>



	%content%


   <div class="clear"></div>

</div><!-- end contr-->
<div class="footer">© 2010-<script>var time=new Date(); var years=  time.getYear(); if (years<200) years += 1900; document.write(years);</script> <a target="_blank" href="http://www.bagira-cms.ru/license" title="Bagira.CMS">Bagira.CMS v1.1.6b</a>.  Все права защищены. </div>
</div></div>


<div class="clear"></div>
</div><!-- end .width_max or width_min--></div>
</body>
</html>