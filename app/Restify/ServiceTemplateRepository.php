<?php

namespace App\Restify;

use App\Models\ServiceTemplate;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class ServiceTemplateRepository extends Repository
{
    public static string $model = ServiceTemplate::class;

    public static string $title = 'name';

    public static array $search = [
        'name',
        'desc',
    ];

    public static function related(): array
    {
        return [
            'devices' => BelongsToMany::make('devices', DeviceRepository::class),
            'groups' => BelongsToMany::make('groups', DeviceGroupRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('name')->rules('required', 'string'),
            field('check')->rules('required', 'string'),
            field('type')->rules('required', 'string', 'in:static,dynamic'),
            field('desc')->rules('nullable', 'string'),
            field('param')->rules('nullable', 'string'),
            field('ip')->rules('nullable', 'string'),
            field('disabled')->rules('boolean'),
            field('rules')->readonly(),
        ];
    }

    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        if ($user = $request->user()) {
            return $query->hasAccess($user);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return static::indexQuery($request, $query);
    }
}
