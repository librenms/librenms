<?php

class MIBUpCtrl {

	public function __construct() {
	}


	/**
	 * Load a new controller named 'MIBUpCtrl' . $sName
	 */
	public static function load($sName) {
		$sCtrl = 'MIBUpCtrl' . $sName;
		return new $sCtrl;
	}

	public function loadView($sName) {
		return MIBUpView::load($sName);
	}

	public function loadModel($sName) {
		return MIBUpModel::load($sName);
	}

	public function run() {
		throw new MIBUpException('not implemented');
	}

}