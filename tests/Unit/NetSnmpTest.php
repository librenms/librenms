<?php

namespace LibreNMS\Tests\Unit;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Database\Eloquent\Collection;
use LibreNMS\Data\Source\Snmp\NetSnmp;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SnmpOidOutput;
use LibreNMS\Enum\SnmpStringOutput;
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

    private function makeDeviceWithSnmpConfig(array $secretData = [], array $settings = [], array $deviceAttrs = []): Device
    {
        $device = new Device(array_merge(['hostname' => 'router1.example.com'], $deviceAttrs));
        $device->device_id = 1;
        $device->setRelation('attribs', new Collection);

        $method = DevicePollingMethod::transient(
            PollingMethodType::Snmp,
            settings: $settings,
            secretData: array_merge(['version' => 'v2c', 'community' => 'test-comm'], $secretData),
            device: $device,
        );
        $device->setRelation('pollingMethods', collect([$method]));

        return $device;
    }

    public function testBuildCliV2c(): void
    {
        $config = new SnmpConfig(
            version: 'v2c',
            community: 'public',
            transport: 'udp',
            port: 161,
        );

        $options = SnmpQueryOptions::quickPrint();
        $cli = $this->backend->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, $options);

        $this->assertStringEndsWith('snmpget', $cli[0]);
        $this->assertContains('-M', $cli);
        $this->assertContains('-m', $cli);
        $this->assertContains('-v2c', $cli);
        $this->assertContains('-c', $cli);
        $this->assertContains('public', $cli);
        $this->assertContains('-OQXUte', $cli);
        $this->assertContains('udp:192.168.1.1:161', $cli);
        $this->assertContains('sysDescr.0', $cli);
    }

    public function testBuildCliNetSnmpLibraryDefaults(): void
    {
        $config = new SnmpConfig(
            version: 'v2c',
            community: 'public',
            transport: 'udp',
            port: 161,
        );

        $options = new SnmpQueryOptions();
        $cli = $this->backend->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, $options);

        $this->assertStringEndsWith('snmpget', $cli[0]);
        $this->assertNotContains('-OQXUte', $cli);
        foreach ($cli as $arg) {
            $this->assertStringStartsNotWith('-O', $arg);
        }
    }

    public function testBuildCliV1(): void
    {
        $config = new SnmpConfig(
            version: 'v1',
            community: 'secret',
            transport: 'udp',
            port: 161,
        );

        $options = new SnmpQueryOptions();
        $cli = $this->backend->buildCli('snmpget', '10.0.0.1', ['sysUpTime.0'], $config, $options);

        $this->assertContains('-v1', $cli);
        $this->assertContains('secret', $cli);
        $this->assertContains('udp:10.0.0.1:161', $cli);
    }

    public function testBuildCliV2cWithContext(): void
    {
        $config = new SnmpConfig(
            version: 'v2c',
            community: 'public',
        );

        $options = new SnmpQueryOptions(context: 'vrf1');
        $cli = $this->backend->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, $options);

        $this->assertContains('public@vrf1', $cli);
    }

    public function testBuildCliV3AuthPriv(): void
    {
        $config = new SnmpConfig(
            version: 'v3',
            authname: 'admin',
            authpass: 'auth_pass',
            authlevel: 'authPriv',
            authalgo: 'SHA',
            cryptopass: 'priv_pass',
            cryptoalgo: 'AES',
        );

        $options = new SnmpQueryOptions(context: 'ctx');
        $cli = $this->backend->buildCli('snmpget', '192.168.1.5', ['sysDescr.0'], $config, $options);

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
        $config = new SnmpConfig(
            version: 'v3',
            authname: 'user1',
            authpass: 'auth_pass',
            authlevel: 'authNoPriv',
            authalgo: 'MD5',
        );

        $options = new SnmpQueryOptions();
        $cli = $this->backend->buildCli('snmpget', '192.168.1.5', ['sysDescr.0'], $config, $options);

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
        $config = new SnmpConfig(
            version: 'v3',
            authname: 'guest',
            authlevel: 'noAuthNoPriv',
        );

        $options = new SnmpQueryOptions();
        $cli = $this->backend->buildCli('snmpget', '192.168.1.5', ['sysDescr.0'], $config, $options);

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
        $config = new SnmpConfig(
            version: 'v2c',
            community: 'public',
            transport: 'udp6',
            port: 161,
        );

        $cli = $this->backend->buildCli('snmpget', '2001:db8::1', ['sysDescr.0'], $config, new SnmpQueryOptions());

        $this->assertContains('udp6:[2001:db8::1]:161', $cli);
    }

    public function testBuildCliTimeoutAndRetries(): void
    {
        $config = new SnmpConfig(
            version: 'v2c',
            community: 'public',
            timeout: 3,
            retries: 0,
        );

        $cli = $this->backend->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, new SnmpQueryOptions());

        $this->assertContains('-t', $cli);
        $this->assertContains('3', $cli);
        $this->assertContains('-r', $cli);
        $this->assertContains('0', $cli);
    }

    public function testBuildCliFloatTimeout(): void
    {
        $config = new SnmpConfig(
            version: 'v2c',
            community: 'public',
            timeout: 0.2,
        );

        $cli = $this->backend->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, new SnmpQueryOptions());

        $this->assertContains('-t', $cli);
        $this->assertContains('0.2', $cli);
    }

    public function testBuildCliZeroOrNegativeTimeoutDoesNotEmitFlag(): void
    {
        $config = new SnmpConfig(
            version: 'v2c',
            community: 'public',
            timeout: 0,
        );

        $cli = $this->backend->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, new SnmpQueryOptions());

        $this->assertNotContains('-t', $cli);
    }

    public function testBuildCliDecidesBulk(): void
    {
        $configV2 = new SnmpConfig(
            version: 'v2c',
            community: 'public',
        );
        $configV1 = new SnmpConfig(
            version: 'v1',
            community: 'public',
        );

        // v2c with allowBulk (default) upgrades snmpwalk to snmpbulkwalk
        $cliBulk = $this->backend->buildCli('snmpwalk', '192.168.1.1', ['.'], $configV2, new SnmpQueryOptions());
        $this->assertStringEndsWith('snmpbulkwalk', $cliBulk[0]);

        // v2c with allowBulk: false stays snmpwalk
        $cliNoBulk = $this->backend->buildCli('snmpwalk', '192.168.1.1', ['.'], $configV2, new SnmpQueryOptions(allowBulk: false));
        $this->assertStringEndsWith('snmpwalk', $cliNoBulk[0]);

        // v1 with allowBulk: true stays snmpwalk because v1 does not support bulk
        $cliV1 = $this->backend->buildCli('snmpwalk', '192.168.1.1', ['.'], $configV1, new SnmpQueryOptions(allowBulk: true));
        $this->assertStringEndsWith('snmpwalk', $cliV1[0]);

        // v2c with bulk: false on SnmpConfig stays snmpwalk even with allowBulk: true
        $configNoBulk = new SnmpConfig(
            version: 'v2c',
            community: 'public',
            bulk: false,
        );
        $cliTargetNoBulk = $this->backend->buildCli('snmpwalk', '192.168.1.1', ['.'], $configNoBulk, new SnmpQueryOptions(allowBulk: true));
        $this->assertStringEndsWith('snmpwalk', $cliTargetNoBulk[0]);
    }

    public function testBuildCliFormattingOptions(): void
    {
        $config = new SnmpConfig(
            version: 'v2c',
            community: 'public',
        );

        $options = SnmpQueryOptions::quickPrint();
        $options->tolerateUnorderedIndexes = true;
        $options->oidFormat = SnmpOidOutput::Numeric;
        $options->numericIndexes = true;
        $options->numericEnums = false;

        $cli = $this->backend->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, $options);

        $this->assertContains('-OQXUtbn', $cli); // numeric suppression of 's', no 'e' because numericEnums = false
        $this->assertContains('-Cc', $cli);

        // When oidFormat is Suffix, 's' is emitted
        $optionsSymbolic = SnmpQueryOptions::quickPrint();
        $optionsSymbolic->oidFormat = SnmpOidOutput::Suffix;
        $cliSymbolic = $this->backend->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, $optionsSymbolic);
        $this->assertContains('-OQXUtes', $cliSymbolic);
    }

    public function testBuildCliAsciiStrings(): void
    {
        $config = new SnmpConfig(
            version: 'v2c',
            community: 'public',
        );

        $options = SnmpQueryOptions::quickPrint();
        $options->stringFormat = SnmpStringOutput::Ascii;
        $cli = $this->backend->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, $options);
        $this->assertContains('-OQXUtea', $cli);

        $optionsFromCli = (new SnmpQueryOptions)->parseCli(['-OteQUSab', '-Pu', '-Ih']);
        $cliFromCli = $this->backend->buildCli('snmpwalk', '192.168.1.1', ['.1.3.6.1.4.1.2356.100'], $config, $optionsFromCli);
        $this->assertContains('-OQUteba', $cliFromCli);
        $this->assertContains('-Pu', $cliFromCli);
        $this->assertContains('-Ih', $cliFromCli);
    }

    public function testBuildCliHexStrings(): void
    {
        $config = new SnmpConfig(
            version: 'v2c',
            community: 'public',
        );

        $options = SnmpQueryOptions::quickPrint();
        $options->stringFormat = SnmpStringOutput::Hex;
        $cli = $this->backend->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, $options);
        $this->assertContains('-OQXUtex', $cli);

        $optionsFromCli = (new SnmpQueryOptions)->parseCli(['-OteQUax', '-Pu']);
        $cliFromCli = $this->backend->buildCli('snmpwalk', '192.168.1.1', ['.1.3.6.1.4.1.2356.100'], $config, $optionsFromCli);
        $this->assertContains('-OQUtex', $cliFromCli);
        $this->assertContains('-Pu', $cliFromCli);
    }

    public function testSnmpConfigFromDevice(): void
    {
        $device = $this->makeDeviceWithSnmpConfig(
            secretData: [
                'version' => 'v2c',
                'community' => 'test-comm',
            ],
            settings: [
                'port' => 1161,
                'timeout' => 2,
                'retries' => 3,
            ],
        );

        $config = SnmpConfig::fromDevice($device);

        $this->assertSame('v2c', $config->version);
        $this->assertSame('test-comm', $config->community);
        $this->assertSame(1161, $config->port);
        $this->assertEquals(2, $config->timeout);
        $this->assertSame(3, $config->retries);
        $this->assertTrue($config->bulk);
    }

    public function testDeviceToSnmpConfigHandlesMutation(): void
    {
        $device = $this->makeDeviceWithSnmpConfig(
            secretData: ['community' => 'test-comm'],
        );

        $config1 = $device->toSnmpConfig();

        // Mutate the polling method's secret data
        $snmpMethod = $device->pollingMethod(PollingMethodType::Snmp);
        $secret = $snmpMethod->secret;
        $secret->data = array_merge($secret->data, ['community' => 'new']);

        $config2 = $device->toSnmpConfig();

        $this->assertSame('test-comm', $config1->community);
        $this->assertSame('new', $config2->community);
    }

    public function testSnmpConfigFromDeviceRespectsSnmpBulkSetting(): void
    {
        $deviceBulk = $this->makeDeviceWithSnmpConfig(
            deviceAttrs: ['hostname' => 'bulk.device', 'os' => 'ios'],
        );
        $configBulk = SnmpConfig::fromDevice($deviceBulk);
        $this->assertTrue($configBulk->bulk);

        \App\Facades\LibrenmsConfig::set('os.airos.snmp_bulk', false);
        $deviceNoBulk = $this->makeDeviceWithSnmpConfig(
            deviceAttrs: ['hostname' => 'nobulk.device', 'os' => 'airos'],
        );
        $configNoBulk = SnmpConfig::fromDevice($deviceNoBulk);
        $this->assertFalse($configNoBulk->bulk);
    }

    public function testSnmpConfigFromDeviceFloatTimeout(): void
    {
        $device = $this->makeDeviceWithSnmpConfig(
            settings: ['timeout' => 0.5],
        );

        $config = SnmpConfig::fromDevice($device);
        $this->assertSame(0.5, $config->timeout);

        // A timeout <= 0 falls back to configured snmp.timeout
        $deviceZero = $this->makeDeviceWithSnmpConfig(
            settings: ['timeout' => 0],
        );
        $configZero = SnmpConfig::fromDevice($deviceZero);
        $this->assertEquals(\App\Facades\LibrenmsConfig::get('snmp.timeout', 1), $configZero->timeout);
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
        $device = $this->makeDeviceWithSnmpConfig(
            secretData: ['community' => 'secret'],
            settings: ['port' => 161],
            deviceAttrs: [
                'hostname' => 'debug.device.local',
                'os' => 'ios',
            ],
        );

        $controller = new \App\Http\Controllers\Device\Debug\DebugSnmpwalkController();
        $refMethod = new \ReflectionMethod($controller, 'buildCommandLine');
        $cmd = $refMethod->invoke($controller, $device);

        $this->assertStringContainsString('snmp', $cmd[0]);
        $this->assertContains('udp:debug.device.local:161', $cmd);
        $this->assertContains('-OUebn', $cmd);
        $this->assertNotContains('-Pu', $cmd);
        $this->assertNotContains('-OQXUte', $cmd);
        $this->assertContains('.', $cmd);
    }

    public function testDebugSnmpwalkControllerRespectsOsSnmpBulkFalse(): void
    {
        \App\Facades\LibrenmsConfig::set('os.airos.snmp_bulk', false);
        $device = $this->makeDeviceWithSnmpConfig(
            secretData: ['community' => 'secret'],
            settings: ['port' => 161],
            deviceAttrs: [
                'hostname' => 'nobulk.device.local',
                'os' => 'airos',
            ],
        );

        $controller = new \App\Http\Controllers\Device\Debug\DebugSnmpwalkController();
        $refMethod = new \ReflectionMethod($controller, 'buildCommandLine');
        $cmd = $refMethod->invoke($controller, $device);

        $this->assertStringEndsWith('snmpwalk', $cmd[0]);
    }

    public function testTranslateAlreadyNumericOidReturnsImmediately(): void
    {
        $options = new SnmpQueryOptions(oidFormat: SnmpOidOutput::Numeric);
        $result = $this->backend->translate('.1.3.6.1.2.1.1.1.0', $options);
        $this->assertSame('.1.3.6.1.2.1.1.1.0', $result);

        $resultWithoutDot = $this->backend->translate('1.3.6.1.2.1.1.1.0', $options);
        $this->assertSame('.1.3.6.1.2.1.1.1.0', $resultWithoutDot);
    }
}
