<?php

namespace App\Restify;

use App\Models\MplsSap;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class MplsSapRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = MplsSap::class;

    public static string $id = 'sap_id';

    public static string $title = 'sapDescription';

    public static array $search = [
        'sapDescription',
        'ifName',
    ];

    public static array $match = [
        'svc_id' => 'integer',
        'svc_oid' => 'integer',
        'sapPortId' => 'integer',
        'ifName' => 'text',
        'sapEncapValue' => 'text',
        'device_id' => 'integer',
        'sapRowStatus' => 'text',
        'sapType' => 'text',
        'sapDescription' => 'text',
        'sapAdminStatus' => 'text',
        'sapOperStatus' => 'text',
        'sapLastMgmtChange' => 'integer',
        'sapLastStatusChange' => 'integer',
    ];

    public static array $sort = [
        'svc_id',
        'svc_oid',
        'sapPortId',
        'ifName',
        'sapEncapValue',
        'device_id',
        'sapRowStatus',
        'sapType',
        'sapDescription',
        'sapAdminStatus',
        'sapOperStatus',
        'sapLastMgmtChange',
        'sapLastStatusChange',
    ];

    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
            'service' => BelongsTo::make('service', MplsServiceRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('svc_id')->readonly(),
            field('svc_oid')->readonly(),
            field('sapPortId')->readonly(),
            field('ifName')->readonly(),
            field('sapEncapValue')->readonly(),
            field('device_id')->readonly(),
            field('sapRowStatus')->readonly(),
            field('sapType')->readonly(),
            field('sapDescription')->readonly(),
            field('sapAdminStatus')->readonly(),
            field('sapOperStatus')->readonly(),
            field('sapLastMgmtChange')->readonly(),
            field('sapLastStatusChange')->readonly(),
        ];
    }

    /**
     * MPLS SAPs are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * MPLS SAPs are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
