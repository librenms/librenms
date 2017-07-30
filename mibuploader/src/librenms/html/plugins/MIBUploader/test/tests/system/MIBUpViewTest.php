<?php

class MIBUpViewTest extends PHPUnit_Framework_TestCase {

	public function test_view() {
		$oView = MIBUpView::load('mibup.unittest');

		$this->assertInstanceOf(MIBUpView, $oView);
	}

	public function test_set_view() {
		$sRes = MIBUpView::load('mibup.unittest')
			->set('sVar1', 'val1')
			->set('sVar2', 'val2')
			->render();

		$this->assertEquals('val1val2val3', $sRes);
	}

}