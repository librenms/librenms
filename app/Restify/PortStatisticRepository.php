<?php

namespace App\Restify;

use App\Models\PortStatistic;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class PortStatisticRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = PortStatistic::class;

    public static string $id = 'port_id';

    public static string $title = 'port_id';

    public static array $match = [
        'port_id' => 'integer',
        'ifInNUcastPkts' => 'integer',
        'ifInNUcastPkts_delta' => 'integer',
        'ifInNUcastPkts_rate' => 'integer',
        'ifOutNUcastPkts' => 'integer',
        'ifOutNUcastPkts_delta' => 'integer',
        'ifOutNUcastPkts_rate' => 'integer',
        'ifInDiscards' => 'integer',
        'ifInDiscards_delta' => 'integer',
        'ifInDiscards_rate' => 'integer',
        'ifOutDiscards' => 'integer',
        'ifOutDiscards_delta' => 'integer',
        'ifOutDiscards_rate' => 'integer',
        'ifInUnknownProtos' => 'integer',
        'ifInUnknownProtos_delta' => 'integer',
        'ifInUnknownProtos_rate' => 'integer',
        'ifInBroadcastPkts' => 'integer',
        'ifInBroadcastPkts_delta' => 'integer',
        'ifInBroadcastPkts_rate' => 'integer',
        'ifOutBroadcastPkts' => 'integer',
        'ifOutBroadcastPkts_delta' => 'integer',
        'ifOutBroadcastPkts_rate' => 'integer',
        'ifInMulticastPkts' => 'integer',
        'ifInMulticastPkts_delta' => 'integer',
        'ifInMulticastPkts_rate' => 'integer',
        'ifOutMulticastPkts' => 'integer',
        'ifOutMulticastPkts_delta' => 'integer',
        'ifOutMulticastPkts_rate' => 'integer',
    ];

    public static array $sort = [
        'port_id',
        'ifInNUcastPkts',
        'ifInNUcastPkts_delta',
        'ifInNUcastPkts_rate',
        'ifOutNUcastPkts',
        'ifOutNUcastPkts_delta',
        'ifOutNUcastPkts_rate',
        'ifInDiscards',
        'ifInDiscards_delta',
        'ifInDiscards_rate',
        'ifOutDiscards',
        'ifOutDiscards_delta',
        'ifOutDiscards_rate',
        'ifInUnknownProtos',
        'ifInUnknownProtos_delta',
        'ifInUnknownProtos_rate',
        'ifInBroadcastPkts',
        'ifInBroadcastPkts_delta',
        'ifInBroadcastPkts_rate',
        'ifOutBroadcastPkts',
        'ifOutBroadcastPkts_delta',
        'ifOutBroadcastPkts_rate',
        'ifInMulticastPkts',
        'ifInMulticastPkts_delta',
        'ifInMulticastPkts_rate',
        'ifOutMulticastPkts',
        'ifOutMulticastPkts_delta',
        'ifOutMulticastPkts_rate',
    ];

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
