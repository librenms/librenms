<?php
/**
 * AlertMapController.php
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
 * @copyright  2024 Adam James
 * @author     Tony Murray <murraytony@gmail.com>
 * @author     Adam James <adam.james@transitiv.co.uk>
 */

namespace App\Http\Controllers\Widgets;

use App\Models\Device;
use App\Models\DeviceGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LibreNMS\Config;
use LibreNMS\Util\Url;

class AlertMapController extends WidgetController
{
    protected $title = 'Alert Map';

    public function __construct()
    {
        $this->defaults = [
            'title' => null,
            'type' => (int) Config::get('webui.alert_map_compact', 0),
            'tile_size' => 12,
            'display_label' => 0,
            'show_disabled_and_ignored' => 0,
            'show_ok_devices' => 1,
            'order_by' => Config::get('webui.alert_map_sort_status') ? 'status' : 'display-name',
            'device_group' => null,
        ];
    }

    public function getView(Request $request)
    {
        $data = $this->getSettings();

        [$devices, $alert_totals] = $this->getAlerts();

        $data['devices'] = $devices;
        $data['alert_totals'] = $alert_totals;

        return view('widgets.alert-map', $data);
    }

    public function getSettingsView(Request $request)
    {
        return view('widgets.settings.alert-map', $this->getSettings(true));
    }

    private function getAlerts(): array
    {
        $settings = $this->getSettings();

        // filter for by device group or show all
        if ($settings['device_group']) {
            $device_query = DeviceGroup::find($settings['device_group'])->devices()->hasAccess(Auth::user());
        } else {
            $device_query = Device::hasAccess(Auth::user());
        }

        if (! $settings['show_disabled_and_ignored']) {
            $device_query->isNotDisabled();
        }

        $devices = $device_query->with([
            'alerts' => function ($query) {
                $query->active()->select('alerts.device_id', 'rule_id', 'state');
            },
            'alerts.rule' => function ($query) {
                $query->select('id', 'severity');
            },
        ])->get();

        $totals = ['ok' => 0, 'warning' => 0, 'critical' => 0];
        $data = [];

        foreach ($devices as $device) {
            [$worst_severity, $severities] = $this->getDeviceAlerts($device);
            array_walk($severities, function ($n, $sev) use (&$totals) {
                $totals[$sev] += $n;
            });

            if (! $settings['show_ok_devices'] && $worst_severity == 'ok') {
                continue;
            }

            [$state_name, $class] = $this->parseDeviceSeverity($device, $worst_severity, $settings['type'] == 1);

            $data[] = [
                'severity' => $worst_severity,
                'link' => Url::deviceUrl($device, ['tab' => 'alerts']),
                'tooltip' => $this->getDeviceTooltip($device, $severities),
                'label' => $this->getDeviceLabel($device, $state_name, $settings['display_label']),
                'labelClass' => $class,
            ];
        }

        $this->sort($data, $settings['order_by']);

        return [$data, $totals];
    }

    private function sort(array &$data, string $order_by): void
    {
        switch ($order_by) {
            case 'severity':
                usort($data, function ($l, $r) {
                    // reverse sort as worse severities have higher values
                    return $this->alertSeverityValue($r['severity']) <=> $this->alertSeverityValue($l['severity']) ?:
                        strcasecmp($l['label'], $r['label']);
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

    private function getDeviceLabel(Device $device, string $severity, int $label_type): string
    {
        switch ($label_type) {
            case 1:
                return '';
            case 4:
                return $device->shortDisplayName();
            case 2:
                return strtolower($device->hostname);
            case 3:
                return strtolower($device->sysName);
            default:
                // translation entries for severities are capitalised, e.g. Ok, Warning etc.
                return __(ucfirst($severity));
        }
    }

    private function getDeviceTooltip(Device $device, array $alert_severities): string
    {
        $tooltip = $device->displayName();

        $tooltip .= sprintf(
            ' - %s: %d / %s: %d / %s: %d',
            __('Ok'),
            $alert_severities['ok'],
            __('Warning'),
            $alert_severities['warning'],
            __('Critical'),
            $alert_severities['critical'],
        );

        return $tooltip;
    }

    private function parseDeviceSeverity(Device $device, string $severity, bool $compact = false): array
    {
        if ($device->disabled) {
            return ['disabled', 'blackbg'];
        } elseif ($device->ignore) {
            return ['ignored-ok', $compact ? 'alert-map-compact-ok' : 'label-success'];
        }

        if ($severity == 'ok') {
            return ['ok', $compact ? 'alert-map-compact-ok' : 'label-success'];
        } elseif ($severity == 'warning') {
            return ['warning', $compact ? 'alert-map-compact-warning' : 'label-warning'];
        }

        return ['critical', $compact ? 'alert-map-compact-critical' : 'label-danger'];
    }

    private function getDeviceAlerts(Device $device): array
    {
        $worst_severity = 'ok';

        $severities = [
            'ok' => 0,
            'warning' => 0,
            'critical' => 0,
        ];

        foreach ($device->alerts as $alert) {
            $severities[$alert['rule']['severity']]++;
        }

        if ($severities['critical'] > 0) {
            $worst_severity = 'critical';
        } elseif ($severities['warning'] > 0) {
            $worst_severity = 'warning';
        }

        return [$worst_severity, $severities];
    }

    private static function alertSeverityValue(string $severity): int
    {
        switch ($severity) {
            case 'ok':
                return 0;
            case 'warning':
                return 1;
            case 'critical':
                return 2;
        }

        return -1;
    }
}
