<?php

$TEMPLATE['frame'] = <<<END
<script type="text/javascript" src="/css_mpanel/filters.js"></script>
<form name="filter_form" id="filter_form" method="post" onSubmit="return false;">

	<code>
		%fields%
		<div class="clear"></div>
	</code>

	<input name="filter" type="hidden" value="1">
</form>
END;
          //  <ins class="date"> Äàòà ïóáëèêàöèè <b></b>	 c <input /> ïî  <input /></ins>
$TEMPLATE['field'] = <<<END
	<ins>
		%field.name%  <b></b>
		<input type="text" name="%field.sname%" id="%field.sname%" value="%field.value%">
	</ins>
END;

$TEMPLATE['field_spec'] = <<<END
	<ins>
		%field.name%  <b></b>
		%field.content%
	</ins>
END;

$TEMPLATE['field_period'] = <<<END

    <ins class="date">
		%field.name%  <b></b>
		с <input class="filter_field2" type="text" name="%field.sname%" id="%field.sname%" value="%field.value%">  по
   			<input class="filter_field2" type="text" name="%field.sname%2" id="%field.sname%2" value="%field.value2%">
	</ins>

END;

$TEMPLATE['field_select'] = <<<END

	<ins class="date">
		%field.name%  <b></b>
		%element%
	</ins>
END;

?>