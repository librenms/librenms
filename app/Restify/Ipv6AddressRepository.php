<?php

namespace App\Restify;

use App\Models\Ipv6Address;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class Ipv6AddressRepository extends Repository
{
    public static string $model = Ipv6Address::class;

    public static string $id = 'ipv6_address_id';

    public static string $title = 'ipv6_compressed';

    public static array $search = [
        'ipv6_address',
        'ipv6_compressed',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('ipv6_address')->readonly(),
            field('ipv6_compressed')->readonly(),
            field('ipv6_prefixlen')->readonly(),
            field('ipv6_origin')->readonly(),
            field('ipv6_network_id')->readonly(),
            field('port_id')->readonly(),
            field('context_name')->readonly(),
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
     * IPv6 addresses are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * IPv6 addresses are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
