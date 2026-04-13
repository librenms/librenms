<?php

namespace App\Restify;

use App\Models\Ipv4Network;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class Ipv4NetworkRepository extends Repository
{
    public static string $model = Ipv4Network::class;

    public static string $id = 'ipv4_network_id';

    public static string $title = 'ipv4_network';

    public static array $search = [
        'ipv4_network',
    ];

    public static array $match = [
        'ipv4_network' => 'text',
        'context_name' => 'text',
    ];

    public static array $sort = [
        'ipv4_network',
        'context_name',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('ipv4_network')->readonly(),
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
     * IPv4 networks are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * IPv4 networks are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
