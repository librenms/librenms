<?php

/**
 * LatencyController.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Illuminate\Http\Request;
use LibreNMS\Interfaces\UI\DeviceTab;
use LibreNMS\Util\Smokeping;

class LatencyController implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return LibrenmsConfig::get('smokeping.integration') || $device->getAttrib('override_icmp_disable') !== 'true';
    }

    public function slug(): string
    {
        return 'latency';
    }

    public function icon(): string
    {
        return 'fa-line-chart';
    }

    public function name(): string
    {
        return __('Latency');
    }

    public function data(Device $device, Request $request): array
    {
        $smokeping = new Smokeping($device);
        $smokeping_tabs = [];
        if ($smokeping->hasInGraph()) {
            $smokeping_tabs[] = 'in';
        }
        if ($smokeping->hasOutGraph()) {
            $smokeping_tabs[] = 'out';
        }

        return [
            'smokeping' => $smokeping,
            'smokeping_tabs' => $smokeping_tabs,
        ];
    }
}
