<?php

namespace App\Http\Controllers\Table;

use App\Models\MdadmArray;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LibreNMS\Util\Number;
use LibreNMS\Util\Url;

/**
 * @extends TableController<MdadmArray>
 */
class MdadmArrayController extends TableController
{
    protected ?string $model = MdadmArray::class;

    protected array $default_sort = ['name' => 'asc'];

    protected function rules(): array
    {
        return [];
    }

    protected function sortFields(Request $request): array
    {
        return [
            'array_name',
            'name',
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

    protected function searchFields(Request $request): array
    {
        return [
            'array_name',
            'name',
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
            ->when($request->input('app_id'), fn ($q, $id) => $q->where('app_id', $id));
    }

    /**
     * @param  MdadmArray  $model
     */
    public function formatItem(Model $model): array
    {
        $dev    = $model->application->device ?? null;
        $arrUrl = $dev ? Url::generate([
            'page'   => 'device',
            'device' => $dev->device_id,
            'tab'    => 'apps',
            'app'    => 'mdadm',
            'array'  => $model->name ?? $model->uuid,
        ]) : '#';

        $stateClass = match (strtolower((string) ($model->state ?? ''))) {
            'clean', 'active'                             => 'default',
            'degraded'                                    => 'danger',
            'recovering', 'resyncing', 'checking', 'sync' => 'warning',
            default                                       => 'default',
        };

        $failedCnt  = (int) ($model->failed_devices ?? 0);
        $mismatchCnt = (int) ($model->mismatch_cnt ?? 0);

        $linkText = $model->array_name ?? $model->uuid;

        return [
            'device'         => $dev ? Url::modernDeviceLink($dev) : '?',
            'array_name'     => '<a href="' . htmlspecialchars($arrUrl) . '">' . htmlspecialchars($linkText) . '</a>',
            'name'           => $model->name !== null
                ? htmlspecialchars($model->name)
                : '<span class="text-muted">&mdash;</span>',
            'level'          => htmlspecialchars((string) ($model->level ?? '')),
            'state'          => '<span class="label label-' . $stateClass . '">'
                . htmlspecialchars((string) ($model->state ?? '')) . '</span>',
            'sync_action'    => htmlspecialchars((string) ($model->sync_action ?? 'idle')),
            'raid_disks'     => $model->raid_disks,
            'active_devices' => $model->active_devices,
            'spare_devices'  => $model->spare_devices,
            'failed_devices' => '<span class="label label-' . ($failedCnt > 0 ? 'danger' : 'default') . '">'
                . $failedCnt . '</span>',
            'size'           => ($model->size_bytes ?? 0) > 0
                ? Number::formatBi((int) $model->size_bytes)
                : '&mdash;',
            'mismatch_cnt'   => '<span class="label label-' . ($mismatchCnt > 0 ? 'warning' : 'default') . '">'
                . $mismatchCnt . '</span>',
        ];
    }

    protected function getExportHeaders(): array
    {
        return [
            'Device',
            'Array Name',
            'MD Device',
            'Level',
            'State',
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
            $model->name ?? '',
            $model->level ?? '',
            $model->state ?? '',
            $model->sync_action ?? 'idle',
            $model->raid_disks,
            $model->active_devices,
            $model->spare_devices,
            $model->failed_devices,
            ($model->size_bytes ?? 0) > 0 ? Number::formatBi((int) $model->size_bytes) : '',
            $model->mismatch_cnt,
        ];
    }
}
