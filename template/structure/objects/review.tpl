<?php

$TEMPLATE['frame_list'] = <<<END


    <div class="leftcolumn">

        <div class="clear"></div>

        %list%

    </div>

    <div class="rightcolumn">
        <a href="#" title="" class="feedback whiteshader">Оставить отзыв</a><br/>
    </div>


    %feedback.form(review)%



END;


$TEMPLATE['list_review'] = <<<END

                <h4>%obj.name%</h4>
            <div class="readonly" rel="%obj.user_rate%" id="review%obj.id%"></div>
            <p>— оценка покупки</p>
            <div class="datepubl">
                %core.fdate(d, %obj.create_date%)%
                %core.rus_month(%obj.create_date%, 3)%
                %core.fdate(Y, %obj.create_date%)%
            </div>
            <div class="clear"></div>
             %obj.content%<br/><br/>

END;

$TEMPLATE['empty'] = <<<END

    <div class="leftcolumn">

        <div class="clear"></div>
        Еще нет ни одного отзыва, вы можете быть первыми!

    </div>

    <div class="rightcolumn">
        <a href="#" title="" class="feedback whiteshader">Оставить отзыв</a><br/>
    </div>

    %feedback.form(review)%
END;




?>