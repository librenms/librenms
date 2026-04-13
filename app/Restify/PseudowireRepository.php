<?php

namespace App\Restify;

use App\Models\Pseudowire;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class PseudowireRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = Pseudowire::class;

    public static string $id = 'pseudowire_id';

    public static string $title = 'pw_descr';

    public static array $search = [
        'pw_descr',
        'pw_type',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'port_id' => 'integer',
        'peer_device_id' => 'integer',
        'peer_ldp_id' => 'integer',
        'cpwVcID' => 'integer',
        'cpwOid' => 'integer',
        'pw_type' => 'text',
        'pw_psntype' => 'text',
        'pw_local_mtu' => 'integer',
        'pw_peer_mtu' => 'integer',
        'pw_descr' => 'text',
    ];

    public static array $sort = [
        'device_id',
        'port_id',
        'peer_device_id',
        'peer_ldp_id',
        'cpwVcID',
        'cpwOid',
        'pw_type',
        'pw_psntype',
        'pw_local_mtu',
        'pw_peer_mtu',
        'pw_descr',
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
