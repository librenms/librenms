<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Polling\Method\ProbeResult;

readonly class DiscoverPollingMethod
{
    public function __construct(
        private PollingMethodRegistry $pollingMethods,
        private BuildPollingMethodCandidates $candidates,
    ) {
    }

    /**
     * Check the candidates of a polling method in order, the first that works is applied to the method.
     * The failure lists the reasons of every candidate tried.
     */
    public function execute(Device $device, DevicePollingMethod $deviceMethod): ProbeResult
    {
        $method = $this->pollingMethods->get($deviceMethod->method_type);

        $reasons = [];
        $lastResult = null;
        foreach ($this->candidates->execute($device, $deviceMethod) as $candidate) {
            $result = $method->discover($device, $candidate);

            if ($result->isSuccess()) {
                $this->candidates->apply($candidate, $deviceMethod);

                return $result;
            }

            $lastResult = $result;
            array_push($reasons, ...$result->reasons());
        }

        return ProbeResult::failure($lastResult?->stats() ?? [], $lastResult?->errorMessage(), $reasons);
    }
}
