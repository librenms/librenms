<?php

namespace App\Restify;

use App\Models\Device;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class DeviceRepository extends Repository
{
    public static string $model = Device::class;

    public static string $id = "device_id";

    public static string $title = "hostname";

    public static array $search = [
        "hostname",
        "sysName",
        "os",
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field("hostname")->readonly(),
            field("sysName")->readonly(),
            field("sysDescr")->readonly(),
            field("sysObjectID")->readonly(),
            field("os")->readonly(),
            field("status")->readonly(),
            field("status_reason")->readonly(),
            field("hardware")->readonly(),
            field("serial")->readonly(),
            field("version")->readonly(),
            field("features")->readonly(),
            field("type")->readonly(),
            field("ip")->readonly(),
            field("display")->readonly(),
            field("disabled")->readonly(),
            field("uptime")->readonly(),
            field("location_id")->readonly(),
            field("purpose")->readonly(),
            field("notes")->readonly(),
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
