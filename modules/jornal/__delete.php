<?php

class __delete {

	public function defAction() {

		$file = @fopen(ROOT_DIR."/revue.log", "w");
		@fclose ($file);

		db::q('DELETE FROM <<revue>>');
		
		system::redirect('/jornal');
	}
	
}

?>