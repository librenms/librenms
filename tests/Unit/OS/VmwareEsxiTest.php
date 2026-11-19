<?php

namespace LibreNMS\Tests\Unit\OS;

use App\Data\TimeSeries\MetricCollector;
use App\Models\Vminfo;
use Illuminate\Support\Collection;
use LibreNMS\OS\VmwareEsxi;
use LibreNMS\Tests\TestCase;

final class VmwareEsxiTest extends TestCase
{
    public function testAFailedWalkKeepsTheVms(): void
    {
        $vms = new Collection([new Vminfo(['vm_type' => 'vmware', 'vmwVmVMID' => 1])]);

        $this->assertSame($vms, $this->os(new Collection)->pollVminfo($vms, new MetricCollector));
    }

    public function testPollReturnsTheWalkedVms(): void
    {
        $walked = new Collection([new Vminfo(['vm_type' => 'vmware', 'vmwVmVMID' => 2])]);
        $vms = new Collection([new Vminfo(['vm_type' => 'vmware', 'vmwVmVMID' => 1])]);

        $this->assertSame($walked, $this->os($walked)->pollVminfo($vms, new MetricCollector));
    }

    /**
     * @param  Collection<int, Vminfo>  $walked
     */
    private function os(Collection $walked): VmwareEsxi
    {
        $device = ['device_id' => 1, 'os' => 'vmware-esxi'];

        return new class($device, $walked) extends VmwareEsxi
        {
            /**
             * @param  array<mixed>  $device
             * @param  Collection<int, Vminfo>  $walked
             */
            public function __construct(array &$device, private readonly Collection $walked)
            {
                parent::__construct($device);
            }

            /**
             * @return Collection<int, Vminfo>
             */
            public function discoverVmInfo(): Collection
            {
                return $this->walked;
            }
        };
    }
}
