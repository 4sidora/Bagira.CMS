<?php

$TEMPLATE['frame'] = <<<END

<div id="divForm" title=""></div>

<form id="changeForm" name="changeForm" action="" method="post" enctype="multipart/form-data">

<div id="tabs">

	<ul>
    	<li><a href="#tabs-main">%text.1%</a><ins></ins></li>
    	<li><a href="#tabs-lang">%text.2%</a><ins></ins></li>
    	<li><a href="#tabs-domain">%text.3%</a><ins></ins></li>
    </ul>


<div id="tabs-main">
    <div class="ins" style="padding-left:20px;">
	    %gzip%

	    <div class="otstup"></div>

	    %delToTrash%

	    <div class="otstup"></div>
	 	<div class="otstup"></div>

	   	%noIE6%


	    <div class="otstup"></div>
	    <div class="otstup"></div>
	   	<div align="left">%text.32%</div>
	   	<div class="otstup"></div>
	   	%watermark%
	    <div class="otstup"></div>
	    %scaleBigJpeg%
	    <input class="input" type="text" name="sizeBigJpeg" id="sizeBigJpeg" value="%sizeBigJpeg%" style="width:40px;"> px.
        <div class="otstup"></div>

    </div><!-- end ins-->
	<div class="clear"></div>
</div>

<div id="tabs-lang">
    <div class="ins" style="padding-left:20px;padding-bottom:20px;">
		%langs%
    </div><!-- end ins-->
	<div class="clear"></div>
</div>

<div id="tabs-domain">
    <div class="ins" style="padding-left:20px;padding-bottom:20px;">
		%domains%
    </div><!-- end ins-->
	<div class="clear"></div>
</div>




</div>

 	<input name="parram" id="parramForm" type="hidden" value="">
 	<input name="right" type="hidden" value="change_proc">
</form>

<script language="javascript" src="/css_mpanel/global_settings.js"></script>

END;


$TEMPLATE['domain_frame'] = <<<END

<form id="changeDomainForm" name="changeDomainForm" action="" method="post">
  Сообщение при системной ошибке:<br />
  <textarea name="error_msg" style="width:95%;height:100px;" wrap="on">%error_msg%</textarea>
  <br clear="all" /><br />

  Сообщение при отключенном сайте:<br />
  <textarea name="offline_msg" style="width:95%;height:100px;" wrap="on">%offline_msg%</textarea>
  <br clear="all" /><br />

  %mirrors_list%

</form>

END;

?>