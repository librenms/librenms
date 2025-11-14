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
use App\Polling\Measure\MeasurementManager;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\Event;
use LibreNMS\Enum\ProcessType;
use LibreNMS\Polling\Result;
use LibreNMS\Util\ModuleList;

class PerDeviceProcess
{
    private ?int $current_device_id = null;
    public Result $results;

    public function __construct(
        public readonly ProcessType $type,
        private readonly string $deviceSpec,
        private readonly string $job,
        private readonly string $completionEvent,
        private readonly ModuleList $moduleList,
    ) {
        $this->results = new Result;
    }

    public function run(): Result
    {
        $this->moduleList->printOverrides($this->type);

        // listen for the completed events to mark the device completed
        Event::listen($this->completionEvent, function ($event): void {
            if ($event->device->device_id == $this->current_device_id) {
                $this->results->markCompleted($event->device->status);
            }
        });
        $dispatcher = app(Dispatcher::class);

        foreach (Device::whereDeviceSpec($this->deviceSpec)->pluck('device_id') as $device_id) {
            $this->current_device_id = $device_id;
            $this->results->markAttempted();
            $dispatcher->dispatchSync(new $this->job($device_id, $this->moduleList));
        }

        return $this->results;
    }

    public function processResults(MeasurementManager $measurements, OutputStyle $output): int
    {
        $type = $this->type->verb(); // discover or poll
        $translation_prefix = 'commands.device:' . $type;

        if ($this->results->hasAnyCompleted()) {
            if (! $output->isQuiet()) {
                if ($this->results->hasMultipleCompleted()) {
                    $output->newLine();
                    $time_spent = sprintf('%0.3fs', $measurements->getCategory('device')->getSummary($type)->getDuration());
                    $output->writeln(__($translation_prefix . '.actioned', ['count' => $this->results->getCompleted(), 'time' => $time_spent]));
                }
                $output->newLine();
                $measurements->printStats();
            }

            return 0;
        }

        // 0 devices actioned, maybe there were none
        if ($this->results->hasNoAttempts()) {
            if ($this->deviceSpec == 'new') {
                $output->writeln(__('commands.errors.no_new_devices'));

                return 0; // no new devices is normal
            }

            $output->writeln('<error>' . __('commands.errors.no_devices') . '</error>');

            return 1;
        }

        // attempted some devices, but none were up.
        if ($this->results->hasNoCompleted()) {
            $output->writeln('<fg=red>' . trans_choice($translation_prefix . '.errors.none_up', $this->results->getAttempted()) . '</>');

            return 6;
        }

        $output->writeln('<error>' . __($translation_prefix . '.errors.none_actioned') . '</error>');

        return 1; // failed
    }
}
