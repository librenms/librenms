<?php

namespace App\Graphs\Vminfo;

use App\Data\Graphing\GraphPageContext;
use App\Data\TimeSeries\MetricIdentity;
use App\Graphs\AbstractSeriesGraph;
use App\Models\Vminfo;
use LibreNMS\Exceptions\NoGraphData;

/**
 * A graph of one virtual machine, viewable by users who can view the virtual machine
 */
abstract class VminfoGraph extends AbstractSeriesGraph
{
    private ?Vminfo $vminfo = null;

    protected function isAuthorized(): bool
    {
        $vminfo = $this->findVminfo();

        return $vminfo !== null && $this->canView($vminfo);
    }

    protected function validation(): array
    {
        return ['id' => 'required|integer'];
    }

    public function getPageContext(): GraphPageContext
    {
        $vminfo = $this->findVminfo();

        return new GraphPageContext($vminfo?->device, subtitle: $vminfo ? " :: VM :: $vminfo->vmwVmDisplayName" : null);
    }

    public function getTitle(): string
    {
        $vminfo = $this->vminfo();

        return $vminfo->device?->shortDisplayName() . '::' . $vminfo->vmwVmDisplayName;
    }

    /**
     * @throws NoGraphData
     */
    protected function vminfo(): Vminfo
    {
        return $this->findVminfo() ?? throw new NoGraphData;
    }

    /**
     * @throws NoGraphData
     */
    protected function metric(string $measurement): MetricIdentity
    {
        return $this->vminfo()->metricIdentity($measurement);
    }

    private function findVminfo(): ?Vminfo
    {
        return $this->params->id === null ? null : ($this->vminfo ??= Vminfo::with('device')->find($this->params->id));
    }
}
