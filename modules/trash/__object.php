<?php

class __object {

  	// удаление объекта
  	public function del() {

        if (system::issetUrl(2) && is_numeric(system::url(2))) {

            // Одиночное удаление
            $obj = new ormObject(system::url(2));
            $obj->delete();

			echo 'delete';

        } else if (isset($_POST['objects'])) {

        	// Множественное удаление
        	while(list($id, $val) = each($_POST['objects'])) {
        		if (is_numeric($id)) {
        			$obj = new ormObject($id);
					$obj->delete();
				}
        	}
        	echo 'delete';


        } else if (!system::issetUrl(2)) {

            // Удаление всех объектов
        	$objects = ormObjects::getTrashObjects();
        	while(list($id, $val) = each($objects)) {
        		$obj = new ormObject($val['id']);
				$obj->delete();
        	}
        	echo 'delete';
        }

        system::stop();
  	}

  	// Востановление объекта
  	public function restore() {

        if (system::issetUrl(2) && is_numeric(system::url(2))) {

            // Одиночное востановление
			$obj = new ormObject(system::url(2));
            
            if ($obj->isInheritor('section'))
                $obj = new ormPage(system::url(2));

			$obj->restore();

			echo 'delete';

        } else if (isset($_POST['objects'])) {

        	// Множественное востановление
        	while(list($id, $val) = each($_POST['objects'])) {
        		if (is_numeric($id)) {
					$obj = new ormObject($id);

                    if ($obj->isInheritor('section'))
                        $obj = new ormPage($id);

					$obj->restore();
				}
        	}
        	echo 'delete';
        }

        ormPages::clearCache();
        system::stop();
  	}

}

?>