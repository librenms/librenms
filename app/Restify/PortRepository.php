<?php

namespace App\Restify;

use App\Models\Port;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;

class PortRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = Port::class;

    public static string $id = 'port_id';

    public static string $title = 'ifName';

    public static array $search = [
        'ifName',
        'ifAlias',
        'ifDescr',
    ];

    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
            'groups' => BelongsToMany::make('groups', PortGroupRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('device_id')->readonly(),
            field('ifIndex')->readonly(),
            field('ifName')->readonly(),
            field('ifAlias')->readonly(),
            field('ifDescr')->readonly(),
            field('ifType')->readonly(),
            field('ifSpeed')->readonly(),
            field('ifHighSpeed')->readonly(),
            field('ifOperStatus')->readonly(),
            field('ifAdminStatus')->readonly(),
            field('ifMtu')->readonly(),
            field('ifPhysAddress')->readonly(),
            field('ifInOctets')->readonly(),
            field('ifOutOctets')->readonly(),
            field('ifInErrors')->readonly(),
            field('ifOutErrors')->readonly(),
        ];
    }
}
