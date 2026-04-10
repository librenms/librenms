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
