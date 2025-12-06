<?php

/**
 * LoadBalancerController.php
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

use App\Facades\DeviceCache;
use App\Models\Device;
use Illuminate\Http\Request;
use LibreNMS\Component;
use LibreNMS\Interfaces\UI\DeviceTab;

class LoadBalancerController implements DeviceTab
{
    private array $tabs = [];
    private array $counts = [];

    public function __construct()
    {
        $device = DeviceCache::getPrimary();

        if ($device->os == 'netscaler') {
            $count = $device->netscalerVservers()->count();
            if ($count > 0) {
                $this->addTab('netscaler_vsvr', $count);
            }
        }

        // Cisco ACE
        if ($device->os == 'acsw') {
            $count = $device->vServers()->count();
            if ($count > 0) {
                $this->addTab('loadbalancer_vservers', $count);
            }
        }

        // F5 LTM
        if ($device->os == 'f5') {
            $component = new Component();
            $component_count = $component->getComponentCount($device['device_id']);

            if (! empty($component_count['f5-ltm-bwc'])) {
                $this->addTab('ltm_bwc', (int) $component_count['f5-ltm-bwc']);
            }
            if (! empty($component_count['f5-ltm-vs'])) {
                $this->addTab('ltm_vs', (int) $component_count['f5-ltm-vs']);
            }
            if (! empty($component_count['f5-ltm-pool'])) {
                $this->addTab('ltm_pool', (int) $component_count['f5-ltm-pool']);
            }
            if (! empty($component_count['f5-gtm-wide'])) {
                $this->addTab('gtm_wide', (int) $component_count['f5-gtm-wide']);
            }
            if (! empty($component_count['f5-gtm-pool'])) {
                $this->addTab('gtm_pool', (int) $component_count['f5-gtm-pool']);
            }
            if (! empty($component_count['f5-cert'])) {
                $this->addTab('f5-cert', (int) $component_count['f5-cert']);
            }
        }

        if ($device->os == 'alteonos') {
            $stateSensorsQuery = $device->sensors()->where('sensor_class', 'state');

            $tabs = [
                'alteonos_real_servers' => ['slbEnhRealServer', 'slbRealServer'],
                'alteonos_real_groups' => ['slbOperEnhGroupRealServerRuntime', 'slbOperEnhGroupRealServerRuntimeStatus', 'slbOperGroupRealServer', 'slbOperGroupRealServerState'],
                'alteonos_virtual_servers' => ['slbCurCfgEnhVirtServer', 'slbCurCfgVirtServer'],
                'alteonos_virtual_services' => [
                    'slbVirtServices',
                    'slbVirtServicesInfo',
                    'slbVirtServicesInfoState',
                    'slbCurCfgEnhVirtService',
                    'slbCurCfgEnhVirtServiceStatus',
                    'slbCurCfgVirtService',
                    'slbCurCfgVirtServiceStatus',
                ],
            ];

            foreach ($tabs as $tab => $types) {
                $query = clone $stateSensorsQuery;
                $query->whereIn('sensor_type', $types);

                if ($tab === 'alteonos_real_groups') {
                    $count = $query->pluck('sensor_index')
                        ->map(fn ($index) => explode('.', ltrim((string) $index, '.'))[0] ?? null)
                        ->filter()
                        ->unique()
                        ->count();
                } else {
                    $count = $query->count();
                }

                if ($count > 0) {
                    $this->addTab($tab, $count);
                }
            }
        }
    }

    public function visible(Device $device): bool
    {
        return ! empty($this->tabs);
    }

    public function slug(): string
    {
        return 'loadbalancer';
    }

    public function icon(): string
    {
        return 'fa-balance-scale';
    }

    public function name(): string
    {
        return __('Load Balancer');
    }

    public function data(Device $device, Request $request): array
    {
        return [
            'loadbalancer_tabs' => $this->tabs,
            'device_loadbalancer_count' => $this->counts,
        ];
    }

    private function addTab(string $tab, int $count = 0): void
    {
        $this->tabs[] = $tab;
        $this->counts[$tab] = $count;
    }
}
