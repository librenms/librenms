<?php

namespace App\Restify;

use App\Models\Pseudowire;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class PseudowireRepository extends Repository
{
    public static string $model = Pseudowire::class;

    public static string $id = 'pseudowire_id';

    public static string $title = 'pw_descr';

    public static array $search = [
        'pw_descr',
        'pw_type',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('device_id')->readonly(),
            field('port_id')->readonly(),
            field('peer_device_id')->readonly(),
            field('peer_ldp_id')->readonly(),
            field('cpwVcID')->readonly(),
            field('cpwOid')->readonly(),
            field('pw_type')->readonly(),
            field('pw_psntype')->readonly(),
            field('pw_local_mtu')->readonly(),
            field('pw_peer_mtu')->readonly(),
            field('pw_descr')->readonly(),
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
     * Pseudowires are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Pseudowires are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
