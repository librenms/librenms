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
use App\Models\Sensor;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\View\View;
use LibreNMS\Enum\Sensor as SensorClass;
use ValueError;

class HealthSensorsController extends WidgetController
{
    protected string $name = 'health-sensors';

    /** @var array<string, mixed> */
    protected $defaults = [
        'title' => null,
        'device' => null,
        'rows' => 4,
        'cols' => 3,
        'display_mode' => 'number',
        'sensor_class' => SensorClass::Temperature->value,
        'descr_regex' => '.*',
        'warning' => '',
        'critical' => '',
    ];

    public function getTitle(): string
    {
        $settings = $this->getSettings();
        if (! empty($settings['title'])) {
            return (string) $settings['title'];
        }

        return parent::getTitle();
    }

    public function getView(Request $request): View|string
    {
        $settings = $this->getSettings();
        if (empty($settings['device'])) {
            return $this->getSettingsView($request);
        }

        $regex = (string) ($settings['descr_regex'] ?? '.*');
        $regex = $regex === '' ? '.*' : $regex;

        $warning = $this->parseOptionalFloat($settings['warning'] ?? null);
        $critical = $this->parseOptionalFloat($settings['critical'] ?? null);
        $maxEntries = $this->maxEntries((int) $settings['rows'], (int) $settings['cols']);

        try {
            $sensors = Sensor::hasAccess($request->user())
                ->where('sensor_deleted', 0)
                ->where('device_id', (int) $settings['device'])
                ->where('sensor_class', $settings['sensor_class'])
                ->when($regex !== '.*', fn ($q) => $q->whereRaw('sensor_descr REGEXP ?', [$regex]))
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
                'error' => __('Invalid regular expression for sensor description filter.'),
                'sensors' => collect(),
                'display_mode' => $settings['display_mode'],
            ]);
        }

        return view('widgets.health-sensors', [
            'id' => $settings['id'],
            'error' => null,
            'sensors' => $sensors,
            'display_mode' => $settings['display_mode'] ?? '',
            'sensor_class_enum' => SensorClass::from((string) $settings['sensor_class']),
            'cols' => (int) $settings['cols'],
        ]);
    }

    public function getSettings($settingsView = false): array
    {
        $settings = parent::getSettings($settingsView);
        $settings['device'] = isset($settings['device']) && is_numeric($settings['device']) ? (int) $settings['device'] : null;
        $settings['rows'] = isset($settings['rows']) && is_numeric($settings['rows']) ? (int) $settings['rows'] : 4;
        $settings['cols'] = isset($settings['cols']) && is_numeric($settings['cols']) ? (int) $settings['cols'] : 3;
        $settings['rows'] = max(1, min(50, $settings['rows']));
        $settings['cols'] = max(1, min(12, $settings['cols']));
        $settings['sensor_class'] = SensorClass::tryFrom((string) ($settings['sensor_class'] ?? ''))?->value
            ?? SensorClass::Temperature->value;
        $settings['display_mode'] = match ($settings['display_mode'] ?? '') {
            'number', 'progress-bar', 'gauge', 'graph' => $settings['display_mode'],
            default => 'number',
        };

        return $settings;
    }

    public function getSettingsView(Request $request): View
    {
        $settings = $this->getSettings(true);
        $settings['device'] = Device::hasAccess($request->user())->find($settings['device']) ?: null;
        $settings['sensor_classes'] = collect(SensorClass::cases())
            ->sortBy(fn (SensorClass $c) => $c->value)
            ->values();

        return view('widgets.settings.health-sensors', $settings);
    }

    private function parseOptionalFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function maxEntries(int $rows, int $cols): int
    {
        $rows = max(1, min(50, $rows));
        $cols = max(1, min(12, $cols));

        // safety cap to avoid accidentally rendering huge widgets
        return max(1, min(240, $rows * $cols));
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
