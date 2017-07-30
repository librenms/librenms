<?php


class MIBUpTestCase extends PHPUnit_Framework_TestCase {

	private static $oDB = null;

	public static function setUpBeforeClass() {
		self::$oDB = new MIBUpDBSetup();
		self::$oDB->setup();
	}

	public static function tearDownAfterClass() {
		self::$oDB->resetTables();
	}

}