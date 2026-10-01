<?php

namespace App\Http\Controllers\Select;

use App\Models\Customoid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @extends SelectController<Customoid>
 */
class CustomoidController extends SelectController
{
    /**
     * Defines the base query for this resource
     */
    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Customoid::class);

        return Customoid::hasAccess($request->user())
            ->with(['device' => function ($query): void {
                $query->select('device_id', 'hostname', 'sysName', 'display');
            }])
            ->select('customoid_id', 'customoid_descr', 'device_id');
    }

    /**
     * @param  Customoid  $model
     * @return array{id: int|string, text: string, icon?: string}
     */
    public function formatItem(Model $model): array
    {
        return [
            'id' => $model->customoid_id,
            'text' => $model->device->shortDisplayName() . ' (' . $model->customoid_descr . ')',
        ];
    }
}
