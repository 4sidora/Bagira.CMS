<?php

$TEMPLATE['frame'] = <<<END
%list%
END;

$TEMPLATE['frame_list'] = <<<END
%list%
<script> 
$(window).load(function(){
	$('.goods_1').attr('class', 'goods selected');
    $('.minio_1').attr('class', 'selected');
    
}); 
</script>
END;

$TEMPLATE['list_goods'] = <<<END
<div class="goods goods_%obj.num%">
    
    <div class="image">
       %structure.getProperty(image, %obj.id%, super_right)%
    </div>
    <div class="text">
        
        <a href="%obj.url%" title="%obj.name%">%obj.name%</a><br/><br/>
        %obj.notice%<br/><br/>
        <div class="buy">
            <a >в магазине за</a><br/>
            <div class="clear"></div>
            <span class="summa">%obj.price%</span> <b style="font-size:21px;">Р</b><span class="rubl" style="color:#fff;">−</span>
        </div>
    </div>
</div>

END;


?>