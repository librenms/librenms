<?php

namespace App\Restify;

use App\Models\Processor;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class ProcessorRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Processor::class;

    public static string $id = 'processor_id';

    public static string $title = 'processor_descr';

    public static array $search = [
        'processor_descr',
        'processor_type',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'processor_type' => 'text',
        'processor_descr' => 'text',
        'processor_usage' => 'integer',
        'processor_oid' => 'text',
        'processor_index' => 'text',
        'processor_precision' => 'integer',
        'processor_perc_warn' => 'integer',
    ];

    public static array $sort = [
        'device_id',
        'processor_type',
        'processor_descr',
        'processor_usage',
        'processor_oid',
        'processor_index',
        'processor_precision',
        'processor_perc_warn',
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

    /**
     * Processors are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Processors are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
