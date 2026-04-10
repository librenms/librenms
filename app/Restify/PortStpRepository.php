<?php

namespace App\Restify;

use App\Models\PortStp;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class PortStpRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = PortStp::class;

    public static string $id = 'port_stp_id';

    public static string $title = 'state';

    public static array $search = [
        'state',
        'designatedRoot',
        'designatedBridge',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('device_id')->readonly(),
            field('vlan')->readonly(),
            field('port_id')->readonly(),
            field('port_index')->readonly(),
            field('priority')->readonly(),
            field('state')->readonly(),
            field('enable')->readonly(),
            field('pathCost')->readonly(),
            field('designatedRoot')->readonly(),
            field('designatedCost')->readonly(),
            field('designatedBridge')->readonly(),
            field('designatedPort')->readonly(),
            field('forwardTransitions')->readonly(),
        ];
    }

    /**
     * Port STP entries are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Port STP entries are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
