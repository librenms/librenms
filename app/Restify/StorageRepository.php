<?php

namespace App\Restify;

use App\Models\Storage;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class StorageRepository extends Repository
{
    public static string $model = Storage::class;

    public static string $id = 'storage_id';

    public static string $title = 'storage_descr';

    public static array $search = [
        'storage_descr',
        'storage_type',
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
            field('storage_type')->readonly(),
            field('storage_descr')->readonly(),
            field('storage_size')->readonly(),
            field('storage_units')->readonly(),
            field('storage_used')->readonly(),
            field('storage_free')->readonly(),
            field('storage_perc')->readonly(),
            field('storage_perc_warn')->readonly(),
            field('storage_index')->readonly(),
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

    /**
     * Storage entries are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Storage entries are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
