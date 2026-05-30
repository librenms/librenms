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

use LibreNMS\Graph\GraphContext;
use LibreNMS\Graph\GraphDefinition;
use LibreNMS\Graph\GraphSeriesDefinition;
use LibreNMS\Graph\RrdMetricBinding;

class PollerPerfGraph implements GraphDefinition
{
    public const GRAPH_TYPE = 'device_poller_perf';

    public function graphType(): string
    {
        return self::GRAPH_TYPE;
    }

    public function id(GraphContext $context): string
    {
        return self::GRAPH_TYPE . ':' . $context['device_id'];
    }

    public function title(GraphContext $context): string
    {
        return 'Poller Time';
    }

    public function subtitle(GraphContext $context): string
    {
        return $context['hostname'] ?? '';
    }

    public function unit(GraphContext $context): string
    {
        return 'seconds';
    }

    public function series(GraphContext $context): array
    {
        return [
            new GraphSeriesDefinition(
                name:        'Poller time',
                key:         'poller_time',
                unit:        $this->unit($context),
                color:       '008C00',
                area:        true,
                areaOpacity: 0.2,
                bindings:    [
                    new RrdMetricBinding(rrdName: 'poller-perf', ds: 'poller'),
                ],
            ),
        ];
    }

    public function markers(GraphContext $context): array
    {
        return [];
    }

    public function entityType(): string
    {
        return 'device';
    }

    public function display(): array
    {
        return ['kind' => 'line', 'stacked' => false, 'area' => true, 'legend' => true];
    }

    public function variables(): array
    {
        return [];
    }
}
