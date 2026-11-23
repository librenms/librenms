<?php

namespace App\Http\Controllers\Select;

use App\Models\AlertLog;
use App\Models\AlertRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * @extends SelectController<AlertRule>
 */
class AlertLogRuleController extends SelectController
{
    protected ?string $idField = 'id';
    protected ?string $textField = 'name';

    /** @return array{device: string} */
    protected function rules(): array
    {
        return ['device' => 'sometimes|integer|min:1'];
    }

    /**
     * @return list<string>
     */
    protected function searchFields(Request $request): array
    {
        return ['name'];
    }

    /**
     * @return Builder<AlertRule>
     */
    public function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', AlertLog::class);

        return AlertRule::query()
            ->whereHas('logs', fn (Builder $logs) => $logs->hasAccess($request->user())
                ->when($request->integer('device'), fn ($query, $deviceId) => $query->where('device_id', $deviceId)))
            ->orderBy('name');
    }
}
