<?php

class MIBUpCtrlIndex extends MIBUpCtrl {

	public function run() {
		// Display Index Menu
		echo $this->loadView('mibup.index.menu')->render();
	}

}