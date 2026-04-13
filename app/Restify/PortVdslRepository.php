<?php

namespace App\Restify;

use App\Models\PortVdsl;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class PortVdslRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = PortVdsl::class;

    public static string $id = 'port_id';

    public static string $title = 'port_id';

    public static array $match = [
        'port_id' => 'integer',
        'xdsl2LineStatusAttainableRateDs' => 'integer',
        'xdsl2LineStatusAttainableRateUs' => 'integer',
        'xdsl2ChStatusActDataRateXtur' => 'integer',
        'xdsl2ChStatusActDataRateXtuc' => 'integer',
        'xdsl2LineStatusActAtpDs' => 'integer',
        'xdsl2LineStatusActAtpUs' => 'integer',
    ];

    public static array $sort = [
        'port_id',
        'xdsl2LineStatusAttainableRateDs',
        'xdsl2LineStatusAttainableRateUs',
        'xdsl2ChStatusActDataRateXtur',
        'xdsl2ChStatusActDataRateXtuc',
        'xdsl2LineStatusActAtpDs',
        'xdsl2LineStatusActAtpUs',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('port_id')->readonly(),
            field('xdsl2LineStatusAttainableRateDs')->readonly(),
            field('xdsl2LineStatusAttainableRateUs')->readonly(),
            field('xdsl2ChStatusActDataRateXtur')->readonly(),
            field('xdsl2ChStatusActDataRateXtuc')->readonly(),
            field('xdsl2LineStatusActAtpDs')->readonly(),
            field('xdsl2LineStatusActAtpUs')->readonly(),
        ];
    }

    /**
     * VDSL port stats are discovered automatically by LibreNMS during the polling process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * VDSL port stats are managed by the LibreNMS polling process.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
