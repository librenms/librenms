<?php

namespace App\Restify;

use App\Models\Mempool;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class MempoolRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Mempool::class;

    public static string $id = 'mempool_id';

    public static string $title = 'mempool_descr';

    public static array $search = [
        'mempool_descr',
        'mempool_type',
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
            field('mempool_type')->readonly(),
            field('mempool_class')->readonly(),
            field('mempool_descr')->readonly(),
            field('mempool_perc')->readonly(),
            field('mempool_used')->readonly(),
            field('mempool_free')->readonly(),
            field('mempool_total')->readonly(),
            field('mempool_perc_warn')->readonly(),
            field('mempool_index')->readonly(),
        ];
    }

    /**
     * Memory pools are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Memory pools are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
