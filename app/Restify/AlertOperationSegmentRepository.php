<?php

namespace App\Restify;

use App\Models\AlertOperationSegment;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class AlertOperationSegmentRepository extends Repository
{
    public static string $model = AlertOperationSegment::class;

    public static string $title = 'id';

    public static function related(): array
    {
        return [
            'alertOperation' => BelongsTo::make('alertOperation', AlertOperationRepository::class),
        ];
    }

    public static function matches(): array
    {
        return [
            'alertOperationId' => MatchFilter::make()->setType('integer')->setColumn('alert_operation_id'),
            'operationPhase' => MatchFilter::make()->setType('text')->setColumn('operation_phase'),
            'escalationStepFrom' => MatchFilter::make()->setType('integer')->setColumn('escalation_step_from'),
            'escalationStepTo' => MatchFilter::make()->setType('integer')->setColumn('escalation_step_to'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'position' => SortableFilter::make()->setColumn('position'),
            'alertOperationId' => SortableFilter::make()->setColumn('alert_operation_id'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('alertOperationId', fn ($value, $model) => $model->alert_operation_id)->readonly(),
            field('position')->readonly(),
            field('operationPhase', fn ($value, $model) => $model->operation_phase)->readonly(),
            field('escalationStepFrom', fn ($value, $model) => $model->escalation_step_from)->readonly(),
            field('escalationStepTo', fn ($value, $model) => $model->escalation_step_to)->readonly(),
            field('startInSeconds', fn ($value, $model) => $model->start_in_seconds)->readonly(),
            field('stepDurationSeconds', fn ($value, $model) => $model->step_duration_seconds)->readonly(),
            field('transports', function ($value, $model) {
                if ($model instanceof AlertOperationSegment) {
                    return $model->toApiArray()['transports'];
                }

                return [];
            })->readonly(),
        ];
    }

    /**
     * Segments are created/updated as part of their parent AlertOperation.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
