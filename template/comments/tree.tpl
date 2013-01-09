<?php

$TEMPLATE['frame'] = <<<END

<div id="comments">

    <h4>Комментарии (%count%)</h4>

    <ul>
        %list%
    </ul>

    %comments.form(%obj_id%)%

</div>

<script type="text/javascript" src="/css_js/_comments.js"></script>

END;

$TEMPLATE['list'] = <<<END
<li id="com%obj.id%">
    <b>%obj.username%</b>
    <small>%core.fdate(d.m.Y H:i, %obj.publ_date%)%</small>
    <small><a href="#" rel="%obj.id%" class="answer">Ответить</a></small>
    <small><a href="%current_url%#comment%obj.id%" id="comment%obj.id%">Ссылка</a></small>

    %rate%

    <div class="clear"></div>
    %obj.text%
    %list%
</li>
END;

$TEMPLATE['frame_list'] = <<<END

    <ul>
        %list%
    </ul>

END;


$TEMPLATE['rate'] = <<<END
    <a href="down" rel="%obj.id%" class="change-rate">-</a>
    <span id="rate%obj.id%">%obj.rate%</span>
    <a href="up" rel="%obj.id%" class="change-rate">+</a>
END;

$TEMPLATE['rate_change'] = <<<END
    <a href="down" rel="%obj.id%" class="change-rate">-</a>
    <span>%obj.rate%</span>
    <a href="up" rel="%obj.id%" class="change-rate">+</a>
END;

$TEMPLATE['empty'] = <<<END

<div id="comments">

    <h4>Комментарии</h4>

    <ul> </ul>

    %comments.form(%obj_id%)%

</div>

<script type="text/javascript" src="/css_js/_comments.js"></script>

END;

?>