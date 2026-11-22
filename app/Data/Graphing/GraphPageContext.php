<?php

namespace App\Data\Graphing;

use App\Models\Device;
use App\Models\Port;

/**
 * What the graph page shows around a graph: the graphed entity in the heading and an optional subtitle
 */
final readonly class GraphPageContext
{
    /**
     * @param  Device|null  $device  linked in the heading, null when the graph is not of one device
     * @param  Port|null  $port  linked in the heading after the device
     * @param  string|null  $subtitle  heading text after the entity, null to use the graph type description
     */
    public function __construct(
        public ?Device $device = null,
        public ?Port $port = null,
        public ?string $subtitle = null,
    ) {
    }
}
