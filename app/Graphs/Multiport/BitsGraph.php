<?php

namespace App\Graphs\Multiport;

use App\Data\Graphing\GraphPageContext;
use App\Data\Graphing\GraphParameters;
use App\Graphs\AbstractSeriesGraph;
use App\Graphs\Port\StacksPortTraffic;
use App\Models\Port;
use Illuminate\Support\Collection;

/**
 * Traffic of the comma separated port ids in the id input
 */
class BitsGraph extends AbstractSeriesGraph
{
    use StacksPortTraffic;

    /** @var Collection<int, Port>|null */
    private ?Collection $ports = null;
    /** @var list<int>|null */
    private ?array $portIds = null;

    protected function validation(): array
    {
        return ['id' => ['nullable', 'regex:' . GraphParameters::ID_LIST_PATTERN]];
    }

    protected function isAuthorized(): bool
    {
        $ports = $this->ports();

        return $ports->count() === count($this->portIds())
            && $ports->every(fn (Port $port) => $port->device !== null && $this->canView($port));
    }

    public function getPageContext(): GraphPageContext
    {
        $ports = $this->ports();
        if ($ports->isEmpty()) {
            return new GraphPageContext(subtitle: $this->getTitle());
        }

        $devices = $ports->pluck('device')->filter()->unique('device_id');

        return new GraphPageContext(subtitle: __('graph.multiport.subtitle', [
            'ports' => trans_choice('graph.multiport.ports', $ports->count()),
            'devices' => trans_choice('graph.multiport.devices', $devices->count(), ['device' => $devices->first()?->shortDisplayName()]),
        ]));
    }

    public function getTitle(): string
    {
        return __('graph.multiport.title');
    }

    public function getSeries(): array
    {
        return $this->portTrafficSeries($this->ports(), fn (Port $port) => $port->device?->shortDisplayName() . ' ' . $port->getShortLabel());
    }

    /**
     * @return Collection<int, Port> in the requested order
     */
    private function ports(): Collection
    {
        if ($this->ports === null) {
            $found = Port::with('device')->whereIntegerInRaw('port_id', $this->portIds())->get()->keyBy('port_id');
            $this->ports = collect($this->portIds())->map(fn (int $id) => $found->get($id))->filter()->values();
        }

        return $this->ports;
    }

    /**
     * @return list<int>
     */
    private function portIds(): array
    {
        $ids = $this->params->input->string('id') ?? '';

        return $this->portIds ??= $ids === '' ? [] : array_values(array_unique(array_map(intval(...), explode(',', $ids))));
    }
}
