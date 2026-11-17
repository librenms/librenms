<?php

/**
 * PseudowiresController.php
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

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Pseudowire;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Interfaces\UI\DeviceTab;

class PseudowiresController extends Controller implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return $device->pseudowires()->exists();
    }

    public function slug(): string
    {
        return 'pseudowires';
    }

    public function icon(): string
    {
        return 'fa-arrows-alt';
    }

    public function name(): string
    {
        return __('Pseudowires');
    }

    /**
     * @return array<string, mixed>
     */
    public function data(Device $device, Request $request): array
    {
        return [];
    }

    public function index(Device $device, Request $request): View
    {
        $this->authorize('view', $device);

        $request->validate([
            'view' => 'nullable|in:detail,minigraphs',
        ]);

        $view = $request->string('view')->value() ?: 'detail';

        return view('device.tabs.pseudowires', [
            'device' => $device,
            'view' => $view,
            'options' => [
                'detail' => [
                    'text' => __('Details'),
                    'link' => route('device.pseudowires', ['device' => $device, 'view' => 'detail']),
                ],
                'minigraphs' => [
                    'text' => __('Mini Graphs'),
                    'link' => route('device.pseudowires', ['device' => $device, 'view' => 'minigraphs']),
                ],
            ],
            'circuits' => Pseudowire::circuits($request->user(), $device),
        ]);
    }
}
