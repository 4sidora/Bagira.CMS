<?php


$TEMPLATE['frame'] = <<<END

<script language="javascript">

jQuery(document).ready(function() {

    var rules = [];

    rules.push("required,delivery_name,Поле «Имя» обязательно для заполнения!");
    rules.push("required,delivery_surname,Поле «Фамилия» обязательно для заполнения!");
    rules.push("required,delivery_phone,Поле «Телефон» обязательно для заполнения!");
    rules.push("required,delivery_address,Поле «Адрес доставки» обязательно для заполнения!");

	$("#changeForm").RSV({
          customErrorHandler: ShowMessageRVS,
          rules: rules
    });

    $("#name").focus();

});

</script>



<div class="onetabs">
    <div class="ins">

	<form id="changeForm" name="changeForm" action="" method="post" enctype="multipart/form-data">


    <div class="fieldBox">
        <label for="name">Номер заказа:</label> <big>%order.number%</big><br/><br/>
        <label for="name">Дата заказа:</label> %obj.date%<br/><br/>
        <label for="name">E-mail заказчика:</label> <a href="%user_link%" target="_blank">%obj.email%</a><br/>
    </div>

    <div class="fieldBox">
    	<label for="state" class=" chek" title=""><b></b>Статус заказа</label>
        <div class="clear"></div>
        %state%
 	</div>


    <div class="clear"></div>

    <div class="fieldBox" style="width: 950px;">
        <label for="notice" class=" " title=""><b></b>Примечания администратора</label>
        <div class="redactor" >
            <textarea name="notice" id="notice" style="height: 100px; width: 100%;">%obj.notice%</textarea>
        </div>
    </div>

    <div class="clear"></div>





    <div class="line_abc" style="width:999px;"><i><b>Заказанные товары</b></i></div>


    <div style="margin-left:20px;">%goods_list%</div>

    <div class="clear"></div><br />
    <div style="margin-left:613px;float:left;">Итого:</div>
    <div style="margin-left:20px;float:left;">%order.cost% руб.</div>

    <div class="clear"></div><br />
    <div style="margin-left:594px;float:left;">Доставка:</div>
    <div style="margin-left:19px;float:left;">%order.delivery_price% руб.</div>

    <div class="clear"></div><br />
    <div style="margin-left:593px;float:left;"><b>К оплате:</b></div>
    <div style="margin-left:20px;float:left;">
        <b>%order.cost_all% руб.</b>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        %is_payment%
    </div>

    <div class="clear"></div>



    <div class="line_abc" style="width:999px;"><i><b>Инфомация о доставке</b></i></div>

        <div class="fieldBox">
               <label for="delivery"><b></b>Способ доставки</label>
               <div class="clear"></div>
               <big>%order.delivery%</big>
            </div>
           <div class="otstup"></div><div class="clear"></div>
           <div class="fieldBox">
               <label for="delivery_name" class=" chek" title=""><b></b>Имя</label>
               <input class="input" type="text" name="delivery_name" id="delivery_name" value="%obj.delivery_name%">
           </div>

           <div class="fieldBox">
               <label for="delivery_surname" class=" chek" title=""><b></b>Фамилия</label>
               <input class="input" type="text" name="delivery_surname" id="delivery_surname" value="%obj.delivery_surname%">
           </div>
       <div class="clear"></div>
           <div class="fieldBox">
               <label for="delivery_phone" class=" chek" title=""><b></b>Телефон</label>
               <input class="input" type="text" name="delivery_phone" id="delivery_phone" value="%obj.delivery_phone%">
           </div>


       <div class="fieldBox" style="width: 950px;">
           <label for="delivery_address" class=" chek" title=""><b></b>Адрес доставки</label>
           <div class="redactor" >
               <textarea name="delivery_address" id="delivery_address" style="height: 100px; width: 100%;">%obj.delivery_address%</textarea>
           </div>
       </div>
       <div class="clear"></div>

       <div class="fieldBox" style="width: 950px;">
           <label for="delivery_notice" class=" " title=""><b></b>Дополнителная информация</label>
           <div class="redactor" >
               <textarea name="delivery_notice" id="delivery_notice" style="height: 100px; width: 100%;">%obj.delivery_notice%</textarea>
           </div>
       </div>







	 	<input name="parram" id="parramForm" type="hidden" value="">
        <input name="right" type="hidden" value="order_proc_view">
        <input name="obj_id" type="hidden" value="%obj.id%">
	</form>

	</div><!-- end ins-->
    <div class="clear"></div>
</div><!-- end tab -->
END;

?>