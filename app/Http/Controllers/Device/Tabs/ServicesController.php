<?php

/**
 * ServicesController.php
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
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Interfaces\UI\DeviceTab;
use LibreNMS\Util\Time;

class ServicesController implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return Gate::allows('viewAny', Service::class) && $device->services()->exists();
    }

    public function slug(): string
    {
        return 'services';
    }

    public function icon(): string
    {
        return 'fa-cogs';
    }

    public function name(): string
    {
        return __('Services');
    }

    public function data(Device $device, Request $request): array
    {
        Gate::authorize('view', $device);
        abort_if(Gate::none(['service.view', 'service.viewAll']), 403);

        $validated = $request->validate([
            'view' => 'nullable|string|in:basic,details',
        ]);
        $view = $validated['view'] ?? 'basic';

        $services = $device->services()
            ->hasAccess($request->user())
            ->get()
            ->map(function ($service) use ($view) {
                $statusClass = match ((int) $service->service_status) {
                    0 => 'label-success',
                    1 => 'label-warning',
                    2 => 'label-danger',
                    default => 'label-info',
                };

                $graphs = [];
                if ($view === 'details') {
                    $serviceDs = htmlspecialchars_decode((string) $service->service_ds);
                    $checkScript = LibrenmsConfig::get('install_dir') . '/includes/services/check_' . strtolower((string) $service->service_type) . '.inc.php';
                    if (is_file($checkScript)) {
                        include $checkScript;
                        if (isset($check_ds) && is_string($check_ds)) {
                            $serviceDs = $check_ds;
                        }
                    }

                    $decoded = json_decode($serviceDs, true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $k => $v) {
                            $graphTitle = isset($v['full_name']) ? (string) $v['full_name'] : (string) $k;
                            $graphs[] = [
                                'title' => $graphTitle,
                                'ds' => $k,
                            ];
                        }
                    }
                }

                return [
                    'service' => $service,
                    'status_class' => $statusClass,
                    'last_changed' => $service->service_changed ? Time::formatInterval(time() - $service->service_changed) : __('Waiting for first check'),
                    'graphs' => $graphs,
                ];
            });

        $options = [
            'basic' => [
                'text' => __('Basic'),
                'link' => route('device', ['device' => $device, 'tab' => 'services', 'view' => 'basic']),
            ],
            'details' => [
                'text' => __('Details'),
                'link' => route('device', ['device' => $device, 'tab' => 'services', 'view' => 'details']),
            ],
        ];

        return [
            'view' => $view,
            'options' => $options,
            'services' => $services,
        ];
    }
}
