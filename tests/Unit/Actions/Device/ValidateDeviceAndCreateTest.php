<?php

namespace LibreNMS\Tests\Unit\Actions\Device;

use App\Actions\Device\DeviceIsSnmpable;
use App\Actions\Device\ValidateDeviceAndCreate;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Exceptions\SnmpException;
use LibreNMS\Tests\TestCase;
use ReflectionMethod;

class ValidateDeviceAndCreateTest extends TestCase
{
    /**
     * Regression test for GitHub issue #20582: CDP/LLDP auto-discovery constructs a stub
     * Device with every SNMPv3 credential field null (see
     * includes/discovery/functions.inc.php::discover_new_device()). detectCredentials() used
     * to unconditionally prepend that empty credential set onto the front of $v3_credentials,
     * so it was always tried first. NetSnmpOptions::buildAuth()'s match on authlevel has no
     * default/null case, so a real SnmpQuery call against that empty set throws
     * SnmpException("Unsupported SNMPv3 AuthLevel: ") before any of the real configured
     * snmp.v3 credential sets are ever tried, and detectCredentials() had no catch for it -
     * the whole discovery run aborted on the very first attempt.
     *
     * This fake mirrors that real failure mode (empty authlevel => throw, matching
     * NetSnmpOptions::buildAuth()'s own default arm) without needing a live device or a real
     * SnmpQuery/net-snmp call chain.
     *
     * @param  array<string, mixed>  $expectedCredential
     */
    private function bindFakeDeviceIsSnmpable(array $expectedCredential): void
    {
        $fake = new class($expectedCredential) extends DeviceIsSnmpable
        {
            /**
             * @param  array<string, mixed>  $expectedCredential
             */
            public function __construct(private array $expectedCredential)
            {
            }

            public function execute(Device $device): bool
            {
                if (empty($device->authlevel)) {
                    throw new SnmpException("Unsupported SNMPv3 AuthLevel: {$device->authlevel}");
                }

                return $device->authlevel === $this->expectedCredential['authlevel']
                    && $device->authname === $this->expectedCredential['authname'];
            }
        };

        $this->instance(DeviceIsSnmpable::class, $fake);
    }

    public function testDetectCredentialsSkipsEmptyStubAuthlevelAndUsesGlobalV3Credential(): void
    {
        $globalCredential = [
            'authlevel' => 'authPriv',
            'authname' => 'globaluser',
            'authpass' => 'globalauthpass',
            'authalgo' => 'SHA',
            'cryptopass' => 'globalcryptopass',
            'cryptoalgo' => 'AES',
        ];

        LibrenmsConfig::set('snmp.version', ['v3']);
        LibrenmsConfig::set('snmp.community', []);
        LibrenmsConfig::set('snmp.v3', [$globalCredential]);

        $this->bindFakeDeviceIsSnmpable($globalCredential);

        // Matches discover_new_device()'s stub Device exactly: only hostname/poller_group
        // set, every credential field (including authlevel) null.
        $device = new Device([
            'hostname' => 'neighbor.example.com',
            'poller_group' => 0,
        ]);

        $action = new ValidateDeviceAndCreate($device);
        $method = new ReflectionMethod($action, 'detectCredentials');

        // Before the fix, this line throws SnmpException because the stub's own empty
        // credential set is tried first and is never skipped.
        $method->invoke($action);

        $this->assertSame('authPriv', $device->authlevel);
        $this->assertSame('globaluser', $device->authname);
    }

    public function testDetectCredentialsStillTriesDeviceOwnCredentialsFirstWhenSet(): void
    {
        $deviceCredential = [
            'authlevel' => 'authNoPriv',
            'authname' => 'deviceuser',
            'authpass' => 'deviceauthpass',
            'authalgo' => 'SHA',
            'cryptopass' => null,
            'cryptoalgo' => null,
        ];
        $globalCredential = [
            'authlevel' => 'authPriv',
            'authname' => 'globaluser',
            'authpass' => 'globalauthpass',
            'authalgo' => 'SHA',
            'cryptopass' => 'globalcryptopass',
            'cryptoalgo' => 'AES',
        ];

        LibrenmsConfig::set('snmp.version', ['v3']);
        LibrenmsConfig::set('snmp.community', []);
        LibrenmsConfig::set('snmp.v3', [$globalCredential]);

        // The fake only succeeds for the device's own credential, proving the fix's guard
        // still prepends (and tries first) a real, already-populated device credential set -
        // it only skips the prepend when authlevel is empty, matching the existing sibling
        // guard on $communities two lines above in the same method.
        $this->bindFakeDeviceIsSnmpable($deviceCredential);

        $device = new Device(array_merge([
            'hostname' => 'existing-device.example.com',
            'poller_group' => 0,
        ], $deviceCredential));

        $action = new ValidateDeviceAndCreate($device);
        $method = new ReflectionMethod($action, 'detectCredentials');

        $method->invoke($action);

        $this->assertSame('authNoPriv', $device->authlevel);
        $this->assertSame('deviceuser', $device->authname);
    }
}
