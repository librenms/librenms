<?php
/**
 * EditTabs.php
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
use Illuminate\Support\Facades\Request;
use Illuminate\View\Component;

class EditTabs extends Component
{
    public array $tabs;
    public string $tab;

    public function __construct(
        public Device $device,
        ?string $tab = null,
    ) {
        $this->tab = $tab ?? Request::segment(4, 'edit');

        $this->tabs = [
            'edit' => [
                'text' => __('Device Settings'),
                'link' => route('device.edit', $this->device->device_id),
            ],
            'snmp' => [
                'text' => 'SNMP',
                'link' => url('/device/device=' . $this->device->device_id . '/tab=edit/section=snmp/')
            ]
        ];

        if (! $device->snmp_disable) {
            $this->tabs['ports'] = [
                'text' => __('Port Settings'),
                'link' => url('/device/device=' . $this->device->device_id . '/tab=edit/section=ports/')
            ];
        }

        if ($device->bgppeers()->exists()) {
            $this->tabs['routing'] = [
                'text' => __('Routing'),
                'link' => url('/device/device=' . $this->device->device_id . '/tab=edit/section=routing/')
            ];
        }

        if (count(LibrenmsConfig::get("os.{$device->os}.icons", []))) {
            $this->tabs['icon'] = [
                'text' => __('Icon'),
                'link' => url('/device/device=' . $this->device->device_id . '/tab=edit/section=icon/')
            ];
        }

        if (! $device->snmp_disable) {
            $this->tabs['apps'] = [
                'text' => __('Applications'),
                'link' => url('/device/device=' . $this->device->device_id . '/tab=edit/section=apps/')
            ];
        }

        $this->tabs['alert-rules'] = [
            'text' => __('Alert Rules'),
            'link' => url('/device/device=' . $this->device->device_id . '/tab=edit/section=alert-rules/')
        ];

        if (! $device->snmp_disable) {
            $this->tabs['modules'] = [
                'text' => __('Modules'),
                'link' => url('/device/device=' . $this->device->device_id . '/tab=edit/section=modules/')
            ];
        }

        if (LibrenmsConfig::get('show_services')) {
            $this->tabs['services'] = [
                'text' => __('Services'),
                'link' => url('/device/device=' . $this->device->device_id . '/tab=edit/section=services/')
            ];
        }

        $this->tabs['ipmi'] = [
            'text' => __('IPMI'),
            'link' => url('/device/device=' . $this->device->device_id . '/tab=edit/section=ipmi/')
        ];

        if ($this->device->sensors()->exists()) {
            $this->tabs['health'] = [
                'text' => __('Health'),
                'link' => url('/device/device=' . $this->device->device_id . '/tab=edit/section=health/')
            ];
        }

        if ($this->device->wirelessSensors()->exists()) {
            $this->tabs['wireless-sensors'] = [
                'text' => __('Wireless Sensors'),
                'link' => url('/device/device=' . $this->device->device_id . '/tab=edit/section=wireless-sensors/')
            ];
        }

        if (! $device->snmp_disable) {
            $this->tabs['storage'] = [
                'text' => __('Storage'),
                'link' => url('/device/device=' . $this->device->device_id . '/tab=edit/section=storage/')
            ];
            $this->tabs['processors'] = [
                'text' => __('Processors'),
                'link' => url('/device/device=' . $this->device->device_id . '/tab=edit/section=processors/')
            ];
            $this->tabs['mempools'] = [
                'text' => __('Memory'),
                'link' => url('/device/device=' . $this->device->device_id . '/tab=edit/section=mempools/')
            ];
        }

        $this->tabs['misc'] = [
            'text' => __('Misc'),
            'link' => url('/device/device=' . $this->device->device_id . '/tab=edit/section=misc/')
        ];

        $this->tabs['component'] = [
            'text' => __('Components'),
            'link' => url('/device/device=' . $this->device->device_id . '/tab=edit/section=component/')
        ];

        $this->tabs['customoid'] = [
            'text' => __('Custom OID'),
            'link' => url('/device/device=' . $this->device->device_id . '/tab=edit/section=customoid/')
        ];
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        return view('components.device.edit-tabs');
    }
}
