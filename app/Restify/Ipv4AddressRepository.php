<?php

namespace App\Restify;

use App\Models\Ipv4Address;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class Ipv4AddressRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = Ipv4Address::class;

    public static string $id = 'ipv4_address_id';

    public static string $title = 'ipv4_address';

    public static array $search = [
        'ipv4_address',
    ];

    public static array $match = [
        'ipv4_address' => 'text',
        'ipv4_prefixlen' => 'integer',
        'ipv4_network_id' => 'integer',
        'port_id' => 'integer',
        'context_name' => 'text',
    ];

    public static array $sort = [
        'ipv4_address',
        'ipv4_prefixlen',
        'ipv4_network_id',
        'port_id',
        'context_name',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('ipv4_address')->readonly(),
            field('ipv4_prefixlen')->readonly(),
            field('ipv4_network_id')->readonly(),
            field('port_id')->readonly(),
            field('context_name')->readonly(),
        ];
    }

    /**
     * IPv4 addresses are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * IPv4 addresses are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
