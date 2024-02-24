<?php
/**
 * AvailabilityMapController.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Widgets;

use App\Models\AlertSchedule;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LibreNMS\Config;
use LibreNMS\Util\Url;

class AvailabilityMapController extends WidgetController
{
    protected $title = 'Availability Map';

    public function __construct()
    {
        $this->defaults = [
            'title' => null,
            'type' => (int) Config::get('webui.availability_map_compact', 0),
            'tile_size' => 12,
            'color_only_select' => 0,
            'show_disabled_and_ignored' => 0,
            'mode_select' => 0,
            'order_by' => Config::get('webui.availability_map_sort_status') ? 'status' : 'display-name',
            'device_group' => null,
        ];
    }

    public function getView(Request $request)
    {
        $data = $this->getSettings();

        [$devices, $device_totals] = $this->getDevices();
        [$services, $services_totals] = $this->getServices();

        $data['devices'] = $devices;
        $data['device_totals'] = $device_totals;
        $data['services'] = $services;
        $data['services_totals'] = $services_totals;

        return view('widgets.availability-map', $data);
    }

    public function getSettingsView(Request $request)
    {
        return view('widgets.settings.availability-map', $this->getSettings(true));
    }

    private function getDevices(): array
    {
        $settings = $this->getSettings();

        if ($settings['mode_select'] == 1) { // services only
            return [[], []];
        }

        // filter for by device group or show all
        if ($settings['device_group']) {
            $device_query = DeviceGroup::find($settings['device_group'])->devices()->hasAccess(Auth::user());
        } else {
            $device_query = Device::hasAccess(Auth::user());
        }

        if (! $settings['show_disabled_and_ignored']) {
            $device_query->isNotDisabled();
        }
        $devices = $device_query->select(['devices.device_id', 'hostname', 'sysName', 'display', 'status', 'uptime', 'last_polled', 'disabled', 'ignore', 'ignore_status'])->get();

        // process status
        $uptime_warn = (int) Config::get('uptime_warning', 86400);
        $check_maintenance = AlertSchedule::isActive()->exists(); // check if any maintenance schedule is active
        // TODO: take a deeper look, why key ignored still has to exist
        $totals = ['warn' => 0, 'up' => 0, 'down' => 0, 'maintenance' => 0, 'ignored' => 0, 'ignored-up' => 0, 'ignored-down' => 0, 'disabled' => 0];
        $data = [];

        foreach ($devices as $device) {
            // parse state and count
            [$state_name, $class] = $this->parseDeviceState($device, $uptime_warn);
            $totals[$state_name]++;

            if ($check_maintenance && $device->isUnderMaintenance()) {
                $class = 'label-default';
                $totals['maintenance']++;
            }

            if ($settings['type'] == 1) {
                $class = "availability-map-oldview-box-$state_name";
            }

            $data[] = [
                'status' => $device->status,
                'link' => Url::deviceUrl($device),
                'tooltip' => $this->getDeviceTooltip($device, $state_name),
                'label' => $this->getDeviceLabel($device, $state_name), // add another field for the selected label
                'labelClass' => $class,
            ];
        }

        $this->sort($data);

        return [$data, $totals];
    }

    private function getServices(): array
    {
        $settings = $this->getSettings();

        if ($settings['mode_select'] == 0) { // devices only
            return [[], []];
        }

        // filter for by device group or show all
        if ($settings['device_group']) {
            $services_query = DeviceGroup::find($settings['device_group'])->services()->hasAccess(Auth::user());
        } else {
            $services_query = Service::hasAccess(Auth::user());
        }

        $services = $services_query->with([
            'device' => function ($query) {
                $query->select(['devices.device_id', 'hostname', 'sysName', 'display']);
            },
        ])->select(['service_id', 'services.device_id', 'service_type', 'service_name', 'service_desc', 'service_status'])->get();

        // process status
        $totals = ['warn' => 0, 'up' => 0, 'down' => 0];
        $data = [];
        foreach ($services as $service) {
            [$state_name, $class] = $this->parseServiceState($service);
            $totals[$state_name]++;

            if ($settings['type'] == 1) {
                $class = "availability-map-oldview-box-$state_name";
            }

            $data[] = [
                'status' => $service->service_status,
                'link' => Url::deviceUrl($service->device),
                'tooltip' => $this->getServiceTooltip($service),
                'label' => $this->getServiceLabel($service, $state_name),
                'labelClass' => $class,
            ];
        }

        $this->sort($data);

        return [$data, $totals];
    }

    private function sort(array &$data): void
    {
        switch ($this->getSettings()['order_by']) {
            case 'status':
                usort($data, function ($l, $r) {
                    return ($l['status'] <=> $r['status']) ?: strcasecmp($l['label'], $r['label']);
                });
                break;
            case 'label':
                usort($data, function ($l, $r) {
                    return strcasecmp($l['label'], $r['label']);
                });
                break;
            default: // device display name (tooltip starts with the display name)
                usort($data, function ($l, $r) {
                    return strcasecmp($l['tooltip'], $r['tooltip']) ?: strcasecmp($l['label'], $r['label']);
                });
        }
    }

    private function getDeviceLabel(Device $device, string $state_name): string
    {
        switch ($this->getSettings()['color_only_select']) {
            case 1:
                return '';
            case 4:
                return $device->shortDisplayName();
            case 2:
                return strtolower($device->hostname);
            case 3:
                return strtolower($device->sysName);
            default:
                return __($state_name);
        }
    }

    private function getServiceLabel(Service $service, string $state_name): string
    {
        if ($this->getSettings()['color_only_select'] == 1) {
            return '';
        }

        return $service->service_type . ' - ' . __($state_name);
    }

    private function getDeviceTooltip(Device $device, string $state_name): string
    {
        $tooltip = $device->displayName();

        if (! $device->status && ! $device->last_polled) {
            $time = __('Never polled');
        } else {
            $time = $device->formatDownUptime(true);
        }

        if ($time) {
            $tooltip .= ' - ' . ($state_name == 'down' ? 'downtime ' : '') . $time;
        }

        return $tooltip;
    }

    private function getServiceTooltip(Service $service): string
    {
        $tooltip = $service->device->displayName() . ' [' . $service->service_type . ']';

        $description = $service->service_name ?: $service->service_desc;
        if ($description) {
            $tooltip .= ' ' . $description;
        }

        return $tooltip;
    }

    private function parseDeviceState(Device $device, int $uptime_warn): array
    {
        if ($device->disabled) {
            return ['disabled', 'blackbg'];
        }

        if ($device->ignore_status) {
            return ['ignored-up', 'label-success'];
        }

        if ($device->ignore) {
            if (($device->status == 1) && ($device->uptime != 0)) {
                return ['ignored-up', 'label-success'];
            }

            return ['ignored-down', 'label-default'];
        }

        if ($device->status == 1) {
            if (($device->uptime < $uptime_warn) && ($device->uptime != 0)) {
                return ['warn', 'label-warning'];
            }

            return ['up', 'label-success'];
        }

        return ['down', 'label-danger'];
    }

    private function parseServiceState(Service $service): array
    {
        if ($service->service_status == 0) {
            return ['up', 'label-success'];
        }

        if ($service->service_status == 1) {
            return ['warn', 'label-warning'];
        }

        return ['down', 'label-danger'];
    }
}
