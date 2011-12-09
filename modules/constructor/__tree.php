<?php

class __tree {

    private $classes;

    private $rights = array(
    	'values' => 'list_block_image',
    	'0' => 'empty',
    	'class_add' => 'add_image',
    	'class_upd' => 'compose_image',
    	'class_del' => 'drop_image'
    );


	public function defAction() {

        ui::newButton(lang::get('BTN_NEW_CLASS'), '/constructor/class_add');

        if (file_exists(MODUL_DIR.'/constructor/template/classesTree.tpl')){
            include(MODUL_DIR.'/constructor/template/classesTree.tpl');

             page::assign('items', $this->createTree(0, $TEMPLATE));

             return page::parse($TEMPLATE['frame']);

        }
 	}

 	private function createTree($parent, $TEMPLATE) {

        $items = '';

    	while ($obj = ormClasses::getInheritor($parent)){

	            page::assign('subitem', $this->createTree($obj->id(), $TEMPLATE));

	            page::assign('obj.id', $obj->id());
	            page::assign('obj.url', system::au().'/constructor/class_upd/'.$obj->id());
	            page::assign('obj.name', $obj->getName());
	            page::assign('obj.sname', $obj->getSName());
	            page::assign('obj.parent', $obj->getParentId());

	            $pach = '/css_mpanel/tree/images/classes/';
	            $ico = (file_exists(ROOT_DIR.$pach.$obj->getSName().'.png')) ? $obj->getSName().'.png' : 'file1.gif';
	            page::assign('obj.ico', $pach.$ico);

	            $rights = '';
	            reset($this->rights);
                while (list($right, $pict) = each($this->rights)){

                    $modul = ($right == 'values') ? 'reference' : 'constructor';

                    if (user::issetRight($right, $modul) || $pict == 'empty'){
	                    if ($pict == 'empty')
	                   		$rights .= page::parse($TEMPLATE['empty_right']);
	                   	else {
		                    $parse = true;
		                	if ($right == 'values') $parse = ($obj->isInheritor('handbook') && $obj->getSName() != 'handbook');
		                    if ($right == 'class_del') $parse = (!$obj->isSystem());

		                	if ($parse){
		                		$del_button = ($right == 'class_del') ? 'class="del_button"' : '';
		                		page::assign('del_button', $del_button);

		                		page::assign('right.url', system::au().'/'.$modul.'/'.$right);
			            		page::assign('right.title', lang::right($right, $modul));
			            		page::assign('right.class', $pict);

			            		$rights .= page::parse($TEMPLATE['right']);
		                    } else $rights .= page::parse($TEMPLATE['empty_right']);
	                    }
                    }
                }
                page::assign('rights', $rights);

	     		$items .= page::parse($TEMPLATE['item']);

     	}

        if (!empty($items)){
	        page::assign('items', $items);
	     	return page::parse($TEMPLATE['frame_items']);
     	}
 	}


}

?>
