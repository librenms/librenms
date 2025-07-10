<?php

namespace LibreNMS\Tests;

use App\Facades\LibrenmsConfig;
use PHPUnit\Framework\TestCase;

class AATestEnvTest extends TestCase
{

    public function testSnmpTimeoutsAreConfigured(): void
    {
        $this->assertEquals(0.1, LibrenmsConfig::get('snmp.timeout'));
        $this->assertEquals(0, LibrenmsConfig::get('snmp.retries'));

        $this->fail('Success');
    }
}
