<?php

namespace App\Restify;

use App\Models\Service;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class ServiceRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Service::class;

    public static string $id = 'service_id';

    public static string $title = 'service_name';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'name' => SearchableFilter::make()->setColumn('service_name'),
        ];
    }

    public static function matches(): array
    {
        return [
            'category' => MatchFilter::make()->setType('text')->setColumn('service_type'),
            'name' => MatchFilter::make()->setType('text')->setColumn('service_name'),
            'description' => MatchFilter::make()->setType('text')->setColumn('service_desc'),
            'parameter' => MatchFilter::make()->setType('text')->setColumn('service_param'),
            'ip' => MatchFilter::make()->setType('text')->setColumn('service_ip'),
            'status' => MatchFilter::make()->setType('integer')->setColumn('service_status'),
            'message' => MatchFilter::make()->setType('text')->setColumn('service_message'),
            'updatedAt' => MatchFilter::make()->setType('datetime')->setColumn('service_changed'),
            'isIgnored' => MatchFilter::make()->setType('bool')->setColumn('service_ignore'),
            'isEnabled' => MatchFilter::make()->setType('bool')->setColumn('service_disabled'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'category' => SortableFilter::make()->setColumn('service_type'),
            'name' => SortableFilter::make()->setColumn('service_name'),
            'description' => SortableFilter::make()->setColumn('service_desc'),
            'parameter' => SortableFilter::make()->setColumn('service_param'),
            'ip' => SortableFilter::make()->setColumn('service_ip'),
            'status' => SortableFilter::make()->setColumn('service_status'),
            'message' => SortableFilter::make()->setColumn('service_message'),
            'updatedAt' => SortableFilter::make()->setColumn('service_changed'),
            'isIgnored' => SortableFilter::make()->setColumn('service_ignore'),
            'isEnabled' => SortableFilter::make()->setColumn('service_disabled'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('device_id')->rules('required', 'integer'),
            field('serviceTemplateId', fn ($value, $model) => $model->service_template_id)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->service_template_id = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'integer'),
            field('category', fn ($value, $model) => $model->service_type)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->service_type = $request->input($attribute);
                    }
                })
                ->rules('required', 'string'),
            field('name', fn ($value, $model) => $model->service_name)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->service_name = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'string'),
            field('description', fn ($value, $model) => $model->service_desc)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->service_desc = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'string'),
            field('parameter', fn ($value, $model) => $model->service_param)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->service_param = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'string'),
            field('ip', fn ($value, $model) => $model->service_ip)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->service_ip = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'string'),
            field('status', fn ($value, $model) => $model->service_status)->readonly(),
            field('message', fn ($value, $model) => $model->service_message)->readonly(),
            field('updatedAt', fn ($value, $model) => $model->service_changed)->readonly(),
            field('isIgnored', fn ($value, $model) => $model->service_ignore)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->service_ignore = $request->input($attribute);
                    }
                })
                ->rules('boolean'),
            field('isEnabled', fn ($value, $model) => ! $model->service_disabled)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->service_disabled = ! $request->boolean($attribute);
                    }
                })
                ->rules('boolean'),
        ];
    }
}
