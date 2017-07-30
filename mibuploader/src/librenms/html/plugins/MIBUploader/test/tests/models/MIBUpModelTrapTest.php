<?php

class MIBUpModelTrapTest extends MIBUpTestCase {

	/**
	 * @var MIBUpModelTrap
	 */
	private static $oMdl = null;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$oMdl = MIBUpModel::load('Trap');
	}

	/// EMPTY ///

	public function test_gettrap_empty() {
		$res1 = self::$oMdl->getTrap();
		$this->assertInternalType('array', $res1);
		$this->assertEquals(count($res1), 0);

		$res2 = self::$oMdl->getTrap(1);
		$this->assertInternalType('array', $res2);
		$this->assertEquals(count($res2), 0);

		$res3 = self::$oMdl->getTrap(1, '.1.2');
		$this->assertInternalType('array', $res3);
		$this->assertEquals(count($res3), 0);

		$res4 = self::$oMdl->getTrap(1, '.1.2', true);
		$this->assertInternalType('array', $res4);
		$this->assertEquals(count($res4), 0);
	}

	/// WITH DATA ///

	/**
	 * @depends test_gettrap_empty
	 */
	public function test_set_trap_noval() {
		$res = self::$oMdl->setTrap(1, '.1.2', null);

		$this->assertTrue($res);
	}

	/**
	 * @depends test_gettrap_empty
	 */
	public function test_set_trap_val() {
		$res = self::$oMdl->setTrap(2, '.3.4', 'param:value');

		$this->assertTrue($res);
	}

	/**
	 * @depends test_set_trap_noval
	 */
	public function test_gettrap() {
		$res = self::$oMdl->getTrap(1);

		$this->assertInternalType('array', $res);
		$this->assertEquals(count($res), 1);
		$this->assertEquals($res[0]['device_id'], 1);
		$this->assertEquals($res[0]['oid'], '.1.2');
		$this->assertGreaterThan(1000, $res[0]['last_update_ts']);
	}

	/**
	 * @depends test_set_trap_noval
	 */
	public function test_set_trap_update() {
		$res = self::$oMdl->setTrap(1, '.1.2', 'param:value');

		$this->assertTrue($res);
	}

	/**
	 * @depends test_set_trap_update
	 */
	public function test_gettrap_updated() {
		$res = self::$oMdl->getTrap(1);

		$this->assertInternalType('array', $res);
		$this->assertEquals(count($res), 1);
		$this->assertEquals($res[0]['device_id'], 1);
		$this->assertEquals($res[0]['oid'], '.1.2');
		$this->assertEquals($res[0]['values'], 'param:value');
		$this->assertGreaterThan(1000, $res[0]['last_update_ts']);
	}


	/**
	 * @depends test_set_trap_update
	 */
	public function test_autoclean_no() {
		$res = self::$oMdl->autoClean(0);

		$this->assertTrue($res);
		$this->assertEquals(count(self::$oMdl->getTrap()), 2);
	}

	/**
	 * @depends test_autoclean_no
	 */
	public function test_autoclean_onesec() {
		sleep(2);
		$res = self::$oMdl->autoClean(1);

		$this->assertTrue($res);
		$this->assertEquals(count(self::$oMdl->getTrap()), 0);
	}
}