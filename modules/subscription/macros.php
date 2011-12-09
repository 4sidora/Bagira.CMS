<?php

class subscriptionMacros {


    /**
	* @return stirng
	* @param string $templ_name - Шаблон оформления
	* @desc МАКРОС: Выводит форму подписки на рассылки
	*/
 	function form($templ_name = 'default') {

    	$templ_file = '/subscription/'.$templ_name.'.tpl';
        $TEMPLATE = page::getTemplate($templ_file);

	    if (!is_array($TEMPLATE))
	    	return page::errorNotFound('subscription.form', $templ_file);

        $list = '';

        $sel = new ormSelect('subscription');
        $sel->where('lang', '=', languages::curId());
        $sel->where('domain', '=', domains::curId());
        $sel->where('active', '=', 1);

        $num = 0;
	   	while($obj = $sel->getObject()) {

            page::assign('obj.id', $obj->id);
            page::assign('obj.name', $obj->name);

            $num ++;
            page::assign('obj.num', $num);
            page::assign('class-first', ($num == 1) ? 'first' : '');
	        page::assign('class-last', ($num == $sel->getObjectCount()) ? 'last' : '');
	        page::assign('class-odd', ($num % 2 == 0) ? 'odd' : '');
	        page::assign('class-even', ($num % 2 != 0) ? 'even' : '');
            page::assign('class-third', ($num % 3 == 0) ? 'third' : '');
     
            $list .= page::parse($TEMPLATE['list']);
	   	}

	   	if (empty($list))
	   		return page::parse($TEMPLATE['empty']);
	  	else {
		   	page::assign('list', $list);
		   	return page::parse($TEMPLATE['frame']);
	   	}

	}






}

?>