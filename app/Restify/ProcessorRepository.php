<?php

namespace App\Restify;

use App\Models\Processor;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class ProcessorRepository extends Repository
{
    public static string $model = Processor::class;

    public static string $id = 'processor_id';

    public static string $title = 'processor_descr';

    public static array $search = [
        'processor_descr',
        'processor_type',
    ];

    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('device_id')->readonly(),
            field('processor_type')->readonly(),
            field('processor_descr')->readonly(),
            field('processor_usage')->readonly(),
            field('processor_oid')->readonly(),
            field('processor_index')->readonly(),
            field('processor_precision')->readonly(),
            field('processor_perc_warn')->readonly(),
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

    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
