<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Data\Source\Snmp\PhpSnmp;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Secrets\Data\SnmpCommunitySecretData;
use LibreNMS\Polling\Secrets\Data\SnmpV3SecretData;
use LibreNMS\Tests\TestCase;

class PhpSnmpTest extends TestCase
{
    public function testCommunityAndUsername(): void
    {
        $method = new \ReflectionMethod(PhpSnmp::class, 'communityOrUsername');
        $backend = new PhpSnmp;
        $defaults = (new SnmpPollingMethod)->defaults();

        $v3 = SnmpConfig::make($defaults, new SnmpV3SecretData(authname: 'alice'));
        $v3Default = SnmpConfig::make($defaults, new SnmpV3SecretData);
        $v2 = SnmpConfig::make($defaults, new SnmpCommunitySecretData(community: 'private'));
        $v1 = SnmpConfig::make(['version' => 'v1'] + $defaults, new SnmpCommunitySecretData(community: 'private'));
        $v2Default = SnmpConfig::make($defaults, new SnmpCommunitySecretData(community: ''));

        $this->assertSame('alice', $method->invoke($backend, $v3, ''));
        $this->assertSame('alice', $method->invoke($backend, $v3, 'vrf1'));
        $this->assertSame('root', $method->invoke($backend, $v3Default, 'vrf1'));
        $this->assertSame('private', $method->invoke($backend, $v2, ''));
        $this->assertSame('private@vrf1', $method->invoke($backend, $v2, 'vrf1'));
        $this->assertSame('private@vrf1', $method->invoke($backend, $v1, 'vrf1'));
        $this->assertSame('public', $method->invoke($backend, $v2Default, ''));
    }

    public function testV3SecurityLevelsAndContext(): void
    {
        if (! class_exists(\SNMP::class)) {
            $this->markTestSkipped('The SNMP extension is not installed.');
        }

        $method = new \ReflectionMethod(PhpSnmp::class, 'setSecurityOptions');
        $backend = new PhpSnmp;
        $defaults = (new SnmpPollingMethod)->defaults();

        foreach ([
            ['authPriv', ['authPriv', 'SHA', 'authpass', 'AES', 'privpass', 'vrf1']],
            ['authNoPriv', ['authNoPriv', 'SHA', 'authpass', '', '', 'vrf1']],
            ['noAuthNoPriv', ['noAuthNoPriv', '', '', '', '', 'vrf1']],
        ] as [$level, $expected]) {
            $config = SnmpConfig::make($defaults, new SnmpV3SecretData(
                authname: 'alice',
                authlevel: $level,
                authpass: 'authpass',
                authalgo: 'SHA',
                cryptoalgo: 'AES',
                cryptopass: 'privpass',
            ));

            $snmp = $this->getMockBuilder(\SNMP::class)->disableOriginalConstructor()->onlyMethods(['setSecurity'])->getMock();
            $snmp->expects($this->once())->method('setSecurity')->with(...$expected);
            $method->invoke($backend, $snmp, $config, 'vrf1');
        }
    }
}
