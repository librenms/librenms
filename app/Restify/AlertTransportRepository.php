<?php

namespace App\Restify;

use App\Models\AlertTransport;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class AlertTransportRepository extends Repository
{
    public static string $model = AlertTransport::class;

    public static string $title = 'transport_name';

    public static array $search = [
        'transport_name',
    ];

    public static array $match = [
        'transport_name' => 'text',
        'transport_type' => 'text',
        'is_default' => 'bool',
    ];

    public static array $sort = [
        'transport_name',
        'transport_type',
        'is_default',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('transport_name')->rules('required', 'string'),
            field('transport_type')->rules('required', 'string'),
            field('is_default')->rules('boolean'),
            field('transport_config')->readonly(),
        ];
    }

    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return $query;
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return $query;
    }
}
