<?php

class MIBUpModelTest extends PHPUnit_Framework_TestCase {

	public function test_load() {
		$oModel = MIBUpModel::load('UnitTest');

		$this->assertInstanceOf(MIBUpModel, $oModel);
	}

}