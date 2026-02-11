<?php

/**
 * LogTabs.php
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

namespace App\View\Components\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LogTabs extends Component
{
    public array $tabs = [];

    public function __construct(
        public readonly Device $device,
        public readonly ?string $tab = null,
    ) {
        $this->tabs['outages'] = [
            'text' => __('Outages'),
            'link' => route('device.outages', $this->device->device_id),
        ];

        $this->tabs['eventlog'] = [
            'text' => __('Event Log'),
            'link' => route('device.eventlog', $this->device->device_id),
        ];

        if (LibrenmsConfig::get('enable_syslog')) {
            $this->tabs['syslog'] = [
                'text' => __('Syslog'),
                'link' => route('device.syslog', $this->device->device_id),
            ];
        }

        if (LibrenmsConfig::get('graylog.server')) {
            $this->tabs['graylog'] = [
                'text' => __('Graylog'),
                'link' => route('device.graylog', $this->device->device_id),
            ];
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.device.log-tabs');
    }
}
