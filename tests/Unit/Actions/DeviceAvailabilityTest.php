<?php

namespace LibreNMS\Tests\Unit\Actions;

use App\Actions\Device\CheckDeviceAvailability;
use App\Actions\Device\SetDeviceAvailability;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Support\Collection;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Data\Source\Icmp\FpingResponse;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Exceptions\SecretDecryptionException;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Tests\TestCase;
use Mockery;

final class DeviceAvailabilityTest extends TestCase
{
    public function testStatusReasonListsFailedMethodsThatAffectAvailability(): void
    {
        $snmp = $this->method(PollingMethodType::Snmp, lastCheck: false);
        $icmp = $this->method(PollingMethodType::Icmp, lastCheck: false);
        $ipmi = $this->method(PollingMethodType::Ipmi, lastCheck: false, affectsAvailability: false);
        $device = $this->device([$snmp, $icmp, $ipmi]);
        $setAvailability = new SetDeviceAvailability;

        $this->assertTrue($setAvailability->execute($device, commit: false));
        $this->assertFalse($device->status);
        $this->assertSame('snmp,icmp', $device->status_reason);
        $device->syncOriginal(); // saved

        // disabled methods don't count
        $snmp->enabled = false;
        $this->assertFalse($setAvailability->execute($device, commit: false));
        $this->assertSame('icmp', $device->status_reason);

        $icmp->enabled = false;
        $this->assertTrue($setAvailability->execute($device, commit: false));
        $this->assertTrue($device->status);
        $this->assertSame('', $device->status_reason);
    }

    public function testUncheckedMethodIsNotFailed(): void
    {
        $device = $this->device([$this->method(PollingMethodType::Snmp, lastCheck: null)]);

        $this->assertTrue((new ConnectivityHelper($device))->isAvailable());

        (new SetDeviceAvailability)->execute($device, commit: false);
        $this->assertTrue($device->status);
        $this->assertSame('', $device->status_reason);
    }

    public function testMethodThatCanNotBeCheckedIsFailedAndOthersAreStillChecked(): void
    {
        $snmp = $this->method(PollingMethodType::Snmp);
        $snmp->setRelation('secret', $this->undecryptableSecret());
        $icmp = $this->method(PollingMethodType::Icmp);
        $device = $this->device([$snmp, $icmp]);
        $this->mockFping(FpingResponse::artificialUp('192.0.2.1'));

        $status = app(CheckDeviceAvailability::class)->execute($device);

        $this->assertFalse($status);
        $this->assertFalse($snmp->last_check_successful);
        $this->assertNotNull($snmp->last_checked_at);
        $this->assertTrue($icmp->last_check_successful);
        $this->assertSame('snmp', $device->status_reason);
    }

    public function testFailedMethodThatDoesNotAffectAvailabilityKeepsDeviceUp(): void
    {
        $snmp = $this->method(PollingMethodType::Snmp, affectsAvailability: false);
        $snmp->setRelation('secret', $this->undecryptableSecret());
        $icmp = $this->method(PollingMethodType::Icmp);
        $device = $this->device([$snmp, $icmp]);
        $this->mockFping(FpingResponse::artificialUp('192.0.2.1'));

        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($snmp->last_check_successful);
        $this->assertSame('', $device->status_reason);
    }

    public function testProbeExceptionMarksMethodFailed(): void
    {
        $icmp = $this->method(PollingMethodType::Icmp);
        $device = $this->device([$icmp]);

        $fping = Mockery::mock(Fping::class);
        $fping->shouldReceive('ping')->andThrow(new \RuntimeException('fping exploded'));
        $this->app->instance(Fping::class, $fping);

        $this->assertFalse(app(CheckDeviceAvailability::class)->execute($device));
        $this->assertFalse($icmp->last_check_successful);
        $this->assertSame('icmp', $device->status_reason);
    }

    /**
     * @param  DevicePollingMethod[]  $methods
     */
    private function device(array $methods): Device
    {
        $device = new Device(['hostname' => '192.0.2.1', 'status' => true]);
        $device->syncOriginal(); // as if loaded, so status changes are detected
        foreach ($methods as $method) {
            $method->setRelation('device', $device);
        }
        $device->setRelation('pollingMethods', new Collection($methods));

        return $device;
    }

    private function method(PollingMethodType $type, ?bool $lastCheck = true, bool $affectsAvailability = true): DevicePollingMethod
    {
        return new DevicePollingMethod([
            'method_type' => $type,
            'enabled' => true,
            'affects_availability' => $affectsAvailability,
            'last_check_successful' => $lastCheck,
        ]);
    }

    private function undecryptableSecret(): Secret
    {
        $secret = Mockery::mock(Secret::class)->makePartial();
        $secret->secret_type = SecretType::Snmp;
        $secret->shouldReceive('getAttribute')->with('data')->andThrow(SecretDecryptionException::failedToDecrypt('The payload is invalid.'));

        return $secret;
    }

    private function mockFping(FpingResponse $response): void
    {
        $fping = Mockery::mock(Fping::class);
        $fping->shouldReceive('ping')->andReturn($response);
        $this->app->instance(Fping::class, $fping);
    }
}
