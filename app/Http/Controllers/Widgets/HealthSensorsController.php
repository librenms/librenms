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
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

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
            $deviceRegex = trim((string) ($settings['device_regex'] ?? '.*'));
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

        $userWarning = $settings['warning'] ?? null;
        $userCritical = $settings['critical'] ?? null;
        $maxEntries = $this->maxEntries((int) $settings['rows'], (int) $settings['cols']);

        try {
            $sensors = Sensor::hasAccess($request->user())
                ->where('sensor_deleted', 0)
                ->when($scope === 'device', fn ($q) => $q->where('device_id', (int) $settings['device']))
                ->when($scope === 'device_group', fn ($q) => $q->whereIn('device_id', DB::table('device_group_device')->select('device_id')->where('device_group_id', (int) $settings['device_group'])))
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
                ->map(function (Sensor $sensor) use ($userWarning, $userCritical): array {
                    $raw = $sensor->sensor_current;

                    // If the user didn't specify thresholds, fall back to sensor limits (if set)
                    $useSensorLimits = $userWarning === null && $userCritical === null;
                    $highWarn = $useSensorLimits ? ($sensor->sensor_limit_warn) : $userWarning;
                    $highCrit = $useSensorLimits ? ($sensor->sensor_limit) : $userCritical;
                    $lowWarn = $useSensorLimits ? ($sensor->sensor_limit_low_warn) : null;
                    $lowCrit = $useSensorLimits ? ($sensor->sensor_limit_low) : null;

                    $status = $this->thresholdStatus($raw, $lowWarn, $lowCrit, $highWarn, $highCrit);
                    $gaugeBounds = $this->gaugeBounds($raw, $lowWarn, $lowCrit, $highWarn, $highCrit);

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

    /**
     * @return array<string, mixed>
     */
    public function getSettings($settingsView = false): array
    {
        $settings = parent::getSettings($settingsView);
        $settings['device_scope'] = match ((string) ($settings['device_scope'] ?? 'device')) {
            'device', 'device_group', 'device_regex' => (string) $settings['device_scope'],
            default => 'device',
        };
        $settings['device'] = isset($settings['device']) && is_numeric($settings['device']) ? (int) $settings['device'] : null;
        if (! ($settingsView && ($settings['device_group'] ?? null) instanceof DeviceGroup)) {
            $settings['device_group'] = isset($settings['device_group']) && is_numeric($settings['device_group'])
                ? (int) $settings['device_group']
                : null;
        }
        $settings['device_regex'] = trim((string) ($settings['device_regex'] ?? ''));
        $settings['rows'] = isset($settings['rows']) && is_numeric($settings['rows']) ? (int) $settings['rows'] : 4;
        $settings['cols'] = isset($settings['cols']) && is_numeric($settings['cols']) ? (int) $settings['cols'] : 3;
        $settings['rows'] = max(1, min(48, $settings['rows']));
        $settings['cols'] = max(1, min(12, $settings['cols']));

        $sensorClassRegex = trim((string) ($settings['sensor_class_regex'] ?? ''));
        $settings['sensor_class_regex'] = $sensorClassRegex === '' ? '.*' : $sensorClassRegex;

        $descrRegex = trim((string) ($settings['descr_regex'] ?? ''));
        $settings['descr_regex'] = $descrRegex === '' ? '.*' : $descrRegex;

        $settings['display_mode'] ??= 'number';

        return $settings;
    }

    public function getSettingsView(Request $request): View
    {
        $settings = $this->getSettings(true);
        $settings['device'] = Device::hasAccess($request->user())->find($settings['device']) ?: null;

        return view('widgets.settings.health-sensors', $settings);
    }

    private function maxEntries(int $rows, int $cols): int
    {
        $rows = max(1, min(50, $rows));
        $cols = max(1, min(12, $cols));

        // safety cap to avoid accidentally rendering huge widgets
        return max(1, min(48, $rows * $cols));
    }

    private function thresholdStatus(?float $value, ?float $lowWarn, ?float $lowCrit, ?float $highWarn, ?float $highCrit): string
    {
        if ($value === null) {
            return 'unknown';
        }

        // If there are no thresholds configured, don't imply "OK" status.
        if ($lowWarn === null && $lowCrit === null && $highWarn === null && $highCrit === null) {
            return 'unknown';
        }

        if ($highCrit !== null && $value >= $highCrit) {
            return 'critical';
        }

        if ($lowCrit !== null && $value <= $lowCrit) {
            return 'critical';
        }

        if ($highWarn !== null && $value >= $highWarn) {
            return 'warning';
        }

        if ($lowWarn !== null && $value <= $lowWarn) {
            return 'warning';
        }

        return 'ok';
    }

    /**
     * @return array{min: float, max: float}
     */
    private function gaugeBounds(?float $value, ?float $lowWarn, ?float $lowCrit, ?float $highWarn, ?float $highCrit): array
    {
        if ($value === null) {
            return ['min' => 0.0, 'max' => 1.0];
        }

        $points = [$value];
        if ($lowWarn !== null) {
            $points[] = $lowWarn;
        }
        if ($lowCrit !== null) {
            $points[] = $lowCrit;
        }
        if ($highWarn !== null) {
            $points[] = $highWarn;
        }
        if ($highCrit !== null) {
            $points[] = $highCrit;
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
