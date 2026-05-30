<?php

/**
 * GraphDefinition.php
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

namespace LibreNMS\Graph;

/**
 * Every method receives a single {@see GraphContext} carrying the resolved Device model
 * and the GraphQuery. $context behaves like the old $device array (ArrayAccess) and also
 * exposes $context->device (model) and $context->query (GraphQuery). The entity-specific
 * keys required for a query live in $context->query->entities and vary by graph type.
 */
interface GraphDefinition
{
    public function graphType(): string;

    public function id(GraphContext $context): string;

    public function title(GraphContext $context): string;

    public function subtitle(GraphContext $context): string;

    public function unit(GraphContext $context): string;

    /**
     * Return the series to render for this graph.
     *
     * The context's query lets definitions make time-range decisions (e.g. skip
     * the weekly average line when the window is less than 8 days).
     *
     * @return GraphSeriesDefinition[]
     */
    public function series(GraphContext $context): array;

    /**
     * Horizontal reference lines rendered on the graph (e.g. sensor alert thresholds).
     * Each entry: ['type' => 'horizontal_line', 'name' => string, 'value' => float, 'severity' => 'warning'|'critical']
     */
    public function markers(GraphContext $context): array;

    /**
     * The primary entity type this graph belongs to ('device', 'port', 'sensor', 'wireless_sensor', 'bill').
     * Informational — used for routing context and debug identification.
     * URL construction is handled explicitly via GraphDataUrl static helpers, not by dispatching on this value.
     */
    public function entityType(): string;

    /**
     * Frontend renderer hints for this graph type.
     * Merged with base display defaults before serialization.
     * Keys: kind ('line'|'bar'), stacked (bool), area (bool)
     */
    public function display(): array;

    /**
     * Typed request parameters with defaults and validation bounds.
     * Resolved from GraphQuery::$options before series/markers are evaluated.
     *
     * @return GraphVariableDefinition[]
     */
    public function variables(): array;
}
