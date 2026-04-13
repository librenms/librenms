<?php

namespace App\Restify;

use App\Models\Ipv6Network;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class Ipv6NetworkRepository extends Repository
{
    public static string $model = Ipv6Network::class;

    public static string $id = 'ipv6_network_id';

    public static string $title = 'ipv6_network';

    public static array $search = [
        'ipv6_network',
    ];

    public static array $match = [
        'ipv6_network' => 'text',
        'context_name' => 'text',
    ];

    public static array $sort = [
        'ipv6_network',
        'context_name',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('ipv6_network')->readonly(),
            field('context_name')->readonly(),
        ];
    }

    // TODO: Discuss if this should be a global resource or scoped by access to device/port
    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return $query;
    }

    // TODO: Discuss if this should be a global resource or scoped by access to device/port
    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return $query;
    }

    /**
     * IPv6 networks are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * IPv6 networks are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
