<?php

namespace LibreNMS\Tests\Unit;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Database\Eloquent\Collection;
use LibreNMS\Data\Source\Snmp\NetSnmp;
use LibreNMS\Data\Source\Snmp\NetSnmpOptions;
use LibreNMS\Data\Source\Snmp\RawSnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SnmpOidOutput;
use LibreNMS\Enum\SnmpStringOutput;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Secrets\Data\SnmpSecretData;
use LibreNMS\Tests\TestCase;

class NetSnmpTest extends TestCase
{
    private NetSnmp $backend;
    private NetSnmpOptions $optionsParser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->backend = new NetSnmp();
        $this->optionsParser = new NetSnmpOptions();
    }

    private function makeDeviceWithSnmpConfig(array $secretData = [], array $settings = [], array $deviceAttrs = []): Device
    {
        $device = new Device(array_merge(['hostname' => 'router1.example.com'], $deviceAttrs));
        $device->device_id = 1;
        $device->setRelation('attribs', new Collection);

        $secret = new Secret([
            'secret_type' => \LibreNMS\Enum\SecretType::Snmp,
            'data' => array_merge(['version' => 'v2c', 'community' => 'test-comm'], $secretData),
        ]);
        $method = new DevicePollingMethod([
            'method_type' => PollingMethodType::Snmp,
            'enabled' => true,
            'affects_availability' => true,
            'settings' => $settings,
        ]);
        $method->setRelation('device', $device);
        $method->setRelation('secret', $secret);
        $device->setRelation('pollingMethods', collect([$method]));

        return $device;
    }

    public function testBuildCliV2c(): void
    {
        $config = SnmpConfig::make(
            [
                'transport' => 'udp',
                'port' => 161,
            ] + (new SnmpPollingMethod)->defaults(),
            new SnmpSecretData(
                version: 'v2c',
                community: 'public',
            ),
        );

        $options = SnmpQueryOptions::quickPrint();
        $cli = $this->optionsParser->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, $options);

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
        $config = SnmpConfig::make(
            [
                'transport' => 'udp',
                'port' => 161,
            ] + (new SnmpPollingMethod)->defaults(),
            new SnmpSecretData(
                version: 'v2c',
                community: 'public',
            ),
        );

        $options = new SnmpQueryOptions();
        $cli = $this->optionsParser->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, $options);

        $this->assertStringEndsWith('snmpget', $cli[0]);
        $this->assertNotContains('-OQXUte', $cli);
        foreach ($cli as $arg) {
            $this->assertStringStartsNotWith('-O', $arg);
        }
    }

    public function testBuildCliV1(): void
    {
        $config = SnmpConfig::make(
            [
                'transport' => 'udp',
                'port' => 161,
            ] + (new SnmpPollingMethod)->defaults(),
            new SnmpSecretData(
                version: 'v1',
                community: 'secret',
            ),
        );

        $options = new SnmpQueryOptions();
        $cli = $this->optionsParser->buildCli('snmpget', '10.0.0.1', ['sysUpTime.0'], $config, $options);

        $this->assertContains('-v1', $cli);
        $this->assertContains('secret', $cli);
        $this->assertContains('udp:10.0.0.1:161', $cli);
    }

    public function testBuildCliV2cWithContext(): void
    {
        $config = SnmpConfig::make(
            (new SnmpPollingMethod)->defaults(),
            new SnmpSecretData(
                version: 'v2c',
                community: 'public',
            ),
        );

        $options = new SnmpQueryOptions(context: 'vrf1');
        $cli = $this->optionsParser->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, $options);

        $this->assertContains('public@vrf1', $cli);
    }

    public function testBuildCliV3AuthPriv(): void
    {
        $config = SnmpConfig::make(
            (new SnmpPollingMethod)->defaults(),
            new SnmpSecretData(
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
        $cli = $this->optionsParser->buildCli('snmpget', '192.168.1.5', ['sysDescr.0'], $config, $options);

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
        $config = SnmpConfig::make(
            (new SnmpPollingMethod)->defaults(),
            new SnmpSecretData(
                version: 'v3',
                authname: 'user1',
                authpass: 'auth_pass',
                authlevel: 'authNoPriv',
                authalgo: 'MD5',
            ),
        );

        $options = new SnmpQueryOptions();
        $cli = $this->optionsParser->buildCli('snmpget', '192.168.1.5', ['sysDescr.0'], $config, $options);

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
        $config = SnmpConfig::make(
            (new SnmpPollingMethod)->defaults(),
            new SnmpSecretData(
                version: 'v3',
                authname: 'guest',
                authlevel: 'noAuthNoPriv',
            ),
        );

        $options = new SnmpQueryOptions();
        $cli = $this->optionsParser->buildCli('snmpget', '192.168.1.5', ['sysDescr.0'], $config, $options);

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
        $config = SnmpConfig::make(
            [
                'transport' => 'udp6',
                'port' => 161,
            ] + (new SnmpPollingMethod)->defaults(),
            new SnmpSecretData(
                version: 'v2c',
                community: 'public',
            ),
        );

        $cli = $this->optionsParser->buildCli('snmpget', '2001:db8::1', ['sysDescr.0'], $config, new SnmpQueryOptions());

        $this->assertContains('udp6:[2001:db8::1]:161', $cli);
    }

    public function testBuildCliTimeoutAndRetries(): void
    {
        $config = SnmpConfig::make(
            [
                'timeout' => 3,
                'retries' => 0,
            ] + (new SnmpPollingMethod)->defaults(),
            new SnmpSecretData(
                version: 'v2c',
                community: 'public',
            ),
        );

        $cli = $this->optionsParser->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, new SnmpQueryOptions());

        $this->assertContains('-t', $cli);
        $this->assertContains('3', $cli);
        $this->assertContains('-r', $cli);
        $this->assertContains('0', $cli);
    }

    public function testBuildCliFloatTimeout(): void
    {
        $config = SnmpConfig::make(
            [
                'timeout' => 0.2,
            ] + (new SnmpPollingMethod)->defaults(),
            new SnmpSecretData(
                version: 'v2c',
                community: 'public',
            ),
        );

        $cli = $this->optionsParser->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, new SnmpQueryOptions());

        $this->assertContains('-t', $cli);
        $this->assertContains('0.2', $cli);
    }

    public function testBuildCliZeroOrNegativeTimeoutDoesNotEmitFlag(): void
    {
        $config = SnmpConfig::make(
            [
                'timeout' => 0,
            ] + (new SnmpPollingMethod)->defaults(),
            new SnmpSecretData(
                version: 'v2c',
                community: 'public',
            ),
        );

        $cli = $this->optionsParser->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, new SnmpQueryOptions());

        $this->assertNotContains('-t', $cli);
    }

    public function testBuildCliDecidesBulk(): void
    {
        $configV2 = SnmpConfig::make(
            (new SnmpPollingMethod)->defaults(),
            new SnmpSecretData(
                version: 'v2c',
                community: 'public',
            ),
        );
        $configV1 = SnmpConfig::make(
            (new SnmpPollingMethod)->defaults(),
            new SnmpSecretData(
                version: 'v1',
                community: 'public',
            ),
        );

        // v2c with allowBulk (default) upgrades snmpwalk to snmpbulkwalk
        $cliBulk = $this->optionsParser->buildCli('snmpwalk', '192.168.1.1', ['.'], $configV2, new SnmpQueryOptions());
        $this->assertStringEndsWith('snmpbulkwalk', $cliBulk[0]);

        // v2c with allowBulk: false stays snmpwalk
        $cliNoBulk = $this->optionsParser->buildCli('snmpwalk', '192.168.1.1', ['.'], $configV2, new SnmpQueryOptions(allowBulk: false));
        $this->assertStringEndsWith('snmpwalk', $cliNoBulk[0]);

        // v1 with allowBulk: true stays snmpwalk because v1 does not support bulk
        $cliV1 = $this->optionsParser->buildCli('snmpwalk', '192.168.1.1', ['.'], $configV1, new SnmpQueryOptions(allowBulk: true));
        $this->assertStringEndsWith('snmpwalk', $cliV1[0]);

        // v2c with bulk: false on SnmpConfig stays snmpwalk even with allowBulk: true
        $configNoBulk = SnmpConfig::make(
            [
                'bulk' => false,
            ] + (new SnmpPollingMethod)->defaults(),
            new SnmpSecretData(
                version: 'v2c',
                community: 'public',
            ),
        );
        $cliTargetNoBulk = $this->optionsParser->buildCli('snmpwalk', '192.168.1.1', ['.'], $configNoBulk, new SnmpQueryOptions(allowBulk: true));
        $this->assertStringEndsWith('snmpwalk', $cliTargetNoBulk[0]);
    }

    public function testBuildCliFormattingOptions(): void
    {
        $config = SnmpConfig::make(
            (new SnmpPollingMethod)->defaults(),
            new SnmpSecretData(
                version: 'v2c',
                community: 'public',
            ),
        );

        $options = SnmpQueryOptions::quickPrint();
        $options->tolerateUnorderedIndexes = true;
        $options->oidFormat = SnmpOidOutput::Numeric;
        $options->numericIndexes = true;
        $options->numericEnums = false;

        $cli = $this->optionsParser->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, $options);

        $this->assertContains('-OQXUtbn', $cli); // numeric suppression of 's', no 'e' because numericEnums = false
        $this->assertContains('-Cc', $cli);

        // When oidFormat is Suffix, 's' is emitted
        $optionsSymbolic = SnmpQueryOptions::quickPrint();
        $optionsSymbolic->oidFormat = SnmpOidOutput::Suffix;
        $cliSymbolic = $this->optionsParser->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, $optionsSymbolic);
        $this->assertContains('-OQXUtes', $cliSymbolic);
    }

    public function testBuildCliAsciiStrings(): void
    {
        $config = SnmpConfig::make(
            (new SnmpPollingMethod)->defaults(),
            new SnmpSecretData(
                version: 'v2c',
                community: 'public',
            ),
        );

        $options = SnmpQueryOptions::quickPrint();
        $options->stringFormat = SnmpStringOutput::Ascii;
        $cli = $this->optionsParser->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, $options);
        $this->assertContains('-OQXUtea', $cli);

        $optionsFromCli = $this->optionsParser->parseCli(['-OteQUSab', '-Pu', '-Ih']);
        $cliFromCli = $this->optionsParser->buildCli('snmpwalk', '192.168.1.1', ['.1.3.6.1.4.1.2356.100'], $config, $optionsFromCli);
        $this->assertContains('-OQUteba', $cliFromCli);
        $this->assertContains('-Pu', $cliFromCli);
        $this->assertContains('-Ih', $cliFromCli);
    }

    public function testBuildCliHexStrings(): void
    {
        $config = SnmpConfig::make(
            (new SnmpPollingMethod)->defaults(),
            new SnmpSecretData(
                version: 'v2c',
                community: 'public',
            ),
        );

        $options = SnmpQueryOptions::quickPrint();
        $options->stringFormat = SnmpStringOutput::Hex;
        $cli = $this->optionsParser->buildCli('snmpget', '192.168.1.1', ['sysDescr.0'], $config, $options);
        $this->assertContains('-OQXUtex', $cli);

        $optionsFromCli = $this->optionsParser->parseCli(['-OteQUax', '-Pu']);
        $cliFromCli = $this->optionsParser->buildCli('snmpwalk', '192.168.1.1', ['.1.3.6.1.4.1.2356.100'], $config, $optionsFromCli);
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

        $config = $device->polling()->snmp();

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

        $config1 = $device->polling()->snmp();

        // Mutate the polling method's secret data
        $snmpMethod = $device->pollingMethod(PollingMethodType::Snmp);
        $secret = $snmpMethod->secret;
        $secret->data = array_merge($secret->data, ['community' => 'new']);

        $config2 = $device->polling()->snmp();

        $this->assertSame('test-comm', $config1->community);
        $this->assertSame('new', $config2->community);
    }

    public function testSnmpConfigFromDeviceRespectsSnmpBulkSetting(): void
    {
        $deviceBulk = $this->makeDeviceWithSnmpConfig(
            deviceAttrs: ['hostname' => 'bulk.device', 'os' => 'ios'],
        );
        $configBulk = $deviceBulk->polling()->snmp();
        $this->assertTrue($configBulk->bulk);

        \App\Facades\LibrenmsConfig::set('os.airos.snmp_bulk', false);
        $deviceNoBulk = $this->makeDeviceWithSnmpConfig(
            deviceAttrs: ['hostname' => 'nobulk.device', 'os' => 'airos'],
        );
        $configNoBulk = $deviceNoBulk->polling()->snmp();
        $this->assertFalse($configNoBulk->bulk);
    }

    public function testSnmpConfigFromDeviceFloatTimeout(): void
    {
        $device = $this->makeDeviceWithSnmpConfig(
            settings: ['timeout' => 0.5],
        );

        $config = $device->polling()->snmp();
        $this->assertSame(0.5, $config->timeout);
    }

    public function testSnmpResponseStoresCommand(): void
    {
        $response = new SnmpResponse(['test' => '1'], '', 0, ['/usr/bin/snmpget', 'test']);
        $this->assertSame(['/usr/bin/snmpget', 'test'], $response->command);

        $appended = $response->append(new SnmpResponse(['test2' => '2']));
        $this->assertSame(['/usr/bin/snmpget', 'test'], $appended->command);
    }

    public function testMibDirectoriesResolvesOsAndGroup(): void
    {
        $dirs = \LibreNMS\Util\Mib::directories('ios', ['custom/mib/dir', 'juniper']);
        $joined = implode(':', $dirs);

        $this->assertStringContainsString('cisco', $joined);
        $this->assertStringNotContainsString('custom/mib/dir', $joined);
        $this->assertStringContainsString('juniper', $joined);
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

    public function testSnmpConfigFromDeviceWithoutPollingMethodIgnoresLegacyFields(): void
    {
        \App\Facades\LibrenmsConfig::set('snmp.retries', 5);
        $device = (new Device())->forceFill([
            'hostname' => 'legacy.device.local',
            'snmpver' => 'v1',
            'community' => 'stale-comm',
            'retries' => 2,
        ]);

        $config = (new SnmpPollingMethod)->config($device); // no polling methods

        $this->assertSame('v2c', $config->version);
        $this->assertNull($config->community);
        $this->assertSame(5, $config->retries);
    }

    public function testLegacyOidLimitUsesTheSnmpPollingMethod(): void
    {
        \App\Facades\LibrenmsConfig::set('snmp.max_oid', 5);
        \App\Facades\DeviceCache::fake($this->makeDeviceWithSnmpConfig(settings: ['max_oid' => 20]));

        $this->assertSame(20, get_device_oid_limit(['device_id' => 1, 'os' => 'generic']));
    }

    public function testLegacySnmpExecUsesTheContextFromTheDeviceArray(): void
    {
        $device = $this->makeDeviceWithSnmpConfig(settings: ['context' => 'vrf-default']);
        \App\Facades\DeviceCache::fake($device);
        $deviceArray = ['device_id' => 1, 'hostname' => 'router1.example.com', 'overwrite_ip' => null, 'os' => 'generic'];

        $contexts = [];
        $backend = \Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('get')->twice()->andReturnUsing(function ($target, $oids, $config, SnmpQueryOptions $options) use (&$contexts) {
            $contexts[] = $options->context;

            return new RawSnmpResponse('SNMPv2-MIB::sysName.0 = STRING: router1', '', 0);
        });
        $this->app->instance(SnmpBackendInterface::class, $backend);

        snmp_get($deviceArray + ['context_name' => 'vrf-a'], 'SNMPv2-MIB::sysName.0', '-Oqv');
        snmp_get($deviceArray, 'SNMPv2-MIB::sysName.0', '-Oqv');

        // an ad-hoc context overrides the device default context
        $this->assertSame(['vrf-a', 'vrf-default'], $contexts);
    }

    public function testSnmpConfigFromDeviceArray(): void
    {
        $deviceArray = [
            'hostname' => 'legacy-array.device.local',
            'snmpver' => 'v3',
            'authlevel' => 'authPriv',
            'authname' => 'testuser',
            'authpass' => 'authpass123',
            'authalgo' => 'SHA',
            'cryptopass' => 'privpass123',
            'cryptoalgo' => 'AES',
            'transport' => 'udp6',
            'port' => 161,
        ];

        $config = (new SnmpPollingMethod)->configFromDeviceArray($deviceArray);

        $this->assertSame('v3', $config->version);
        $this->assertSame('authPriv', $config->authlevel);
        $this->assertSame('testuser', $config->authname);
        $this->assertSame('authpass123', $config->authpass);
        $this->assertSame('SHA', $config->authalgo);
        $this->assertSame('privpass123', $config->cryptopass);
        $this->assertSame('AES', $config->cryptoalgo);
        $this->assertSame('udp6', $config->transport);
        $this->assertSame(161, $config->port);
    }

    public function testSnmpConfigFromNullDeviceArray(): void
    {
        $config = (new SnmpPollingMethod)->configFromDeviceArray(null);

        $this->assertSame('v2c', $config->version);
        $this->assertNull($config->community);
        $this->assertSame('udp', $config->transport);
        $this->assertSame(161, $config->port);
    }

    public function testExistingDeviceWithoutPollingMethodsDoesNotLogEvent(): void
    {
        $device = new Device();
        $device->device_id = 42;
        $device->exists = true;
        $device->hostname = 'no-methods.example.com';
        $device->setRelation('attribs', new Collection);
        $device->setRelation('pollingMethods', collect([]));

        $mockEventlog = \Mockery::mock(\App\Models\Eventlog::class);
        $this->app->instance(\App\Models\Eventlog::class, $mockEventlog);

        $mockEventlog->shouldNotReceive('_log');

        $config = $device->polling()->snmp();

        $this->assertSame('v2c', $config->version);
        $this->assertNull($config->community);
    }

    public function testTransientDeviceWithoutPollingMethodsIgnoresLegacyCredentials(): void
    {
        $device = new Device();
        $device->hostname = 'transient.example.com';
        $device->exists = false;
        $device->setRelation('attribs', new Collection);
        $device->setAttribute('snmpver', 'v3');
        $device->setAttribute('authname', 'stale-user');

        $config = $device->polling()->snmp();

        $this->assertSame('v2c', $config->version);
        $this->assertNull($config->authname);
    }
}
