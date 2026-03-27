<?php

namespace App\Restify;

use App\Models\Bill;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class BillRepository extends Repository
{
    public static string $model = Bill::class;

    public static string $id = 'bill_id';

    public static string $title = 'bill_name';

    public static array $search = [
        'bill_name',
        'bill_ref',
        'bill_custid',
        'bill_notes',
    ];

    public static function related(): array
    {
        return [
            'ports' => BelongsToMany::make('ports', PortRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('bill_name')->rules('required', 'string'),
            field('bill_type')->rules('nullable', 'string'),
            field('bill_day')->rules('nullable', 'integer'),
            field('bill_custid')->rules('nullable', 'string'),
            field('bill_ref')->rules('nullable', 'string'),
            field('bill_notes')->rules('nullable', 'string'),
            field('rate_95th_in')->readonly(),
            field('rate_95th_out')->readonly(),
            field('rate_95th')->readonly(),
            field('dir_95th')->readonly(),
            field('total_data')->readonly(),
            field('total_data_in')->readonly(),
            field('total_data_out')->readonly(),
            field('rate_average_in')->readonly(),
            field('rate_average_out')->readonly(),
            field('rate_average')->readonly(),
            field('bill_last_calc')->readonly(),
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
