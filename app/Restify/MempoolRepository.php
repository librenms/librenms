<?php

namespace App\Restify;

use App\Models\Mempool;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class MempoolRepository extends Repository
{
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
