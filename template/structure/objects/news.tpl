<?php

$TEMPLATE['frame'] = <<<END

%list%

END;

$TEMPLATE['frame_list'] = <<<END

%list%

%structure.navigation(%count_page%)%


<br/><br/><center>
<a href="/structure/rss">Читать ленту в RSS</a>
</center>
END;

$TEMPLATE['list_news_feed'] = <<<END

END;


$TEMPLATE['list_news'] = <<<END
<div class="newsblock">
<div class="content">
     <h4>
        <a href="%obj.url%" title="%obj.name%">%obj.name%</a>
        <a href="%obj.url%#comments" style="text-decoration:none;">%comments.count(%obj.id%)%</a>
     </h4>
     <span>
         %core.fdate(d, %obj.publ_date%)%
         %core.rus_month(%obj.publ_date%)%
         %core.fdate(Y, %obj.publ_date%)%
     </span>
     <div class="text">
        %obj.notice%
     </div>

    %search.tags(%obj.id%)%

</div>

     %structure.getProperty(image, %obj.id%, news)%


<div class="clear"></div>
</div>
<div class="clear"></div> 
END;



$TEMPLATE['frame_news'] = <<<END
<div class="newsview">
<div id="leftcolumn">
     %obj.notice%<br/>
        %structure.getProperty(image, %obj.id%, newsbig)%
        <br/>
        %obj.content%
        <br/><br/>
        %search.tags(%obj.id%)%


<a href="%back_url%">Назад</a>

<br />
    <br />
    

%comments.tree(%obj.id%)%

    
</div>

<div id="rightcolumn">

    <div class="date">
        %core.fdate(d, %obj.publ_date%)%
        %core.rus_month(%obj.publ_date%)%
        %core.fdate(Y, %obj.publ_date%)%
    </div>

    %structure.getProperty(author, %obj.id%, author)%

    %structure.objListByTags(%obj.id%, tags_list)%
    <br/>
   	%structure.objList(1568, fresh_publ, 5)%
    
</div>
</div>


END;






?>