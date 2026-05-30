<?php

/**
 * PollerPerfGraph.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tristan Rhodes
 * @author     Tristan Rhodes <tristan.rhodes@gmail.com>
 */

namespace LibreNMS\Graph\Definitions\Device;

use LibreNMS\Graph\GraphSeriesDefinition;
use LibreNMS\Graph\RrdMetricBinding;

class PollerPerfGraph
{
    public const GRAPH_TYPE = 'device_poller_perf';

    public function graphType(): string
    {
        return self::GRAPH_TYPE;
    }

    /** @param array<string, mixed> $entities */
    public function title(array $entities = []): string
    {
        return 'Poller Time';
    }

    /** @param array<string, mixed> $entities */
    public function subtitle(array $entities = []): string
    {
        return $entities['hostname'] ?? '';
    }

    /** @param array<string, mixed> $entities */
    public function unit(array $entities = []): string
    {
        return 'seconds';
    }

    /**
     * @param array<string, mixed> $entities
     * @return GraphSeriesDefinition[]
     */
    public function series(array $entities = []): array
    {
        return [
            new GraphSeriesDefinition(
                name:        'Poller time',
                key:         'poller_time',
                unit:        $this->unit($entities),
                color:       '008C00',
                area:        true,
                areaOpacity: 0.2,
                bindings:    [
                    new RrdMetricBinding(rrdName: 'poller-perf', ds: 'poller'),
                ],
            ),
        ];
    }

    /**
     * @param array<string, mixed> $entities
     * @return list<array<string, mixed>>
     */
    public function markers(array $entities = []): array
    {
        return [];
    }

    /** @return array<string, mixed> */
    public function display(): array
    {
        return ['kind' => 'line', 'stacked' => false, 'area' => true, 'legend' => true];
    }
}
