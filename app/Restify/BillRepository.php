<?php

namespace App\Restify;

use App\Models\Bill;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;

class BillRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Bill::class;

    public static string $id = 'bill_id';

    public static string $title = 'bill_name';

    public static array $search = [
        'bill_name',
        'bill_ref',
        'bill_custid',
        'bill_notes',
    ];

    public static array $match = [
        'bill_name' => 'text',
        'bill_type' => 'text',
        'bill_day' => 'integer',
        'bill_custid' => 'text',
        'bill_ref' => 'text',
        'bill_notes' => 'text',
        'rate_95th_in' => 'integer',
        'rate_95th_out' => 'integer',
        'rate_95th' => 'integer',
        'dir_95th' => 'text',
        'total_data' => 'integer',
        'total_data_in' => 'integer',
        'total_data_out' => 'integer',
        'rate_average_in' => 'integer',
        'rate_average_out' => 'integer',
        'rate_average' => 'integer',
        'bill_last_calc' => 'datetime',
    ];

    public static array $sort = [
        'bill_name',
        'bill_type',
        'bill_day',
        'bill_custid',
        'bill_ref',
        'bill_notes',
        'rate_95th_in',
        'rate_95th_out',
        'rate_95th',
        'dir_95th',
        'total_data',
        'total_data_in',
        'total_data_out',
        'rate_average_in',
        'rate_average_out',
        'rate_average',
        'bill_last_calc',
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
}
