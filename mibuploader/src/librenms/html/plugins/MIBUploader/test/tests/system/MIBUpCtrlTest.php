<?php

class TestMIBUpCtrlDefault extends MIBUpCtrl {

}

class MIBUpCtrlTest extends PHPUnit_Framework_TestCase {

	/**
	 * @expectedException MIBUpException
	 */
	public function test_default_ctrl_run_throws() {
		$oCtrl = new TestMIBUpCtrlDefault();
		$oCtrl->run();
	}

	public function test_load() {
		$oCtrl = MIBUpCtrl::load('UnitTest');

		$this->assertInstanceOf(MIBUpCtrl, $oCtrl);
	}

	public function test_controller_run() {
		$oCtrl = MIBUpCtrl::load('UnitTest');

		$bRes = $oCtrl->run();

		$this->assertEquals(true, $bRes);
	}
}