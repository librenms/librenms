<?php

namespace LibreNMS\Tests\Mocks;

use App\Models\AlertTemplate;
use App\Restify\Repository;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;

/**
 * Minimal model repository used to test the v1 API base repository.
 */
class RestifyAlertTemplateRepository extends Repository
{
    public static string $model = AlertTemplate::class;

    public static string $uriKey = 'test-alert-templates';

    /**
     * @return array<int, \Binaryk\LaravelRestify\Fields\Field>
     */
    public function fields(RestifyRequest $request): array
    {
        return [
            field('name')->rules('required', 'string', 'max:255'),
            field('template')->rules('required', 'string'),
        ];
    }
}
