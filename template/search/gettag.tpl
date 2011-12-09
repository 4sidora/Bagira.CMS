<?php

$TEMPLATE['frame'] = <<<END

	%list%

END;

$TEMPLATE['frame_list'] = <<<END

	%list%


%structure.navigation(%count_page%, 4, search)%

END;


$TEMPLATE['list'] = <<<END

 %obj.num%. <a href="%obj.url%">%obj.name%</a> <br /> <br />

END;

$TEMPLATE['list_faq'] = <<<END

 %obj.num%. <a href="%obj.url%">Вопрос от посетителя %obj.name%</a> <br /> <br />

END;

$TEMPLATE['not_found'] = <<<END

 <br />
 По вашему запросу ничего не найдено. Повторите поиск изменив условия.

END;

?>