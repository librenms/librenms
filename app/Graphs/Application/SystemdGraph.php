<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

/**
 * A graph of systemd unit counts, a line for each state status of the drawn state types of the
 * $systemd_mapper in includes/systemd-shared.inc.php. A state type keeps its statuses in
 * app-systemd-<id>-<state_type>.rrd, a status mapped as individual has its own file
 * app-systemd-<id>-<state_type>-<status>.rrd.
 */
abstract class SystemdGraph extends ApplicationGraph
{
    /**
     * The state types drawn
     *
     * @param  array<string, array<string, string>>  $systemdMapper  state type => [status => 'shared' or 'individual']
     * @return list<string>
     */
    abstract protected function stateTypes(array $systemdMapper): array;

    /**
     * Whether the series labels are <state_type>_<status> instead of just the status
     */
    abstract protected function prefixLabels(): bool;

    public function getSeries(): array
    {
        require base_path('includes/systemd-shared.inc.php');
        $systemdMapper = $systemd_mapper ?? [];

        $series = [];
        foreach ($this->stateTypes($systemdMapper) as $stateType) {
            foreach ($systemdMapper[$stateType] ?? [] as $status => $rrdLocation) {
                $tags = $rrdLocation === 'individual' ? ['state_type' => $stateType, 'status' => $status] : ['state_type' => $stateType];
                $label = $this->prefixLabels() ? "{$stateType}_$status" : $status;
                $series["{$stateType}_$status"] = new DataSeries($this->metric($tags), $status, $label);
            }
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.application.systemd.units'), palette: 'psychedelic');
    }
}
