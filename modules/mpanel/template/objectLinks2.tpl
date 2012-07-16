<?php

$TEMPLATE['frame'] = <<<END

    <input class="input_min_1 font_gray findObjectLinks" type="text" style="width:%ol_width%px;" id="%field_id%" value="Поиск в дереве" />
    <span id="%field_id%" class="tree addObjectLink"></span>

<input name="%field_name%[]" type="hidden" value="">
 	<ul class="objectsLink" id="objectsLinkList_%field_id%" rel="%field_name%">%list%</ul>

END;


$TEMPLATE['object_link'] = <<<END
    <li id="objectsLinkList_%id%%field_id%" name="%id%">
 		<a href="#" id="%id%%field_id%" class="close_image delObjectLink"></a>
   		<span>%name%</span>
   		<input name="%field_name%[]" type="hidden" value="%id%">
    </li>
END;

$TEMPLATE['new_object_link'] = <<<END
	<li id="objectsLinkList_%id%%field_id%" name="%id%">
 		<a href="#" id="%id%%field_id%" class="close_image delObjectLink"></a>
   		<a href="%url%" class="new" target="_blank">%name%</a>
   		<input name="%field_name%[]" type="hidden" value="%id%">
    </li>
END;

$TEMPLATE['tree_frame'] = <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Bagira.CMS</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<link href="/css_mpanel/style.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="/css_mpanel/jquery-1.7.2.min.js"></script>
	<script type="text/javascript" src="/css_mpanel/jquery.cookie.js"></script>
	<link type="text/css" href="/css_mpanel/ui-lightness/jquery-ui-1.7.2.custom.css" rel="stylesheet" />
<script type="text/javascript" src="/css_mpanel/jquery-ui-1.8.13.custom.min.js"></script>
	<link rel="stylesheet" href="/css_mpanel/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
	<script src="/css_mpanel/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>

	<script type="text/javascript" src="/css_mpanel/lang-ru.js"></script>
	<script type="text/javascript" src="/css_mpanel/main.js"></script>
</head>

<body>

    <input id="parram" type="hidden" value="%parram%">
    <br />

    %tree%
</body>
</html>
END;


?>