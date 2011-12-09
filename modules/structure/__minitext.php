<?php

class __minitext {

    public function __construct() {
    	ui::setNewLink(lang::get('STRUCTURE_SETTINGS'), 'settings', 'tabs-main');
        ui::setNewLink(lang::get('STRUCTURE_FORM_FIELD', 11), 'settings', 'tabs-page_tpl');
        ui::setNewLink(lang::get('STRUCTURE_FORM_FIELD', 14), 'settings', 'tabs-obj_tpl');
        ui::setNewLink(lang::get('STRUCTURE_FORM_FIELD', 15), 'settings', 'tabs-view');
    	ui::setNewLink(lang::get('STRUCTURE_MINITEXT'), 'minitext');
    }

	public function defAction() {


        ui::newButton(lang::get('BTN_SAVE'), "javascript:sendForm('save');");
	    ui::newButton(lang::get('BTN_APPLY'), "javascript:sendForm('apply');");

        if (isset($_SESSION['STRUCTURE_LIST_FLAG']) && $_SESSION['STRUCTURE_LIST_FLAG'])
        	ui::setCancelButton('/structure/list');
        else
        	ui::setCancelButton('/structure/tree');

        $list = reg::getList(ormPages::getPrefix().'/minitext', true);
        $texts = array();
        while(list($num, $val) = each($list))
        	$texts[] = array(
        		'id' => $val['id'],
        		'num' => '<center>%text_'.$val['id'].'%</center>',
        		'text' => $val['value']
        	);

        // Форма редактирования
        $form = new uiMultiForm('change');
        $form->setRight('minitext_proc');
        $form->setData($texts);
        $form->addColumn('text', lang::get('STRUCTURE_TABLE_FIELD_9'), 700, lang::get('STRUCTURE_TABLE_FIELD_10'));
        $form->addColumn('num', lang::get('STRUCTURE_TABLE_FIELD_8'), 150, lang::get('STRUCTURE_TABLE_FIELD_11'), false);


		return $form->getHTML('multi_form_memo');
 	}



  	public function proc() {

        reg::setKey(ormPages::getPrefix().'/minitext');

        function changeText($id, $obj){

            if (!empty($id))
            	reg::setKey($id, $obj['text']);
            else if (!empty($obj['text']))
            	reg::addToList(ormPages::getPrefix().'/minitext', $obj['text']);

			return true;
        }

        function delText($id) { reg::delKey($id); }



        $form = new uiMultiForm('change');
        $form->process('changeText', 'delText');


        ormPages::clearCache();

        if ($_POST['parram'] == 'apply')
			system::redirect('/structure/minitext');
		else if ($_SESSION['STRUCTURE_LIST_FLAG'])
        	system::redirect('/structure/list');
        else
        	system::redirect('/structure/tree');
  	}




}

?>