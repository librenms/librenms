<?php
/**
 * ModuleStatus.php
 *
 * -Description-
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
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Polling;

use App\Models\Device;

class ModuleStatus
{
    public function __construct(
        public bool $global,
        public ?bool $os = null,
        public ?bool $device = null,
        public ?bool $manual = null,
    ) {
    }

    public function isEnabled(): bool
    {
        if ($this->manual !== null) {
            return $this->manual;
        }

        if ($this->device !== null) {
            return $this->device;
        }

        if ($this->os !== null) {
            return $this->os;
        }

        return $this->global;
    }

    public function reason(): string
    {
        if ($this->manual !== null) {
            return 'mannually';
        }

        if ($this->device !== null) {
            return 'by device';
        }

        if ($this->os !== null) {
            return 'by OS';
        }

        return 'globally';
    }

    public function isEnabledAndDeviceUp(Device $device, bool $check_snmp = true): bool
    {
        if ($check_snmp && $device->snmp_disable) {
            return false;
        }

        return $this->isEnabled() && $device->status;
    }

    public function __toString(): string
    {
        return sprintf('Module %s: Global %s | OS %s | Device %s | Manual %s',
            $this->isEnabled() ? 'enabled' : 'disabled',
            $this->global ? '+' : '-',
            $this->os === null ? ' ' : ($this->os ? '+' : '-'),
            $this->device === null ? ' ' : ($this->device ? '+' : '-'),
            $this->manual === null ? ' ' : ($this->manual ? '+' : '-'),
        );
    }
}
