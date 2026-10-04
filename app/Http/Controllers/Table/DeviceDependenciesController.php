<?php

namespace App\Http\Controllers\Table;

use App\Models\Device;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LibreNMS\Util\Url;

/**
 * @extends TableController<Device>
 */
class DeviceDependenciesController extends TableController
{
    /** @var array<string, 'asc'|'desc'> */
    protected array $default_sort = ['hostname' => 'asc'];

    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('device.update');

        return Device::query()->with('parents');
    }

    /**
     * @return array<int|string, string|string[]>
     */
    protected function searchFields(Request $request): array
    {
        return ['hostname', 'sysName', 'display', 'parents' => ['hostname', 'sysName', 'display']];
    }

    /**
     * @return array<string, string>
     */
    protected function sortFields(Request $request): array
    {
        return [
            'device_id' => 'device_id',
            'hostname' => 'hostname',
        ];
    }

    /**
     * @param  Device  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        $parents = $model->parents->sortBy('hostname');

        return [
            'device_id' => $model->device_id,
            'hostname' => Url::deviceLink($model) . '<br />' . e($model->sysName),
            'parents' => $parents->isEmpty()
                ? __('None')
                : $parents->map(fn (Device $parent) => Url::deviceLink($parent))->implode(', '),
            'parents_json' => (string) json_encode($parents->map(fn (Device $parent) => [
                'id' => $parent->device_id,
                'text' => $parent->display,
            ])->values()),
            'display_name' => $model->display,
        ];
    }
}
