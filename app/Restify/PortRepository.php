<?php

namespace App\Restify;

use App\Models\Port;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class PortRepository extends Repository
{
    public static string $model = Port::class;

    public static string $id = "port_id";

    public static string $title = "ifName";

    public static array $search = [
        "ifName",
        "ifAlias",
        "ifDescr",
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field("device_id")->readonly(),
            field("ifIndex")->readonly(),
            field("ifName")->readonly(),
            field("ifAlias")->readonly(),
            field("ifDescr")->readonly(),
            field("ifType")->readonly(),
            field("ifSpeed")->readonly(),
            field("ifHighSpeed")->readonly(),
            field("ifOperStatus")->readonly(),
            field("ifAdminStatus")->readonly(),
            field("ifMtu")->readonly(),
            field("ifPhysAddress")->readonly(),
            field("ifInOctets")->readonly(),
            field("ifOutOctets")->readonly(),
            field("ifInErrors")->readonly(),
            field("ifOutErrors")->readonly(),
        ];
    }

    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        if ($user = $request->user()) {
            return $query->hasAccess($user);
        }

        return $query->whereRaw("1 = 0");
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return static::indexQuery($request, $query);
    }
}
