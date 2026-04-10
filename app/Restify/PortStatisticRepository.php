<?php

namespace App\Restify;

use App\Models\PortStatistic;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class PortStatisticRepository extends Repository
{
    public static string $model = PortStatistic::class;

    public static string $id = 'port_id';

    public static string $title = 'port_id';

    public function fields(RestifyRequest $request): array
    {
        return [
            field('port_id')->readonly(),
            field('ifInNUcastPkts')->readonly(),
            field('ifInNUcastPkts_delta')->readonly(),
            field('ifInNUcastPkts_rate')->readonly(),
            field('ifOutNUcastPkts')->readonly(),
            field('ifOutNUcastPkts_delta')->readonly(),
            field('ifOutNUcastPkts_rate')->readonly(),
            field('ifInDiscards')->readonly(),
            field('ifInDiscards_delta')->readonly(),
            field('ifInDiscards_rate')->readonly(),
            field('ifOutDiscards')->readonly(),
            field('ifOutDiscards_delta')->readonly(),
            field('ifOutDiscards_rate')->readonly(),
            field('ifInUnknownProtos')->readonly(),
            field('ifInUnknownProtos_delta')->readonly(),
            field('ifInUnknownProtos_rate')->readonly(),
            field('ifInBroadcastPkts')->readonly(),
            field('ifInBroadcastPkts_delta')->readonly(),
            field('ifInBroadcastPkts_rate')->readonly(),
            field('ifOutBroadcastPkts')->readonly(),
            field('ifOutBroadcastPkts_delta')->readonly(),
            field('ifOutBroadcastPkts_rate')->readonly(),
            field('ifInMulticastPkts')->readonly(),
            field('ifInMulticastPkts_delta')->readonly(),
            field('ifInMulticastPkts_rate')->readonly(),
            field('ifOutMulticastPkts')->readonly(),
            field('ifOutMulticastPkts_delta')->readonly(),
            field('ifOutMulticastPkts_rate')->readonly(),
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
     * Port statistics are collected automatically by LibreNMS during the polling process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Port statistics are managed by the LibreNMS polling process.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
