<?php

namespace App\Http\Controllers\Select;

use App\Models\AlertOperation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AlertOperationController extends SelectController
{
    /**
     * @return list<string>
     */
    protected function searchFields(Request $request): array
    {
        return ['name'];
    }

    /**
     * @return Builder<AlertOperation>
     */
    public function baseQuery(Request $request): Builder
    {
        return AlertOperation::query()
            ->select(['id', 'name'])
            ->orderBy('name');
    }

    /**
     * @param  AlertOperation  $model
     * @return array{id: int, text: string}
     */
    public function formatItem($model): array
    {
        return [
            'id' => $model->id,
            'text' => (string) $model->name,
        ];
    }
}
