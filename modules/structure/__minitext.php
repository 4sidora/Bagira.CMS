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
        $texts = $js = array();
        foreach($list as $val) {
        	$texts[] = array(
        		'id' => $val['id'],
        		'description' => $val['description'],
        		'num' => '<center>%text_'.$val['id'].'%</center>',
        		'text' => $val['value']
        	);
            $js['text_'.$val['id'].'_vvv'] = $val['value'];
        }

        // Форма редактирования
        $form = new uiMultiForm('change');
		if (reg::getKey('/core/noDelMiniTexts'))
			$form->withoutRemoving();
        $form->setRight('minitext_proc');
        $form->setData($texts);
        $form->addColumn('description', lang::get('STRUCTURE_TABLE_FIELD_12'), 200, lang::get('STRUCTURE_TABLE_FIELD_13'));
        $form->addColumn('text', lang::get('STRUCTURE_TABLE_FIELD_9'), 500, lang::get('STRUCTURE_TABLE_FIELD_10'));
        $form->addColumn('num', lang::get('STRUCTURE_TABLE_FIELD_8'), 150, lang::get('STRUCTURE_TABLE_FIELD_11'), false);

        $js = '<script language="javascript"> var textlist = '.json_encode($js).';</script>';

		return $js.$form->getHTML('multi_form_memo');
 	}
	
	public function proc_edit() {
		$text = system::checkVar(system::POST('minitext'), isText);
		$id = system::checkVar(system::POST('minitext_id'), isInt);
		$key = ormPages::getPrefix().'/minitext/'.$id;

		if ($text && $id && reg::existKey($key)) {
			if (reg::setKey($key, $text)) {
				system::json(array('error' => 0));
				system::stop();
			}
		}

		system::json(array('error' => 1, 'errorInfo' => lang::get('STRUCTURE_MINITEXT_ERROR')));
		system::stop();
	}

  	public function proc() {

        reg::setKey(ormPages::getPrefix().'/minitext');

        function changeText($id, $obj){

            if (!empty($id))
            	reg::setKey($id, $obj['text'], $obj['description']);
            else if (!empty($obj['text']))
            	reg::addToList(ormPages::getPrefix().'/minitext', $obj['text'], $obj['description']);

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