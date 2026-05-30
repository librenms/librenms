<?php

/**
 * GraphContext.php
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

use App\Models\Device;
use ArrayAccess;

/**
 * The single argument passed to every {@see GraphDefinition} method.
 *
 * Bundles the resolved {@see Device} model with the {@see GraphQuery} so graph
 * definitions have a uniform signature instead of the old mix of
 * (array $device) / (array $device, GraphQuery $query).
 *
 * Implements ArrayAccess (delegating to the Device model's attributes) so existing
 * definition bodies that read $context['device_id'], $context['hostname'], etc.
 * continue to work without per-line changes. The full Eloquent model is available
 * via $context->device when a definition needs relations or query builders, which
 * avoids the previous pattern of discarding the model with ->toArray().
 *
 * @implements ArrayAccess<string, mixed>
 */
final class GraphContext implements ArrayAccess
{
    public function __construct(
        public readonly Device $device,
        public readonly GraphQuery $query,
    ) {}

    public function offsetExists(mixed $offset): bool
    {
        return $this->device->offsetExists($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->device->getAttribute($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->device->setAttribute($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->device->offsetUnset($offset);
    }
}
