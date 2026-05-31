<?php

namespace App\Http\Controllers\Table;

use App\Models\MdadmArray;
use App\Models\Sensor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LibreNMS\Enum\SensorState;
use LibreNMS\Util\Number;
use LibreNMS\Util\Url;

/**
 * @extends TableController<MdadmArray>
 */
class MdadmArrayController extends TableController
{
    protected ?string $model = MdadmArray::class;

    /** @var array<string, string> */
    protected array $default_sort = ['md_id' => 'asc'];

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * @return list<string>
     */
    protected function sortFields(Request $request): array
    {
        return [
            'array_name',
            'md_id',
            'uuid',
            'level',
            'state',
            'sync_action',
            'raid_disks',
            'active_devices',
            'spare_devices',
            'failed_devices',
            'size_bytes',
            'mismatch_cnt',
        ];
    }

    /**
     * @return array<int|string, string|list<string>>
     */
    protected function searchFields(Request $request): array
    {
        return [
            'array_name',
            'md_id',
            'uuid',
            'level',
            'state',
            'sync_action',
            'application.device' => ['hostname'],
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        return MdadmArray::query()
            ->with(['application.device'])
            ->when($request->input('app_id'), fn ($q, $id) => $q->where('app_id', $id))
            ->addSelect([
                'mdadm_arrays.*',
                'health_label' => Sensor::select('st.state_descr')
                    ->join('sensors_to_state_indexes as stsi', 'stsi.sensor_id', '=', 'sensors.sensor_id')
                    ->join('state_translations as st', fn ($j) => $j
                        ->on('st.state_index_id', '=', 'stsi.state_index_id')
                        ->whereColumn('st.state_value', 'sensors.sensor_current'))
                    ->whereColumn('sensors.device_id', 'mdadm_arrays.device_id')
                    ->where('sensors.sensor_type', 'mdadm_array_health_status')
                    ->whereRaw('sensors.`group` = CONCAT("Mdadm ", mdadm_arrays.md_id)')
                    ->limit(1),
                'health_generic' => Sensor::select('st.state_generic_value')
                    ->join('sensors_to_state_indexes as stsi', 'stsi.sensor_id', '=', 'sensors.sensor_id')
                    ->join('state_translations as st', fn ($j) => $j
                        ->on('st.state_index_id', '=', 'stsi.state_index_id')
                        ->whereColumn('st.state_value', 'sensors.sensor_current'))
                    ->whereColumn('sensors.device_id', 'mdadm_arrays.device_id')
                    ->where('sensors.sensor_type', 'mdadm_array_health_status')
                    ->whereRaw('sensors.`group` = CONCAT("Mdadm ", mdadm_arrays.md_id)')
                    ->limit(1),
                'op_label' => Sensor::select('st.state_descr')
                    ->join('sensors_to_state_indexes as stsi', 'stsi.sensor_id', '=', 'sensors.sensor_id')
                    ->join('state_translations as st', fn ($j) => $j
                        ->on('st.state_index_id', '=', 'stsi.state_index_id')
                        ->whereColumn('st.state_value', 'sensors.sensor_current'))
                    ->whereColumn('sensors.device_id', 'mdadm_arrays.device_id')
                    ->where('sensors.sensor_type', 'mdadm_array_operation_status')
                    ->whereRaw('sensors.`group` = CONCAT("Mdadm ", mdadm_arrays.md_id)')
                    ->limit(1),
                'op_generic' => Sensor::select('st.state_generic_value')
                    ->join('sensors_to_state_indexes as stsi', 'stsi.sensor_id', '=', 'sensors.sensor_id')
                    ->join('state_translations as st', fn ($j) => $j
                        ->on('st.state_index_id', '=', 'stsi.state_index_id')
                        ->whereColumn('st.state_value', 'sensors.sensor_current'))
                    ->whereColumn('sensors.device_id', 'mdadm_arrays.device_id')
                    ->where('sensors.sensor_type', 'mdadm_array_operation_status')
                    ->whereRaw('sensors.`group` = CONCAT("Mdadm ", mdadm_arrays.md_id)')
                    ->limit(1),
                'health_sensor_id' => Sensor::select('sensors.sensor_id')
                    ->whereColumn('sensors.device_id', 'mdadm_arrays.device_id')
                    ->where('sensors.sensor_type', 'mdadm_array_health_status')
                    ->whereRaw('sensors.`group` = CONCAT("Mdadm ", mdadm_arrays.md_id)')
                    ->limit(1),
                'op_sensor_id' => Sensor::select('sensors.sensor_id')
                    ->whereColumn('sensors.device_id', 'mdadm_arrays.device_id')
                    ->where('sensors.sensor_type', 'mdadm_array_operation_status')
                    ->whereRaw('sensors.`group` = CONCAT("Mdadm ", mdadm_arrays.md_id)')
                    ->limit(1),
            ]);
    }

    /**
     * @param  MdadmArray  $model
     */
    public function formatItem(Model $model): array
    {
        $dev = $model->application->device ?? null;
        $arrUrl = $dev ? Url::generate([
            'page' => 'device',
            'device' => $dev->device_id,
            'tab' => 'apps',
            'app' => 'mdadm',
            'array' => $model->md_id ?? $model->uuid,
        ]) : '#';

        $failedCnt = (int) ($model->failed_devices ?? 0);
        $mismatchCnt = (int) ($model->mismatch_cnt ?? 0);

        $sensorClass = static fn (?string $generic): string => $generic !== null ? match ((int) $generic) {
            SensorState::Ok->value => 'default',
            SensorState::Warning->value => 'warning',
            SensorState::Error->value => 'danger',
            default => 'default',
        } : 'default';

        $healthLabel = $model->health_label;
        $healthClass = $sensorClass($model->health_generic);
        $healthSensorId = $model->health_sensor_id;
        $opLabel = $model->op_label;
        $opClass = $sensorClass($model->op_generic);
        $opSensorId = $model->op_sensor_id;

        $sensorBadge = static fn (?string $label, string $class, ?int $sensorId): string => $label !== null
            ? ($sensorId !== null
                ? Url::graphPopup(['type' => 'sensor_state', 'id' => $sensorId],
                    '<span class="label label-' . $class . '">' . htmlspecialchars($label) . '</span>')
                : '<span class="label label-' . $class . '">' . htmlspecialchars($label) . '</span>')
            : '<span class="text-muted">&mdash;</span>';

        $linkText = $model->array_name ?? $model->uuid;

        return [
            'device' => $dev ? Url::modernDeviceLink($dev) : '?',
            'array_name' => '<a href="' . htmlspecialchars((string) $arrUrl) . '">' . htmlspecialchars($linkText) . '</a>',
            'md_id' => $model->md_id !== null
                ? '<a href="' . htmlspecialchars((string) $arrUrl) . '">' . htmlspecialchars($model->md_id) . '</a>'
                : '<span class="text-muted">&mdash;</span>',
            'level' => $model->level !== null
                ? '<a href="' . htmlspecialchars((string) $arrUrl) . '">' . htmlspecialchars((string) $model->level) . '</a>'
                : '',
            'state' => '<span class="label label-' . $healthClass . '">'
                . htmlspecialchars((string) ($model->state ?? '')) . '</span>',
            'health' => $sensorBadge($healthLabel, $healthClass, $healthSensorId !== null ? (int) $healthSensorId : null),
            'sync_action' => $sensorBadge($opLabel, $opClass, $opSensorId !== null ? (int) $opSensorId : null),
            'raid_disks' => $model->raid_disks,
            'active_devices' => $model->active_devices,
            'spare_devices' => $model->spare_devices,
            'failed_devices' => '<span class="label label-' . ($failedCnt > 0 ? 'danger' : 'default') . '">'
                . $failedCnt . '</span>',
            'size' => ($model->size_bytes ?? 0) > 0
                ? Number::formatBi((int) $model->size_bytes)
                : '&mdash;',
            'mismatch_cnt' => '<span class="label label-' . ($mismatchCnt > 0 ? 'warning' : 'default') . '">'
                . $mismatchCnt . '</span>',
        ];
    }

    /**
     * @return list<string>
     */
    protected function getExportHeaders(): array
    {
        return [
            'Device',
            'Array Name',
            'MDid',
            'Level',
            'State',
            'Health',
            'Operation',
            'Disks',
            'Active',
            'Spare',
            'Failed',
            'Size',
            'Mismatches',
        ];
    }

    /**
     * @param  MdadmArray  $model
     */
    protected function formatExportRow(Model $model): array
    {
        $dev = $model->application->device ?? null;

        return [
            $dev ? $dev->hostname : '',
            $model->array_name ?? '',
            $model->md_id ?? '',
            $model->level ?? '',
            $model->state ?? '',
            (string) ($model->health_label ?? ''),
            (string) ($model->op_label ?? $model->sync_action ?? 'idle'),
            $model->raid_disks,
            $model->active_devices,
            $model->spare_devices,
            $model->failed_devices,
            ($model->size_bytes ?? 0) > 0 ? Number::formatBi((int) $model->size_bytes) : '',
            $model->mismatch_cnt,
        ];
    }
}
