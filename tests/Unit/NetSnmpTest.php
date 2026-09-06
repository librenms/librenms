<?php

namespace LibreNMS\Tests\Unit;

use App\Models\Device;
use LibreNMS\Data\Source\Snmp\NetSnmp;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Data\Source\Snmp\SnmpTarget;
use LibreNMS\Data\Source\SnmpResponse;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Tests\TestCase;

class NetSnmpTest extends TestCase
{
    private NetSnmp $backend;

    protected function setUp(): void
    {
        parent::setUp();
        $this->backend = new NetSnmp();
    }

    public function testBuildCliV2c(): void
    {
        $target = new SnmpTarget(
            hostname: '192.168.1.1',
            config: new SnmpConfig(
                version: 'v2c',
                community: 'public',
                transport: 'udp',
                port: 161,
            ),
        );

        $options = new SnmpQueryOptions();
        $cli = $this->backend->buildCli('snmpget', $target, ['sysDescr.0'], $options);

        $this->assertStringEndsWith('snmpget', $cli[0]);
        $this->assertContains('-M', $cli);
        $this->assertContains('-m', $cli);
        $this->assertContains('-v2c', $cli);
        $this->assertContains('-c', $cli);
        $this->assertContains('public', $cli);
        $this->assertContains('-OQXUte', $cli);
        $this->assertContains('-Pu', $cli);
        $this->assertContains('udp:192.168.1.1:161', $cli);
        $this->assertContains('sysDescr.0', $cli);
    }

    public function testBuildCliV1(): void
    {
        $target = new SnmpTarget(
            hostname: '10.0.0.1',
            config: new SnmpConfig(
                version: 'v1',
                community: 'secret',
                transport: 'udp',
                port: 161,
            ),
        );

        $options = new SnmpQueryOptions();
        $cli = $this->backend->buildCli('snmpget', $target, ['sysUpTime.0'], $options);

        $this->assertContains('-v1', $cli);
        $this->assertContains('secret', $cli);
        $this->assertContains('udp:10.0.0.1:161', $cli);
    }

    public function testBuildCliV2cWithContext(): void
    {
        $target = new SnmpTarget(
            hostname: '192.168.1.1',
            config: new SnmpConfig(
                version: 'v2c',
                community: 'public',
            ),
        );

        $options = new SnmpQueryOptions(context: 'vrf1');
        $cli = $this->backend->buildCli('snmpget', $target, ['sysDescr.0'], $options);

        $this->assertContains('public@vrf1', $cli);
    }

    public function testBuildCliV3AuthPriv(): void
    {
        $target = new SnmpTarget(
            hostname: '192.168.1.5',
            config: new SnmpConfig(
                version: 'v3',
                authname: 'admin',
                authpass: 'auth_pass',
                authlevel: 'authPriv',
                authalgo: 'SHA',
                cryptopass: 'priv_pass',
                cryptoalgo: 'AES',
            ),
        );

        $options = new SnmpQueryOptions(context: 'ctx');
        $cli = $this->backend->buildCli('snmpget', $target, ['sysDescr.0'], $options);

        $this->assertContains('-v3', $cli);
        $this->assertContains('-l', $cli);
        $this->assertContains('authPriv', $cli);
        $this->assertContains('-n', $cli);
        $this->assertContains('ctx', $cli);
        $this->assertContains('-a', $cli);
        $this->assertContains('SHA', $cli);
        $this->assertContains('-A', $cli);
        $this->assertContains('auth_pass', $cli);
        $this->assertContains('-x', $cli);
        $this->assertContains('AES', $cli);
        $this->assertContains('-X', $cli);
        $this->assertContains('priv_pass', $cli);
        $this->assertContains('-u', $cli);
        $this->assertContains('admin', $cli);
    }

    public function testBuildCliV3AuthNoPriv(): void
    {
        $target = new SnmpTarget(
            hostname: '192.168.1.5',
            config: new SnmpConfig(
                version: 'v3',
                authname: 'user1',
                authpass: 'auth_pass',
                authlevel: 'authNoPriv',
                authalgo: 'MD5',
            ),
        );

        $options = new SnmpQueryOptions();
        $cli = $this->backend->buildCli('snmpget', $target, ['sysDescr.0'], $options);

        $this->assertContains('-v3', $cli);
        $this->assertContains('-l', $cli);
        $this->assertContains('authNoPriv', $cli);
        $this->assertContains('-a', $cli);
        $this->assertContains('MD5', $cli);
        $this->assertContains('-A', $cli);
        $this->assertContains('auth_pass', $cli);
        $this->assertNotContains('-x', $cli);
        $this->assertNotContains('-X', $cli);
        $this->assertContains('-u', $cli);
        $this->assertContains('user1', $cli);
    }

    public function testBuildCliV3NoAuthNoPriv(): void
    {
        $target = new SnmpTarget(
            hostname: '192.168.1.5',
            config: new SnmpConfig(
                version: 'v3',
                authname: 'guest',
                authlevel: 'noAuthNoPriv',
            ),
        );

        $options = new SnmpQueryOptions();
        $cli = $this->backend->buildCli('snmpget', $target, ['sysDescr.0'], $options);

        $this->assertContains('-v3', $cli);
        $this->assertContains('-l', $cli);
        $this->assertContains('noAuthNoPriv', $cli);
        $this->assertNotContains('-a', $cli);
        $this->assertNotContains('-x', $cli);
        $this->assertContains('-u', $cli);
        $this->assertContains('guest', $cli);
    }

    public function testBuildCliIpv6Brackets(): void
    {
        $target = new SnmpTarget(
            hostname: '2001:db8::1',
            config: new SnmpConfig(
                version: 'v2c',
                community: 'public',
                transport: 'udp6',
                port: 161,
            ),
        );

        $cli = $this->backend->buildCli('snmpget', $target, ['sysDescr.0'], new SnmpQueryOptions());

        $this->assertContains('udp6:[2001:db8::1]:161', $cli);
    }

    public function testBuildCliTimeoutAndRetries(): void
    {
        $target = new SnmpTarget(
            hostname: '192.168.1.1',
            config: new SnmpConfig(
                version: 'v2c',
                community: 'public',
                timeout: 3,
                retries: 0,
            ),
        );

        $cli = $this->backend->buildCli('snmpget', $target, ['sysDescr.0'], new SnmpQueryOptions());

        $this->assertContains('-t', $cli);
        $this->assertContains('3', $cli);
        $this->assertContains('-r', $cli);
        $this->assertContains('0', $cli);
    }

    public function testBuildCliFormattingOptions(): void
    {
        $target = new SnmpTarget(
            hostname: '192.168.1.1',
            config: new SnmpConfig(version: 'v2c', community: 'public'),
        );

        $options = new SnmpQueryOptions(
            tolerateUnorderedIndexes: true,
            outputOidsNumerically: true,
            outputIndexesNumerically: true,
            outputMibNames: false,
            outputEnumsAsStrings: true,
        );

        $cli = $this->backend->buildCli('snmpget', $target, ['sysDescr.0'], $options);

        $this->assertContains('-OQXUt', $cli); // no 'e' because outputEnumsAsStrings = true
        $this->assertContains('-On', $cli);
        $this->assertContains('-Ob', $cli);
        $this->assertContains('-Os', $cli);
        $this->assertContains('-Cc', $cli);
    }

    public function testSnmpQueryOptionsFromCli(): void
    {
        $options = (new SnmpQueryOptions)::parseCli(['-OUneb', '-Cc']);

        $this->assertTrue($options->outputOidsNumerically);
        $this->assertTrue($options->outputIndexesNumerically);
        $this->assertFalse($options->outputEnumsAsStrings);
        $this->assertTrue($options->tolerateUnorderedIndexes);
        $this->assertTrue($options->outputMibNames);

        $optionsHideMib = (new SnmpQueryOptions)::parseCli('-OQUs');
        $this->assertFalse($optionsHideMib->outputMibNames);
        $this->assertTrue($optionsHideMib->outputEnumsAsStrings);
    }

    public function testSnmpTargetFromDevice(): void
    {
        $device = new Device([
            'hostname' => 'router1.example.com',
            'snmpver' => 'v2c',
            'community' => 'test-comm',
            'port' => 1161,
            'timeout' => 2,
            'retries' => 3,
        ]);

        $target = SnmpTarget::fromDevice($device);

        $this->assertSame('router1.example.com', $target->hostname);
        $this->assertSame('v2c', $target->config->version);
        $this->assertSame('test-comm', $target->config->community);
        $this->assertSame(1161, $target->config->port);
        $this->assertSame(2, $target->config->timeout);
        $this->assertSame(3, $target->config->retries);
        $this->assertSame($device, $target->device);
    }

    public function testSnmpResponseStoresCommand(): void
    {
        $response = new SnmpResponse("test = 1\n", '', 0, ['/usr/bin/snmpget', 'test']);
        $this->assertSame(['/usr/bin/snmpget', 'test'], $response->command);

        $appended = $response->append(new SnmpResponse("test2 = 2\n"));
        $this->assertSame(['/usr/bin/snmpget', 'test'], $appended->command);
    }

    public function testMibDirectoriesResolvesOsAndGroup(): void
    {
        $device = new Device([
            'hostname' => 'router1.example.com',
            'os' => 'ios',
            'snmpver' => 'v2c',
            'community' => 'public',
        ]);

        $dirs = \LibreNMS\Util\Mib::directories($device, ['custom/mib/dir']);
        $joined = implode(':', $dirs);

        $this->assertStringContainsString('cisco', $joined);
        $this->assertStringContainsString('custom/mib/dir', $joined);
    }

    public function testDebugSnmpwalkControllerBuildsCommandLine(): void
    {
        $device = new Device([
            'hostname' => 'debug.device.local',
            'os' => 'ios',
            'snmpver' => 'v2c',
            'community' => 'secret',
            'port' => 161,
        ]);

        $controller = new \App\Http\Controllers\Device\Debug\DebugSnmpwalkController();
        $refMethod = new \ReflectionMethod($controller, 'buildCommandLine');
        $cmd = $refMethod->invoke($controller, $device);

        $this->assertStringContainsString('snmpwalk', $cmd[0]);
        $this->assertContains('udp:debug.device.local:161', $cmd);
        $this->assertContains('-On', $cmd);
        $this->assertContains('-Ob', $cmd);
        $this->assertContains('.', $cmd);
    }

    public function testTranslateAlreadyNumericOidReturnsImmediately(): void
    {
        $options = new SnmpQueryOptions(outputOidsNumerically: true);
        $result = $this->backend->translate('.1.3.6.1.2.1.1.1.0', $options);
        $this->assertSame('.1.3.6.1.2.1.1.1.0', $result);

        $resultWithoutDot = $this->backend->translate('1.3.6.1.2.1.1.1.0', $options);
        $this->assertSame('.1.3.6.1.2.1.1.1.0', $resultWithoutDot);
    }
}
