<?php

namespace App\Restify;

use App\Models\Ipv4Mac;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class Ipv4MacRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = Ipv4Mac::class;

    public static string $title = 'ipv4_address';

    public static array $search = [
        'ipv4_address',
        'mac_address',
    ];

    public static array $match = [
        'port_id' => 'integer',
        'device_id' => 'integer',
        'mac_address' => 'text',
        'ipv4_address' => 'text',
        'context_name' => 'text',
    ];

    public static array $sort = [
        'port_id',
        'device_id',
        'mac_address',
        'ipv4_address',
        'context_name',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('port_id')->readonly(),
            field('device_id')->readonly(),
            field('mac_address')->readonly(),
            field('ipv4_address')->readonly(),
            field('context_name')->readonly(),
        ];
    }

    /**
     * ARP entries are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * ARP entries are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
