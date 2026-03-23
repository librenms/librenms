<?php

namespace App\Http\Controllers\Select;

use App\Models\AlertOperation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AlertOperationController extends SelectController
{
    protected function searchFields(Request $request)
    {
        return ['name'];
    }

    public function baseQuery(Request $request): Builder
    {
        return AlertOperation::query()
            ->select(['id', 'name'])
            ->orderBy('name');
    }

    /** @param  AlertOperation  $model */
    public function formatItem($model): array
    {
        return [
            'id' => $model->id,
            'text' => (string) $model->name,
        ];
    }
}
