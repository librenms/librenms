<?php

namespace LibreNMS\Tests\Feature\Polling;

use App\Models\Device;
use App\Models\Mempool;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Method\Methods\UnixAgentPollingMethod;
use LibreNMS\Polling\Providers\CapabilityProviders;
use LibreNMS\Polling\Providers\MempoolsProvider;
use LibreNMS\Polling\Providers\MethodProvider;
use LibreNMS\Tests\InMemoryDbTestCase;
use LibreNMS\Tests\Traits\FakesPollingMethods;

final class CapabilityProvidersTest extends InMemoryDbTestCase
{
    use FakesPollingMethods;

    public function testModuleChoosesTheOrderWithoutCheckingMethods(): void
    {
        $os = $this->makeOs([PollingMethodType::UnixAgent, PollingMethodType::Ipmi, PollingMethodType::Snmp]);
        $providers = $os->capability(MempoolsProvider::class);

        // ipmi does not provide mempools, unlisted methods follow the preferred ones
        $this->assertSame([PollingMethodType::Snmp, PollingMethodType::UnixAgent], $providers->methods());
        $this->assertSame([PollingMethodType::UnixAgent, PollingMethodType::Snmp], $providers->prefer(PollingMethodType::UnixAgent)->methods());
        $this->assertFalse($this->wasChecked($os, PollingMethodType::Snmp));
        $this->assertFalse($this->wasChecked($os, PollingMethodType::UnixAgent));
    }

    public function testPreferredDoesNotFallBackButFirstAvailableDoes(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp, PollingMethodType::UnixAgent]);
        $this->fakeMethod(SnmpPollingMethod::class, false, $this->fakeMempools('hrstorage'));
        $agent = $this->fakeMempools('unix-agent');
        $this->fakeMethod(UnixAgentPollingMethod::class, true, $agent);
        $providers = $os->capability(MempoolsProvider::class);

        $this->assertNull($providers->preferred());
        $this->assertSame(PollingMethodType::UnixAgent, $providers->firstAvailable()?->type);
        $this->assertSame($agent, $providers->firstAvailable()->provider);
        $this->assertSame($agent, $providers->for(PollingMethodType::UnixAgent));
        $this->assertNull($providers->for(PollingMethodType::Snmp));
    }

    public function testAllHasEveryAvailableMethodInOrder(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp, PollingMethodType::UnixAgent]);
        $this->fakeMethod(SnmpPollingMethod::class, true, $this->fakeMempools('hrstorage'));
        $this->fakeMethod(UnixAgentPollingMethod::class, true, $this->fakeMempools('unix-agent'));

        $types = fn (CapabilityProviders $providers) => array_map(fn (MethodProvider $provider) => $provider->type, $providers->all());

        $this->assertSame([PollingMethodType::Snmp, PollingMethodType::UnixAgent], $types($os->capability(MempoolsProvider::class)));
        $this->assertSame([PollingMethodType::UnixAgent, PollingMethodType::Snmp], $types($os->capability(MempoolsProvider::class)->prefer(PollingMethodType::UnixAgent)));
    }

    public function testForModelsGroupsModelsOfAvailableMethods(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp, PollingMethodType::UnixAgent]);
        $this->fakeMethod(SnmpPollingMethod::class, false);
        $this->fakeMethod(UnixAgentPollingMethod::class, true, $this->fakeMempools('unix-agent'));
        $this->mempool($os->getDevice(), PollingMethodType::Snmp, 'hrstorage');
        $agentMempool = $this->mempool($os->getDevice(), PollingMethodType::UnixAgent, 'unix-agent');

        $groups = $os->capability(MempoolsProvider::class)->forModels($os->getDevice()->mempools);

        $this->assertCount(1, $groups);
        [$methodProvider, $mempools] = $groups[0];
        $this->assertSame(PollingMethodType::UnixAgent, $methodProvider->type);
        $this->assertSame([$agentMempool->mempool_id], $mempools->modelKeys());
    }

    private function mempool(Device $device, PollingMethodType $type, string $mempoolType): Mempool
    {
        $mempool = Mempool::factory()->create(['device_id' => $device->device_id, 'mempool_type' => $mempoolType, 'mempool_index' => '1', 'polling_method' => $type]);
        $device->unsetRelation('mempools');

        return $mempool;
    }
}
