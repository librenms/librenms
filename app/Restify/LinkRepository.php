<?php

namespace App\Restify;

use App\Models\Link;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LinkRepository extends Repository
{
    public static string $model = Link::class;

    public static string $title = 'remote_hostname';

    public static array $search = [
        'remote_hostname',
        'remote_port',
        'protocol',
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
            field('local_port_id')->readonly(),
            field('local_device_id')->readonly(),
            field('remote_port_id')->readonly(),
            field('active')->readonly(),
            field('protocol')->readonly(),
            field('remote_hostname')->readonly(),
            field('remote_device_id')->readonly(),
            field('remote_port')->readonly(),
            field('remote_platform')->readonly(),
            field('remote_version')->readonly(),
        ];
    }

    /**
     * Link extends plain Model (no hasAccess scope), so we filter by device access on local_device_id.
     * TODO: Discuss if this should be scoped by hasAccess on device/port instead of custom filtering.
     */
    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        if ($user = $request->user()) {
            if (Gate::allows('viewAny', Link::class)) {
                return $query;
            }

            return $query->whereIntegerInRaw('local_device_id', \Permissions::devicesForUser($user));
        }

        return $query->whereRaw('1 = 0');
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return static::indexQuery($request, $query);
    }

    /**
     * Links are discovered automatically by LibreNMS via CDP/LLDP — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Links are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
