<?php

class MIBUpUtilsTest extends PHPUnit_Framework_TestCase {

	public function test_build_file_path() {
		$aPaths = Array('root', 'test', 'path');

		$sWant = join(DIRECTORY_SEPARATOR, $aPaths);

		$this->assertEquals($sWant, MIBUpUtils::bfp($aPaths));
	}

	public function test_vardump() {
		$sVar = 'dump';
		$sWant = 'string(4) "dump"' . "\n";

		$sIs = MIBUpUtils::vardump($sVar);

		$this->assertEquals($sWant, $sIs);
	}

	public function test_shellExec_stdout() {
		$aRes = MIBUpUtils::shellExec('echo -n teststdout');

		$this->assertEquals(0, $aRes[0]);
		$this->assertEquals('teststdout', $aRes[1]);
		$this->assertEquals('', $aRes[2]);
	}

	public function test_shellExec_stderr() {
		$aRes = MIBUpUtils::shellExec('echo -n teststdout 1>&2');

		$this->assertEquals(0, $aRes[0]);
		$this->assertEquals('', $aRes[1]);
		$this->assertEquals('teststdout', $aRes[2]);
	}

	public function test_getConf_with_default() {
		$iIs = MIBUpUtils::getConf('test1234test', 1234);

		$this->assertEquals(1234, $iIs);
	}

	/**
	 * @expectedException MIBUpException
	 */
	public function test_getConf_nodefault_throw() {
		MIBUpUtils::getConf('test1234test');
	}

}