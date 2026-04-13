<?php

namespace App\Restify;

use App\Models\PortsNac;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class PortsNacRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = PortsNac::class;

    public static string $id = 'ports_nac_id';

    public static string $title = 'username';

    public static array $search = [
        'username',
        'mac_address',
        'ip_address',
    ];

    public static array $match = [
        'auth_id' => 'text',
        'device_id' => 'integer',
        'port_id' => 'integer',
        'domain' => 'text',
        'username' => 'text',
        'mac_address' => 'text',
        'ip_address' => 'text',
        'vlan' => 'integer',
        'host_mode' => 'text',
        'authz_status' => 'text',
        'authz_by' => 'text',
        'authc_status' => 'text',
        'method' => 'text',
        'timeout' => 'text',
        'time_left' => 'text',
        'time_elapsed' => 'text',
    ];

    public static array $sort = [
        'auth_id',
        'device_id',
        'port_id',
        'domain',
        'username',
        'mac_address',
        'ip_address',
        'vlan',
        'host_mode',
        'authz_status',
        'authz_by',
        'authc_status',
        'method',
        'timeout',
        'time_left',
        'time_elapsed',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('auth_id')->readonly(),
            field('device_id')->readonly(),
            field('port_id')->readonly(),
            field('domain')->readonly(),
            field('username')->readonly(),
            field('mac_address')->readonly(),
            field('ip_address')->readonly(),
            field('vlan')->readonly(),
            field('host_mode')->readonly(),
            field('authz_status')->readonly(),
            field('authz_by')->readonly(),
            field('authc_status')->readonly(),
            field('method')->readonly(),
            field('timeout')->readonly(),
            field('time_left')->readonly(),
            field('time_elapsed')->readonly(),
        ];
    }

    /**
     * NAC entries are discovered automatically by LibreNMS during the polling process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * NAC entries are managed by the LibreNMS polling process.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
