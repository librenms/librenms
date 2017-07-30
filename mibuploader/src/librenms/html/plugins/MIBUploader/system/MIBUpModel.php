<?php

class MIBUpModel {

	public static function load($sName) {
		$sName = 'MIBUpModel' . $sName;
		return new $sName;
	}

}