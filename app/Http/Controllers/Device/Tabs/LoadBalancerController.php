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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Facades\DeviceCache;
use App\Models\Device;
use LibreNMS\Interfaces\UI\DeviceTab;

class LoadBalancerController implements DeviceTab
{
    private $tabs = [];

    public function __construct()
    {
        $device = DeviceCache::getPrimary();

        if ($device->os == 'netscaler') {
            if ($device->netscalerVservers()->exists()) {
                $this->tabs[] = 'netscaler_vsvr';
            }
        }

        // Cisco ACE
        if ($device->os == 'acsw') {
            if ($device->vServers()->exists()) {
                $this->tabs[] = 'loadbalancer_vservers';
            }
        }

        // F5 LTM
        if ($device->os == 'f5') {
            $component = new \LibreNMS\Component();
            $component_count = $component->getComponentCount($device['device_id']);

            if (isset($component_count['f5-ltm-bwc'])) {
                $this->tabs[] = 'ltm_bwc';
            }
            if (isset($component_count['f5-ltm-vs'])) {
                $this->tabs[] = 'ltm_vs';
            }
            if (isset($component_count['f5-ltm-pool'])) {
                $this->tabs[] = 'ltm_pool';
            }
            if (isset($component_count['f5-gtm-wide'])) {
                $this->tabs[] = 'gtm_wide';
            }
            if (isset($component_count['f5-gtm-pool'])) {
                $this->tabs[] = 'gtm_pool';
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

    public function data(Device $device): array
    {
        return [
            'loadbalancer_tabs' => $this->tabs,
        ];
    }
}
