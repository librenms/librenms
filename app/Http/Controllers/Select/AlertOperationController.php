<?php

namespace App\Http\Controllers\Select;

use App\Models\AlertOperation;
use App\Models\AlertRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @extends SelectController<AlertOperation>
 */
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
        $this->authorize('viewAny', AlertRule::class);

        return AlertOperation::query()
            ->select(['id', 'name'])
            ->orderBy('name');
    }

    /**
     * @param  AlertOperation  $model
     * @return array{id: int|string, text: string, icon?: string}
     */
    public function formatItem(Model $model): array
    {
        return [
            'id' => $model->id,
            'text' => (string) $model->name,
        ];
    }
}
