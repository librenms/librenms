<?php

namespace LibreNMS\Interfaces\Polling;

use App\Models\VmPort;
use Illuminate\Support\Collection;
use LibreNMS\Data\Metrics\MetricCollector;

interface VmPortPolling
{
    /**
     * @param  Collection<int, VmPort>  $ports
     * @param  MetricCollector<VmPort>  $metrics
     * @return Collection<int, VmPort>
     */
    public function pollVmPorts(Collection $ports, MetricCollector $metrics): Collection;
}
