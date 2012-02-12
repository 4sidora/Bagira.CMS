<?php

$TEMPLATE['frame'] = <<<END

%structure.filterABC(350 faq, filter_abc, ru, content)%

<div id="leftcolumn">
    <ul class="questionanswer">
        %list%
    </ul>
</div> 
<div id="rightcolumn">
Спрашивайте, не стесняйтесь.
<a id="showFaq" href="#" class="question whiteshader">Задать вопрос</a>
</div>
<div class="clear"></div> 

%feedback.form(faq)%

END;


$TEMPLATE['list_empty_category_faq'] = <<<END

	На указанную букву ничего не найдено!

END;

$TEMPLATE['frame_list'] = <<<END

	%list%

	%structure.navigation(%count_page%)%

END;

$TEMPLATE['list_faq'] = <<<END
<li>
    <div class="answer2"><a href="#" class="title" title="">%obj.content%</a><small>%obj.name%,&nbsp;
        %core.fdate(d, %obj.create_date%)% %core.rus_mounth(%obj.create_date%)% %core.fdate(Y, %obj.create_date%)%</small></div><div class="clear"></div>
    <div class="answer">
        <div class="answer3">
            <span class="content">%obj.answer%</span> 
        </div>
    </div>
</li>
END;




?>