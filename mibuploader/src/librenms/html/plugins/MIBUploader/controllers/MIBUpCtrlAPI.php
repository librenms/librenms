<?php

class MIBUpCtrlAPI extends MIBUpCtrl {

	public function run() {
		if (isset($_GET['coucou'])) {
			header('Content-Type: application/json');
			echo json_encode(Array('coucou' => 'Comment ça va ?'));
		} else {
			echo $this->loadView('mibup.api.test')
				->render();
		}
	}

}