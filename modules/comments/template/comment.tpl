<?php


$TEMPLATE['frame'] = <<<END

<script language="javascript">
jQuery(document).ready(function() {
    var rules = [];
    rules.push("required,c_text,Поле «Текст комментария» обязательно для заполнения!");
	$("#changeForm").RSV({
          customErrorHandler: ShowMessageRVS,
          rules: rules
    });
    $("#c_text").focus();
});
</script>



<div class="onetabs">
    <div class="ins">

	<form id="changeForm" name="changeForm" action="" method="post" enctype="multipart/form-data">


        <div class="fieldBox">
            %user_name%
            <label>E-mail пользователя:</label> %obj.email%<br/>
            <label>Дата создания:</label> %obj.date%<br/><br/>
        </div>

        <div class="fieldBox">
            %active%

            <br/><br/>

            <label>Прикреплен к странице:</label><br/>
            <a href="%page.url%" target="_blank">%page.name%</a>
        </div>


        <div class="clear"></div>

        <div class="fieldBox" style="width: 950px;">
            <label for="notice" class=" " title=""><b></b>Текст комментария</label>
            <div class="redactor" >
                <textarea name="c_text" id="c_text" style="height: 160px; width: 100%;">%obj.text%</textarea>
            </div>
        </div>

        <div class="fieldBox" style="width: 950px;">
            <label for="c_parram"><b></b>Дополнительный параметр</label>
            <input class="input" name="c_parram" id="c_parram" type="text" value="%obj.parram%" style="width:945px;">
        </div>

        <div class="clear"></div>

	 	<input name="parram" id="parramForm" type="hidden" value="">
        <input name="right" type="hidden" value="comment_proc_upd">
        <input name="obj_id" type="hidden" value="%obj.id%">
	</form>

	</div><!-- end ins-->
    <div class="clear"></div>
</div><!-- end tab -->
END;

$TEMPLATE['user_link'] = <<<END
<label>Имя пользователя:</label>
<a href="%user_link%" target="_blank">
        <big>%obj.username%</big>
</a>
<br/><br/>
END;

$TEMPLATE['user_name'] = <<<END
<label>Имя пользователя:</label> <big>%obj.username%</big><br/><br/>
END;

?>