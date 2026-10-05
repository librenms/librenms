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
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Config\UnixAgentConfig;
use LibreNMS\Polling\Method\Definitions\UnixAgentDefinition;
use LibreNMS\Polling\Method\Methods\PollingMethod;
use LibreNMS\Polling\Method\Methods\UnixAgentPollingMethod;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Polling\PerDeviceMethodResults;
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

        $status = app(CheckDeviceAvailability::class)->execute(new PerDeviceMethodResults($device));

        $this->assertFalse($status);
        $this->assertFalse($snmp->last_check_successful);
        $this->assertNotNull($snmp->last_check_message);
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

        $methodResults = new PerDeviceMethodResults($device);
        $this->assertTrue(app(CheckDeviceAvailability::class)->execute($methodResults));
        $this->assertTrue($snmp->last_check_successful); // not checked yet, the first module that needs it checks it
        $this->assertSame('', $device->status_reason);

        $this->assertFalse($methodResults->isAvailable(PollingMethodType::Snmp));
        $this->assertFalse($snmp->last_check_successful);
        $this->assertSame('Failed to decrypt credentials for snmp polling. Verify that APP_KEY matches the primary installation.', $snmp->last_check_message);
        $this->assertTrue($device->status);
    }

    public function testMethodIsCheckedOnceForAllModules(): void
    {
        $agent = new FakePollingMethod([true, false]);
        $this->app->instance(UnixAgentPollingMethod::class, $agent);
        $method = $this->method(PollingMethodType::UnixAgent, lastCheck: null, affectsAvailability: false);
        $device = $this->device([$method]);
        $methodResults = new PerDeviceMethodResults($device);

        app(CheckDeviceAvailability::class)->execute($methodResults);
        $this->assertSame(0, $agent->probes); // does not affect availability

        $this->assertTrue($methodResults->isAvailable(PollingMethodType::UnixAgent));
        $this->assertSame('output', $methodResults->result(PollingMethodType::UnixAgent)?->stat('output'));
        $this->assertSame(1, $agent->probes);
        $this->assertTrue($method->last_check_successful);

        // a new poll checks again
        $this->assertFalse((new PerDeviceMethodResults($device))->isAvailable(PollingMethodType::UnixAgent));
        $this->assertSame(2, $agent->probes);
    }

    public function testDisabledMethodIsNotChecked(): void
    {
        $agent = new FakePollingMethod([true]);
        $this->app->instance(UnixAgentPollingMethod::class, $agent);
        $method = $this->method(PollingMethodType::UnixAgent);
        $method->enabled = false;
        $methodResults = new PerDeviceMethodResults($this->device([$method]));

        app(CheckDeviceAvailability::class)->execute($methodResults);

        $this->assertFalse($methodResults->isAvailable(PollingMethodType::UnixAgent));
        $this->assertNull($methodResults->result(PollingMethodType::UnixAgent));
        $this->assertSame(0, $agent->probes);
    }

    public function testProbeExceptionMarksMethodFailed(): void
    {
        $icmp = $this->method(PollingMethodType::Icmp);
        $device = $this->device([$icmp]);

        $fping = Mockery::mock(Fping::class);
        $fping->shouldReceive('ping')->andThrow(new \RuntimeException('fping exploded'));
        $this->app->instance(Fping::class, $fping);

        $this->assertFalse(app(CheckDeviceAvailability::class)->execute(new PerDeviceMethodResults($device)));
        $this->assertFalse($icmp->last_check_successful);
        $this->assertSame('icmp', $device->status_reason);
    }

    public function testDeviceWithoutPollingMethodsKeepsItsStatus(): void
    {
        $device = $this->device([]);
        $device->status = false;
        $device->status_reason = 'icmp';
        $device->syncOriginal();

        $this->assertFalse(app(CheckDeviceAvailability::class)->execute(new PerDeviceMethodResults($device)));
        $this->assertFalse($device->status);
        $this->assertSame('icmp', $device->status_reason);
        $this->assertFalse($device->isDirty());
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

/**
 * @extends PollingMethod<UnixAgentConfig>
 */
final class FakePollingMethod extends PollingMethod
{
    public int $probes = 0;

    /**
     * @param  bool[]  $results
     */
    public function __construct(private array $results)
    {
    }

    public function probe(Device $device, PollingMethodConfig $config): ProbeResult
    {
        $this->probes++;

        return array_shift($this->results) ? ProbeResult::success(['output' => 'output']) : ProbeResult::failure(errorMessage: 'refused');
    }

    public function definition(): UnixAgentDefinition
    {
        return new UnixAgentDefinition;
    }

    public function defaultAffectsAvailability(): bool
    {
        return false;
    }

    public function defaults(?Device $device = null): array
    {
        return [];
    }

    public function config(Device $device, ?DevicePollingMethod $deviceMethod = null): UnixAgentConfig
    {
        return new UnixAgentConfig(port: 6556, timeout: 1);
    }
}
