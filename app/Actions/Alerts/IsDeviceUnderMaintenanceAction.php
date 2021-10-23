<?php
/**
 * IsDeviceUnderMaintenanceAction.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Actions\Alerts;

use App\Models\AlertSchedule;
use App\Models\Device;
use Illuminate\Database\Eloquent\Builder;

class IsDeviceUnderMaintenanceAction
{
    /**
     * @var \App\Models\Device
     */
    private $device;

    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    public function execute(): bool
    {
        if (! $this->device->exists) {
            return false;
        }

        if (! isset($this->device->properties['maintenance'])) {
            $query = AlertSchedule::isActive()
                ->where(function (Builder $query) {
                    $query->whereHas('devices', function (Builder $query) {
                        $query->where('alert_schedulables.alert_schedulable_id', $this->device->device_id);
                    });

                    if ($this->device->groups->isNotEmpty()) {
                        $query->orWhereHas('deviceGroups', function (Builder $query) {
                            $query->whereIn('alert_schedulables.alert_schedulable_id', $this->device->groups->pluck('id'));
                        });
                    }

                    if ($this->device->location) {
                        $query->orWhereHas('locations', function (Builder $query) {
                            $query->where('alert_schedulables.alert_schedulable_id', $this->device->location->id);
                        });
                    }
                });

            $this->device->properties['maintenance'] = $query->exists();
        }

        return $this->device->properties['maintenance'];
    }
}
