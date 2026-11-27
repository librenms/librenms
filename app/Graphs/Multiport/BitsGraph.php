<?php

namespace App\Graphs\Multiport;

use App\Data\Graphing\GraphPageContext;
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

    protected function validation(): array
    {
        return ['id' => ['nullable', 'regex:/^\d+(,\d+)*$/']];
    }

    protected function isAuthorized(): bool
    {
        $ports = $this->ports();

        return $ports->count() === count($this->portIds())
            && $ports->every(fn (Port $port) => $port->device !== null && $this->canView($port));
    }

    public function getPageContext(): GraphPageContext
    {
        return new GraphPageContext(subtitle: __('graph.multiport.subtitle'));
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
     * @return Collection<int, Port>
     */
    private function ports(): Collection
    {
        return $this->ports ??= Port::with('device')->whereIntegerInRaw('port_id', $this->portIds())->get()
            ->sortBy(fn (Port $port) => array_search($port->port_id, $this->portIds(), true))
            ->values();
    }

    /**
     * @return list<int>
     */
    private function portIds(): array
    {
        $ids = $this->params->input->string('id') ?? '';

        return $ids === '' ? [] : array_values(array_unique(array_map(intval(...), explode(',', $ids))));
    }
}
