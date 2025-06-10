<?php

/**
 * PerDeviceProcess.php
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App;

use App\Models\Device;
use Illuminate\Support\Facades\Event;
use LibreNMS\Enum\ProcessType;
use LibreNMS\Polling\Result;
use LibreNMS\Util\ModuleList;

class PerDeviceProcess
{
    private ?int $current_device_id = null;
    private Result $results;
    private ModuleList $moduleList;

    public function __construct(
        public readonly ProcessType $type,
        private readonly string $deviceSpec,
        private readonly string $job,
        private readonly string $completionEvent,
        array $overrides,
    ) {
        $this->results = new Result;
        $this->moduleList = new ModuleList($type, $overrides);
    }

    public function run(): Result
    {
        $this->moduleList->printOverrides();

        // listen for the completed events to mark the device completed
        Event::listen($this->completionEvent, function ($event) {
            if ($event->device->device_id == $this->current_device_id) {
                $this->results->markCompleted($event->device->status);
            }
        });
        $dispatcher = app(\Illuminate\Contracts\Bus\Dispatcher::class);

        foreach (Device::whereDeviceSpec($this->deviceSpec)->pluck('device_id') as $device_id) {
            $this->current_device_id = $device_id;
            $this->results->markAttempted();
            $dispatcher->dispatchSync(new $this->job($device_id, $this->moduleList));
        }

        return $this->results;
    }
}
