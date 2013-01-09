<?php

$TEMPLATE['frame'] = <<<END

	%list%

END;


$TEMPLATE['vote'] = <<<END
<div class="question" id="vote_block_%obj.id%">
<form id="vote_form_%obj.id%" action="/voting/do" method="post">
  <h3>Опрос</h3>
  <p>%obj.name%</p>
  <ul>
     %answers%
  </ul>
  <a href="#" title="Голосовать" class="act do_voting" id="%obj.id%">Голосовать</a>
  <a href="#" title="" id="%obj.id%" class="show_result">Результаты</a>
  <div class="clear"></div>
    <input name="back_url" type="hidden" value="%current_url%" />
    <input name="vote_id" type="hidden" value="%obj.id%" />  
</form>  
</div>
END;

$TEMPLATE['answer'] = <<<END
<li>
   	<input id="r%obj.id%" type="%type%" name="answers[]" value="%obj.id%" />
   	<label for="r%obj.id%">%obj.name%</label>
</li>
END;


$TEMPLATE['vote_result'] = <<<END
<div class="question">
  <h3>Опрос</h3>
  <p>%obj.name%</p>
  <ul>
     %answers%
  </ul>
  <div class="clear"></div>
</div>
END;

$TEMPLATE['answer_result'] = <<<END
<li>
	%obj.count%&nbsp;&nbsp;%obj.name%
    <div class="answer" style="width: %obj.per1%%;" class="answer %class-best%"></div>
</li>
END;

?>