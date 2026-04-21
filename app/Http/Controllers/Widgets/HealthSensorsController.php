<?php

/**
 * HealthSensorsController.php
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
 * @copyright  2026 LibreNMS Contributors
 * @author     LibreNMS Contributors
 */

namespace App\Http\Controllers\Widgets;

use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Sensor;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\View\View;
use LibreNMS\Enum\Sensor as SensorClass;

class HealthSensorsController extends WidgetController
{
    protected string $name = 'health-sensors';

    /** @var array<string, mixed> */
    protected $defaults = [
        'title' => null,
        'device_scope' => 'device',
        'device' => null,
        'device_group' => null,
        'device_regex' => '.*',
        'rows' => 4,
        'cols' => 3,
        'display_mode' => 'number',
        'sensor_class_regex' => '.*',
        'descr_regex' => '.*',
        'warning' => '',
        'critical' => '',
    ];

    public function getView(Request $request): View|string
    {
        $settings = $this->getSettings();
        $scope = (string) ($settings['device_scope'] ?? 'device');

        if ($scope === 'device' && empty($settings['device'])) {
            return $this->getSettingsView($request);
        }

        if ($scope === 'device_group' && empty($settings['device_group'])) {
            return view('widgets.health-sensors', [
                'id' => $settings['id'],
                'error' => __('Please select a device group.'),
                'sensors' => collect(),
                'display_mode' => $settings['display_mode'],
                'cols' => (int) $settings['cols'],
            ]);
        }

        if ($scope === 'device_regex') {
            $deviceRegex = trim((string)$settings['device_regex'] ?? '.*');
            if ($deviceRegex === '') {
                return view('widgets.health-sensors', [
                    'id' => $settings['id'],
                    'error' => __('Please enter a device match regex (hostname or sysName).'),
                    'sensors' => collect(),
                    'display_mode' => $settings['display_mode'],
                    'cols' => (int) $settings['cols'],
                ]);
            }
        }

        $descrBare = trim((string) ($settings['descr_regex'] ?? '.*'));
        $classBare = trim((string) ($settings['sensor_class_regex'] ?? '.*'));

        $warning = $settings['warning'] ?? null;
        $critical = $settings['critical'] ?? null;
        $maxEntries = $this->maxEntries((int) $settings['rows'], (int) $settings['cols']);

        try {
            $sensors = Sensor::hasAccess($request->user())
                ->where('sensor_deleted', 0)
                ->when($scope === 'device', fn ($q) => $q->where('device_id', (int) $settings['device']))
                ->when($scope === 'device_group', fn ($q) => $q->whereHas('device', fn ($dq) => $dq->inDeviceGroup((int) $settings['device_group'])))
                ->when($scope === 'device_regex', function ($q) use ($settings): void {
                    $pattern = trim((string) ($settings['device_regex'] ?? ''));
                    $q->whereHas('device', function ($dq) use ($pattern): void {
                        $dq->where(function ($dq) use ($pattern): void {
                            $dq->whereRaw('devices.hostname REGEXP ?', [$pattern])
                                ->orWhereRaw('devices.sysName REGEXP ?', [$pattern]);
                        });
                    });
                })
                ->when($classBare !== '.*', fn ($q) => $q->whereRaw('sensor_class REGEXP ?', [$classBare]))
                ->when($descrBare !== '.*', fn ($q) => $q->whereRaw('sensor_descr REGEXP ?', [$descrBare]))
                ->with('device')
                ->orderBy('sensor_descr')
                ->limit($maxEntries)
                ->get()
                ->map(function (Sensor $sensor) use ($warning, $critical): array {
                    $raw = $sensor->sensor_current;
                    $status = $this->thresholdStatus($raw, $warning, $critical);
                    $gaugeBounds = $this->gaugeBounds($raw, $warning, $critical);

                    return [
                        'sensor' => $sensor,
                        'status' => $status,
                        'gauge_min' => $gaugeBounds['min'],
                        'gauge_max' => $gaugeBounds['max'],
                    ];
                });
        } catch (QueryException) {
            return view('widgets.health-sensors', [
                'id' => $settings['id'],
                'error' => __('Invalid regular expression for one of the filters.'),
                'sensors' => collect(),
                'display_mode' => $settings['display_mode'],
            ]);
        }

        return view('widgets.health-sensors', [
            'id' => $settings['id'],
            'error' => null,
            'sensors' => $sensors,
            'display_mode' => $settings['display_mode'] ?? '',
            'cols' => (int) $settings['cols'],
        ]);
    }

    public function getSettings($settingsView = false): array
    {
        $settings = parent::getSettings($settingsView);
        $settings['device_scope'] = match ((string) ($settings['device_scope'] ?? 'device')) {
            'device', 'device_group', 'device_regex' => (string) $settings['device_scope'],
            default => 'device',
        };
        $settings['device'] = isset($settings['device']) && is_numeric($settings['device']) ? (int) $settings['device'] : null;
        $settings['device_group'] = isset($settings['device_group']) && is_numeric($settings['device_group']) ? (int) $settings['device_group'] : null;
        $settings['device_regex'] = trim((string) ($settings['device_regex'] ?? ''));
        $settings['rows'] = isset($settings['rows']) && is_numeric($settings['rows']) ? (int) $settings['rows'] : 4;
        $settings['cols'] = isset($settings['cols']) && is_numeric($settings['cols']) ? (int) $settings['cols'] : 3;
        $settings['rows'] = max(1, min(48, $settings['rows']));
        $settings['cols'] = max(1, min(12, $settings['cols']));

        $sensorClassRegex = trim((string) ($settings['sensor_class_regex'] ?? ''));
        $settings['sensor_class_regex'] = $sensorClassRegex === '' ? '.*' : $sensorClassRegex;

        $descrRegex = trim((string) ($settings['descr_regex'] ?? ''));
        $settings['descr_regex'] = $descrRegex === '' ? '.*' : $descrRegex;

        $settings['display_mode'] = $settings['display_mode'] ?? 'number';

        return $settings;
    }

    public function getSettingsView(Request $request): View
    {
        $settings = $this->getSettings(true);
        $settings['device'] = Device::hasAccess($request->user())->find($settings['device']) ?: null;
        $settings['device_group'] = DeviceGroup::find($settings['device_group']) ?: null;

        return view('widgets.settings.health-sensors', $settings);
    }

    private function maxEntries(int $rows, int $cols): int
    {
        $rows = max(1, min(50, $rows));
        $cols = max(1, min(12, $cols));

        // safety cap to avoid accidentally rendering huge widgets
        return max(1, min(48, $rows * $cols));
    }

    private function thresholdStatus(?float $value, ?float $warning, ?float $critical): string
    {
        if ($value === null) {
            return 'unknown';
        }

        // If the widget has no thresholds configured, don't imply "OK" status.
        if ($warning === null && $critical === null) {
            return 'unknown';
        }

        if ($critical !== null && $value >= $critical) {
            return 'critical';
        }

        if ($warning !== null && $value >= $warning) {
            return 'warning';
        }

        return 'ok';
    }

    /**
     * @return array{min: float, max: float}
     */
    private function gaugeBounds(?float $value, ?float $warning, ?float $critical): array
    {
        if ($value === null) {
            return ['min' => 0.0, 'max' => 1.0];
        }

        $points = [0.0, $value];
        if ($warning !== null) {
            $points[] = $warning;
        }
        if ($critical !== null) {
            $points[] = $critical;
        }

        $minV = min($points);
        $maxV = max($points);

        if ($minV === $maxV) {
            $maxV = $minV + 1;
        }

        $pad = ($maxV - $minV) * 0.08;
        if ($pad === 0.0) {
            $pad = 1.0;
        }

        return [
            'min' => $minV - $pad,
            'max' => $maxV + $pad,
        ];
    }
}
