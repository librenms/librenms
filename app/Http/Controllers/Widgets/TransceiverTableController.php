<?php

/**
 * SPDX-License-Identifier: GPL-3.0-or-later
 *
 * Follows LibreNMS WidgetController, TopInterfacesController, and HasThresholds.
 * Sensor associations follow doc/Developing/os/Health-Information.md and the
 * Junos/AXOS discovery definitions. Developed with AI assistance.
 */

namespace App\Http\Controllers\Widgets;

use App\Models\DeviceGroup;
use App\Models\EntPhysical;
use App\Models\Port;
use App\Models\PortGroup;
use App\Models\Sensor;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\Url;

class TransceiverTableController extends WidgetController
{
    protected string $name = 'transceiver-table';

    // Separate names avoid the base controller dereferencing deleted groups.
    protected $defaults = [
        'title' => '',
        'selected_device_group' => '',
        'selected_port_group' => [],
        'refresh' => 60,
    ];

    public function getTitle(): string
    {
        return __('widgets.transceiver-table.title');
    }

    public function getSettingsView(Request $request): View
    {
        $data = $this->getSettings();
        $data['deviceGroup'] = DeviceGroup::find((int) $data['selected_device_group']);
        $data['portGroups'] = PortGroup::whereIn('id', $this->portGroupIds($data))
            ->orderBy('name')->get();

        return view('widgets.settings.transceiver-table', $data);
    }

    public function getView(Request $request): View|string
    {
        $settings = $this->getSettings();
        $deviceGroup = (int) $settings['selected_device_group'];
        $portGroups = $this->portGroupIds($settings);

        if (($deviceGroup && ! DeviceGroup::whereKey($deviceGroup)->exists())
            || count($portGroups) !== PortGroup::whereIn('id', $portGroups)->count()) {
            return view('widgets.transceiver-table', [
                'rows' => [],
                'laneNumbers' => [0, 1, 2, 3],
                'notice' => __('widgets.transceiver-table.missing_group'),
            ]);
        }

        $ports = Port::hasAccess($request->user())
            ->isNotDeleted()
            ->has('device')
            ->has('transceivers')
            ->with(['device', 'transceivers'])
            ->when($deviceGroup, fn ($q) => $q->inDeviceGroup($deviceGroup))
            ->when($portGroups, fn ($q) => $q->whereHas('groups',
                fn ($groups) => $groups->whereIn('port_groups.id', $portGroups)))
            ->get();

        $rows = [];
        $laneNumbers = [0, 1, 2, 3];
        // Fetch once per device, not once per optic or lane.
        foreach ($ports->groupBy('device_id') as $deviceId => $devicePorts) {
            $sensors = Sensor::where('device_id', $deviceId)
                ->where('sensor_class', 'dbm')
                ->where('sensor_deleted', 0)
                ->orderBy('sensor_descr')
                ->get()
                ->filter(fn ($sensor) => $this->isReceiveSensor($sensor));

            $inventory = EntPhysical::where('device_id', $deviceId)
                ->get(['entPhysicalIndex', 'entPhysicalContainedIn'])
                ->keyBy('entPhysicalIndex');

            foreach ($devicePorts as $port) {
                foreach ($port->transceivers as $optic) {
                    $matchedSensors = [];
                    foreach ($sensors as $sensor) {
                        if (! $this->belongsToOptic($sensor, $port, $optic, $inventory)) {
                            continue;
                        }

                        $matchedSensors[] = $sensor;
                    }

                    $readings = $this->laneReadings($matchedSensors, $port);
                    $laneNumbers = array_unique(array_merge($laneNumbers, array_keys($readings)));

                    $rows[] = [
                        'device' => $port->device->displayName(),
                        'port' => $port->ifName ?: ($port->ifDescr ?: (string) $port->ifIndex),
                        'port_url' => Url::portUrl($port),
                        'description' => trim((string) $port->ifAlias),
                        'readings' => $readings,
                    ];
                }
            }
        }

        usort($rows, fn ($a, $b) => strnatcasecmp($a['device'], $b['device'])
            ?: strnatcasecmp($a['port'], $b['port']));

        sort($laneNumbers, SORT_NUMERIC);

        return view('widgets.transceiver-table', [
            'rows' => $rows, 'notice' => null, 'laneNumbers' => $laneNumbers,
        ]);
    }

    private function laneReadings(array $sensors, Port $port): array
    {
        $lanes = [];
        $unnumbered = [];
        foreach ($sensors as $sensor) {
            if ($port->device->os === 'junos'
                && preg_match('/^rx-\d+\.(\d+)$/', (string) $sensor->sensor_index, $match)) {
                $lane = (int) $match[1];
            } elseif (preg_match('/\blane\s*[:#-]?\s*(\d+)\b/i', (string) $sensor->sensor_descr, $match)) {
                $lane = (int) $match[1];
            } else {
                $unnumbered[] = $sensor;
                continue;
            }
            $lanes[$lane][] = $sensor;
        }

        // Prefer numbered lanes over an additional port-level aggregate.
        // A single unnumbered sensor (including AXOS PON) uses column zero.
        if ($lanes === []) {
            $lanes[0] = $unnumbered;
        }

        $readings = [];
        foreach ($lanes as $lane => $candidates) {
            if (count($candidates) === 1) {
                $readings[$lane] = $this->formatReading($candidates[0], $port);
            } else {
                $readings[$lane] = [
                    'value' => __('widgets.transceiver-table.unavailable'), 'status' => __('widgets.transceiver-table.unknown'), 'background' => '#e5e7eb', 'foreground' => '#1f2937',
                    'tooltip' => $candidates === [] ? __('widgets.transceiver-table.no_sensor')
                        : __('widgets.transceiver-table.ambiguous_lane') . ': ' . implode('; ', array_map(fn ($s) => (string) $s->sensor_descr, $candidates)),
                ];
            }
        }

        return $readings;
    }

    private function formatReading(Sensor $sensor, Port $port): array
    {
        // Stored sensor_current is already scaled to dBm.
        $value = $sensor->sensor_current;
        // AXOS integer units are 0.0001 mW; -60 is LibreNMS's zero sentinel.
        $zeroPower = $port->device->os === 'axos'
            && str_starts_with((string) $sensor->sensor_index, 'axosOltPonPortRxPower.')
            && is_numeric($value) && (float) $value === -60.0;
        $available = ! $zeroPower && is_numeric($value) && is_finite((float) $value);
        $severity = $available && $sensor->hasThresholds() ? $sensor->currentStatus() : Severity::Unknown;
        [$status, $background, $foreground] = match ($severity) {
            Severity::Ok => [__('widgets.transceiver-table.ok'), '#15803d', '#ffffff'],
            Severity::Warning => [__('widgets.transceiver-table.warning'), '#a16207', '#ffffff'],
            Severity::Error => [__('widgets.transceiver-table.critical'), '#b91c1c', '#ffffff'],
            default => [$available ? __('widgets.transceiver-table.no_thresholds') : __('widgets.transceiver-table.unknown'), '#e5e7eb', '#1f2937'],
        };
        $limits = [];
        foreach (['sensor_limit_low' => __('widgets.transceiver-table.critical_low'), 'sensor_limit_low_warn' => __('widgets.transceiver-table.warning_low'),
            'sensor_limit_warn' => __('widgets.transceiver-table.warning_high'), 'sensor_limit' => __('widgets.transceiver-table.critical_high')] as $field => $label) {
            if (is_numeric($sensor->$field)) {
                $limits[] = $label . ': ' . number_format((float) $sensor->$field, 2) . ' dBm';
            }
        }

        return [
            'value' => $zeroPower ? __('widgets.transceiver-table.zero_reported') : ($available ? number_format((float) $value, 2) . ' dBm' : __('widgets.transceiver-table.unavailable')),
            'status' => $status, 'background' => $background, 'foreground' => $foreground,
            'tooltip' => implode(' | ', array_merge([
                (string) $sensor->sensor_descr, $status,
                __('widgets.transceiver-table.last_update') . ': ' . ($sensor->lastupdate ?: __('widgets.transceiver-table.unknown')),
            ], $limits)),
        ];
    }

    private function portGroupIds(array $settings): array
    {
        // Accept the original single ID and new multi-select arrays.
        // The form sends an empty entry when all selections are cleared.
        return array_values(array_unique(array_filter(
            array_map('intval', (array) ($settings['selected_port_group'] ?? [])),
            fn ($id) => $id > 0
        )));
    }

    private function isReceiveSensor(Sensor $sensor): bool
    {
        // Direction is not a dedicated sensor column. These descriptions cover
        // common discovery labels; extend here for verified vendor labels.
        $description = (string) $sensor->sensor_descr;
        $tx = '/(?<![a-z])(?:tx|transmit(?:ted)?)(?![a-z])/i';
        $rx = '/(?<![a-z])(?:rx|receive(?:d|r)?)(?![a-z])|\b(?:input|in)\s+(?:optical\s+)?power\b/i';

        return ! preg_match($tx, $description) && (bool) preg_match($rx, $description);
    }

    private function belongsToOptic(Sensor $sensor, Port $port, $optic, $inventory): bool
    {
        $index = (string) $sensor->entPhysicalIndex;
        if ($index === '' || $index === '0') {
            return false;
        }

        // "ports" means SNMP ifIndex, NOT LibreNMS port_id.
        if ($sensor->entPhysicalIndex_measured === 'ports'
            || $sensor->group === 'transceiver') {
            // A port-level sensor cannot distinguish multiple optics on a port.
            return $port->transceivers->count() === 1
                && $index === (string) $port->ifIndex;
        }

        // In 26.9.1 these OS implementations put ifIndex in the optic's
        // entity_physical_index. It must not be treated as an inventory index.
        if (in_array($port->device->os, ['junos', 'axos'], true)) {
            return false;
        }

        if (! empty($sensor->entPhysicalIndex_measured)) {
            return false;
        }

        $target = (string) $optic->entity_physical_index;
        if ($target === '' || $target === '0') {
            return false;
        }

        // ENTITY-MIB sensors may be children of the transceiver inventory item.
        // Walk only explicit containment links within this device.
        $visited = [];
        while ($index !== '' && $index !== '0' && ! isset($visited[$index])) {
            if ($index === $target) {
                return true;
            }
            $visited[$index] = true;
            $entity = $inventory->get($index);
            if (! $entity) {
                break;
            }
            $index = (string) $entity->entPhysicalContainedIn;
        }

        return false;
    }
}
