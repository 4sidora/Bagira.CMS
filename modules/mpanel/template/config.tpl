<?php

$TEMPLATE['account'] = <<<END
<ins>
	<a href="%user_url%">%username%</a>
	<i>( <a href="%url_exit%">%text.exit%</a> )</i>
</ins>
END;

$TEMPLATE['to_site'] = <<<END
<a href="http://%url_site%" target="_blank">%text.to_site%</a>
END;


$TEMPLATE['help_link'] = <<<END
<a href="http://www.help.bagira-cms.ru" target="_blank">%text.help%</a>
END;



// Настройки модуля
$TEMPLATE['config_module'] = <<<END
	<span class="options_abs">
        <div class="options">
            <a class="combo2" href="%url_settings%"><span>%text.settings%</span></a>
        </div>
	</span>
END;

$TEMPLATE['config_frame'] = <<<END
	<span class="options_abs">
        <div class="options">
            <a class="combo" href="#"><span>%text.settings%</span></a>
                <ul class="hidden">
                	%config_items%
                </ul>
        </div>
	</span>
END;

$TEMPLATE['config_item'] = <<<END
<li><a href="%url%" title="">%title%</a></li>
END;



// Навибар
$TEMPLATE['navibar_frame'] = <<<END
%navibar%
END;

$TEMPLATE['navibar_link'] = <<<END
<a href="%link%">%title%</a>  →&nbsp;
END;

$TEMPLATE['navibar'] = <<<END
%title% →&nbsp;
END;


$TEMPLATE['left_column'] = <<<END
<div class="change_info">
	<div id="leftcolumn">
	    %left_column%
	</div><!-- end leftcolum -->

	<div class="rightcolum_plustree">

	    <div id="rightcolumn">

	        <div class="buttons">
			    %buttons%
			    <div class="clear"></div>
			</div>


	    	%content%

	    	<div class="clear"></div>

			<div class="buttons">
			    %buttons_down%
			    <div class="clear"></div>
			</div>

	   	</div><!-- end rightcolum -->

		<div class="clear"></div>

	</div><!-- end rightcolum_plustree -->
</div>
END;


$TEMPLATE['bt_item_active'] = <<<END
    <li class="activ"><a href="%link%">%title%</a></li>
END;

$TEMPLATE['bt_item'] = <<<END
    <li><a href="%link%">%title%</a></li>
END;


$TEMPLATE['left_buttons'] = <<<END

<div class="change_info">

    <div id="leftcolumn_notebook">
    	<ul>
        	%left_buttons%
        </ul>
    </div><!-- end leftcolum -->

    <div class="rightcolum_plusnb">

        <div id="rightcolumn">

            <div class="buttons">
			    %buttons%
			    <div class="clear"></div>
			</div>

	    	%content%

	    	<div class="clear"></div>

			<div class="buttons">
			    %buttons_down%
			    <div class="clear"></div>
			</div>

        </div><!-- end rightcolum -->

        <div class="clear"></div>

    </div><!-- end rightcolum_plusnb -->


</div><!-- change info -->
END;


$TEMPLATE['content'] = <<<END

<div class="buttons">
    %buttons%
    <div class="clear"></div>
</div>

<div class="change_info">
	%content%
</div>

<div class="clear"></div>

<div class="buttons">
    %buttons_down%
    <div class="clear"></div>
</div>

END;



?>