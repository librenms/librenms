<?php

/**
 * HostLocation.php
 *
 * A forwarding table entry for a host and how close that port is to it
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Search\Data;

use App\Models\Port;
use App\Models\PortsFdb;
use Carbon\Carbon;

class HostLocation
{
    public function __construct(
        public readonly string $mac,
        public readonly int $portId,
        public readonly ?Port $port,
        public readonly int $vlan,
        public readonly int $macCount,
        public readonly ?Carbon $updatedAt,
    ) {
    }

    /**
     * @param  PortsFdb  $entry  selected with its mac_count and the port's links_count
     */
    public static function fromFdb(PortsFdb $entry): self
    {
        return new self($entry->mac_address, (int) $entry->port_id, $entry->port, (int) $entry->vlan, (int) $entry->mac_count, $entry->updated_at);
    }

    public function isUplink(): bool
    {
        return $this->port?->links_count > 0;
    }

    /**
     * Ports that can't be called the host's port: links to other network devices and aggregates.
     * An aggregate may be a bonded server, but most carry traffic for a whole switch.
     */
    public function isTrunk(): bool
    {
        return $this->isUplink() || $this->port?->ifType === 'ieee8023adLag';
    }

    public function state(): LocationState
    {
        return match (true) {
            $this->isTrunk() => LocationState::Trunk,
            $this->macCount === 1 => LocationState::Direct,
            default => LocationState::Shared,
        };
    }

    public function portDescription(): ?string
    {
        return self::describePort($this->port);
    }

    /**
     * The port's description, when it says more than its name
     */
    public static function describePort(?Port $port): ?string
    {
        return $port && $port->getDescription() != $port->getLabel() ? $port->getDescription() : null;
    }
}
