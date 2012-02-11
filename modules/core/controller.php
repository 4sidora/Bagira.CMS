<?php

class controller {

    public function defAction() {
		 return ormPages::get404();
 	}

 	public function random_imageAction() {
		 if (file_exists(MODUL_DIR.'/core/random_image.php'))
              include(MODUL_DIR.'/core/random_image.php');
 	}

}

?>