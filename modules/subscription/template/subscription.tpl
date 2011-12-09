<?php

$TEMPLATE['frame'] = <<<END
<div id="display"></div>
<script language="javascript" src="/css_mpanel/subscription.js"></script>

<div id="divForm" title="%text.4%" style="display:none;">
	<form id="ajaxFieldForm">

	    %text.1%<br />
	   	<input class="inputfield" type="text" id="subject" name="subject" value="%subject%" style="width:300px;">
	   	<br /><br />

		<div id="part_count" style="display:%sh1%;">
	        %text.2% <span id="pn">%part_num%</span> %text.3%
			<br />
		</div>

		<div id="part_count_text" style="display:%sh2%;">
			%text.7% <span id="epn">%error_part_num%</span> %text.8%
			<br /><br />
		</div>

        <input id="release_id" type="hidden" value="%release_id%">
        <input id="error_part_num" type="hidden" value="%error_part_num%">
        <input id="count_part" type="hidden" value="%count_part%">
        <input id="part" type="hidden" value="%part_num%">
	</form>
</div>

<div id="progressBar" style="display:none;" title="%text.4%">
	<p>
        <div style="display:inline;" id="count_message"></div>  <br /><br />
        <div style="background: #efefef; width:100%; height:20px; border: 1px solid #959a9f;">
        	<div id="probar"  style="background: #e8e3d0; width:0; height:20px;"></div>
        </div>
	</p>
</div>

END;

$TEMPLATE['frame_link'] = <<<END
  <div class="fieldBox">
      %list%

  </div>
END;
//
$TEMPLATE['view_link'] = <<<END
<a href="%url%" target="_blank" style="text-decoration: none;">
  <span class="big_btn"><i class="send">%text.5%</i></span>
 </a>
END;

$TEMPLATE['send_link'] = <<<END

  <span class="big_btn" onclick="showForm()"><i class="view">%text.6%</i></span>

END;

?>