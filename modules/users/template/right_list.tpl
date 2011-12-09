<?php

$TEMPLATE['frame'] = <<<END
<div class="module_block">

<b>%modul_name%</b> <br /><br />

%modul_rights%

</div>

END;


$TEMPLATE['right_0'] = <<<END
<div class="right_block">
  <input type="checkbox" name="rights[]" value="%right_id%" id="right%right_id%" %checked%>
  <label for="right%right_id%">%right_name%</label>

  %subright%
</div>

END;

$TEMPLATE['right_1'] = <<<END
<div class="right_block">

  <div class="cheka" id="cheka%right_id%">%right_name%</div>
  <input name="rights[%right_id%]" id="valcheka%right_id%" type="hidden" value="%value%">

  %subright%
</div>

END;


?>
