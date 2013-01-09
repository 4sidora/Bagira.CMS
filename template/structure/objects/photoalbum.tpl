<?php



$TEMPLATE['frame'] = <<<END
<div class="wrappercut">
    <ul class="albumwrapper">
        %list%
    </ul>
    <div class="clear"></div> 
</div>
<script type="text/javascript" charset="utf-8">
    $(document).ready(function(){
        $("a[rel^='prettyPhoto']").prettyPhoto();
    });
</script>
END;


$TEMPLATE['frame_list'] = <<<END
%list%
END;



$TEMPLATE['list'] = <<<END
<li>
    <a rel="prettyPhoto[gallery1]" href="%obj.image%" title="%obj.name%">
    %structure.getProperty(image, %obj.id%, photogallery)%</a>
</li>
END;

?>