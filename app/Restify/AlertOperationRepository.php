<?php

namespace App\Restify;

use App\Http\Requests\AlertOperationRequest;
use App\Models\AlertOperation;
use App\Services\Alerting\AlertOperationSyncer;
use Binaryk\LaravelRestify\Fields\HasMany;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AlertOperationRepository extends Repository
{
    public static string $model = AlertOperation::class;

    public static string $title = 'name';

    public static function related(): array
    {
        return [
            'segments' => HasMany::make('segments', AlertOperationSegmentRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'name' => SearchableFilter::make()->setColumn('name'),
        ];
    }

    public static function matches(): array
    {
        return [
            'name' => MatchFilter::make()->setType('text')->setColumn('name'),
            'notificationsSuppressed' => MatchFilter::make()->setType('bool')->setColumn('notifications_suppressed'),
            'defaultOperationStepDurationSeconds' => MatchFilter::make()->setType('integer')->setColumn('default_operation_step_duration_seconds'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'name' => SortableFilter::make()->setColumn('name'),
            'notificationsSuppressed' => SortableFilter::make()->setColumn('notifications_suppressed'),
            'defaultOperationStepDurationSeconds' => SortableFilter::make()->setColumn('default_operation_step_duration_seconds'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('name')->rules('required', 'string', 'max:255'),
            field('defaultOperationStepDurationSeconds', fn ($value, $model) => $model->default_operation_step_duration_seconds)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->default_operation_step_duration_seconds = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'integer', 'min:0'),
            field('notificationsSuppressed', fn ($value, $model) => (bool) $model->notifications_suppressed)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->notifications_suppressed = $request->boolean($attribute);
                    }
                })
                ->rules('boolean'),
        ];
    }

    public function store(RestifyRequest $request)
    {
        $segments = $this->validatedSegments($request);

        $response = parent::store($request);

        try {
            /** @var AlertOperation $operation */
            $operation = $this->resource;
            AlertOperationSyncer::sync($operation, $segments);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages(['segments' => $e->getMessage()]);
        }

        return $response;
    }

    public function update(RestifyRequest $request, $repositoryId)
    {
        $segments = $this->validatedSegments($request);

        $response = parent::update($request, $repositoryId);

        try {
            /** @var AlertOperation $operation */
            $operation = $this->resource;
            AlertOperationSyncer::sync($operation, $segments);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages(['segments' => $e->getMessage()]);
        }

        return $response;
    }

    public function destroy(RestifyRequest $request, $repositoryId)
    {
        /** @var AlertOperation $operation */
        $operation = $this->resource;
        if ($operation->alertRules()->exists()) {
            throw ValidationException::withMessages([
                'id' => 'This operation is assigned to one or more alert rules and cannot be deleted.',
            ]);
        }

        return parent::destroy($request, $repositoryId);
    }

    /**
     * Run the segment portion of {@see AlertOperationRequest} rules and return the rows.
     *
     * @return array<int, array<string, mixed>>
     */
    private function validatedSegments(RestifyRequest $request): array
    {
        $rules = (new AlertOperationRequest())->rules();
        $segmentRules = array_filter(
            $rules,
            fn (string $key) => $key === 'segments' || str_starts_with($key, 'segments.'),
            ARRAY_FILTER_USE_KEY,
        );

        $payload = ['segments' => $request->input('segments', [])];
        if (is_array($payload['segments'])) {
            foreach ($payload['segments'] as $i => $seg) {
                if (is_array($seg) && ($seg['escalation_step_to'] ?? null) === '') {
                    $payload['segments'][$i]['escalation_step_to'] = null;
                }
            }
        }

        Validator::make($payload, $segmentRules)->validate();

        return $payload['segments'];
    }
}
