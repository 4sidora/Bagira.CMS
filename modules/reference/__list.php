<?php

class __list {

    public function __construct() {
    	ui::checkClasses('handbook');
    }

	// вывод списка
	public function defAction() {

        $list = array();

        if ($class = ormClasses::get('handbook')) {
	        $mas = $class->getAllInheritors();

	        while(list($id, $sname) = each($mas))
		        if ($sname != 'handbook') {
		            $h = ormClasses::get($sname);
		        	$list[] = array(
		        		'id' => $id,
		        		'empty' => '',
		        		'name' => $h->getName(),
		        		'sname' => $sname
		        	);
		        }
        }

		$table = new uiTable($list, 1);

        $table->addColumn('empty', '', 20);
        $table->addColumn('name', 'Название справочника', 300);
        $table->addColumn('sname', 'Системное назавание', 300, 0, 0);

        $table->defaultRight('values');
        $table->addRight('values', 'edit', single);

        return $table->getHTML();

 	}


}

?>