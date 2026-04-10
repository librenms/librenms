<?php

namespace App\Restify;

use App\Models\PortVdsl;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class PortVdslRepository extends Repository
{
    public static string $model = PortVdsl::class;

    public static string $id = 'port_id';

    public static string $title = 'port_id';

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

    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        if ($user = $request->user()) {
            return $query->hasAccess($user);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return static::indexQuery($request, $query);
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
