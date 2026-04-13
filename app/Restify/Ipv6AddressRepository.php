<?php

namespace App\Restify;

use App\Models\Ipv6Address;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class Ipv6AddressRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = Ipv6Address::class;

    public static string $id = 'ipv6_address_id';

    public static string $title = 'ipv6_compressed';

    public static array $search = [
        'ipv6_address',
        'ipv6_compressed',
    ];

    public static array $match = [
        'ipv6_address' => 'text',
        'ipv6_compressed' => 'text',
        'ipv6_prefixlen' => 'integer',
        'ipv6_origin' => 'text',
        'ipv6_network_id' => 'integer',
        'port_id' => 'integer',
        'context_name' => 'text',
    ];

    public static array $sort = [
        'ipv6_address',
        'ipv6_compressed',
        'ipv6_prefixlen',
        'ipv6_origin',
        'ipv6_network_id',
        'port_id',
        'context_name',
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
