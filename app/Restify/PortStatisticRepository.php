<?php

namespace App\Restify;

use App\Models\PortStatistic;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class PortStatisticRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = PortStatistic::class;

    public static string $id = 'port_id';

    public static string $title = 'port_id';



    public static function searchables(): array
    {
        return [];
    }

    public static function matches(): array
    {
        return [
            'inNonUnicastPackets' => MatchFilter::make()->setType('integer')->setColumn('ifInNUcastPkts'),
            'inNonUnicastPacketsDelta' => MatchFilter::make()->setType('integer')->setColumn('ifInNUcastPkts_delta'),
            'inNonUnicastPacketsRate' => MatchFilter::make()->setType('integer')->setColumn('ifInNUcastPkts_rate'),
            'outNonUnicastPackets' => MatchFilter::make()->setType('integer')->setColumn('ifOutNUcastPkts'),
            'outNonUnicastPacketsDelta' => MatchFilter::make()->setType('integer')->setColumn('ifOutNUcastPkts_delta'),
            'outNonUnicastPacketsRate' => MatchFilter::make()->setType('integer')->setColumn('ifOutNUcastPkts_rate'),
            'inDiscards' => MatchFilter::make()->setType('integer')->setColumn('ifInDiscards'),
            'inDiscardsDelta' => MatchFilter::make()->setType('integer')->setColumn('ifInDiscards_delta'),
            'inDiscardsRate' => MatchFilter::make()->setType('integer')->setColumn('ifInDiscards_rate'),
            'outDiscards' => MatchFilter::make()->setType('integer')->setColumn('ifOutDiscards'),
            'outDiscardsDelta' => MatchFilter::make()->setType('integer')->setColumn('ifOutDiscards_delta'),
            'outDiscardsRate' => MatchFilter::make()->setType('integer')->setColumn('ifOutDiscards_rate'),
            'inUnknownProtocols' => MatchFilter::make()->setType('integer')->setColumn('ifInUnknownProtos'),
            'inUnknownProtocolsDelta' => MatchFilter::make()->setType('integer')->setColumn('ifInUnknownProtos_delta'),
            'inUnknownProtocolsRate' => MatchFilter::make()->setType('integer')->setColumn('ifInUnknownProtos_rate'),
            'inBroadcastPackets' => MatchFilter::make()->setType('integer')->setColumn('ifInBroadcastPkts'),
            'inBroadcastPacketsDelta' => MatchFilter::make()->setType('integer')->setColumn('ifInBroadcastPkts_delta'),
            'inBroadcastPacketsRate' => MatchFilter::make()->setType('integer')->setColumn('ifInBroadcastPkts_rate'),
            'outBroadcastPackets' => MatchFilter::make()->setType('integer')->setColumn('ifOutBroadcastPkts'),
            'outBroadcastPacketsDelta' => MatchFilter::make()->setType('integer')->setColumn('ifOutBroadcastPkts_delta'),
            'outBroadcastPacketsRate' => MatchFilter::make()->setType('integer')->setColumn('ifOutBroadcastPkts_rate'),
            'inMulticastPackets' => MatchFilter::make()->setType('integer')->setColumn('ifInMulticastPkts'),
            'inMulticastPacketsDelta' => MatchFilter::make()->setType('integer')->setColumn('ifInMulticastPkts_delta'),
            'inMulticastPacketsRate' => MatchFilter::make()->setType('integer')->setColumn('ifInMulticastPkts_rate'),
            'outMulticastPackets' => MatchFilter::make()->setType('integer')->setColumn('ifOutMulticastPkts'),
            'outMulticastPacketsDelta' => MatchFilter::make()->setType('integer')->setColumn('ifOutMulticastPkts_delta'),
            'outMulticastPacketsRate' => MatchFilter::make()->setType('integer')->setColumn('ifOutMulticastPkts_rate'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'inNonUnicastPackets' => SortableFilter::make()->setColumn('ifInNUcastPkts'),
            'inNonUnicastPacketsDelta' => SortableFilter::make()->setColumn('ifInNUcastPkts_delta'),
            'inNonUnicastPacketsRate' => SortableFilter::make()->setColumn('ifInNUcastPkts_rate'),
            'outNonUnicastPackets' => SortableFilter::make()->setColumn('ifOutNUcastPkts'),
            'outNonUnicastPacketsDelta' => SortableFilter::make()->setColumn('ifOutNUcastPkts_delta'),
            'outNonUnicastPacketsRate' => SortableFilter::make()->setColumn('ifOutNUcastPkts_rate'),
            'inDiscards' => SortableFilter::make()->setColumn('ifInDiscards'),
            'inDiscardsDelta' => SortableFilter::make()->setColumn('ifInDiscards_delta'),
            'inDiscardsRate' => SortableFilter::make()->setColumn('ifInDiscards_rate'),
            'outDiscards' => SortableFilter::make()->setColumn('ifOutDiscards'),
            'outDiscardsDelta' => SortableFilter::make()->setColumn('ifOutDiscards_delta'),
            'outDiscardsRate' => SortableFilter::make()->setColumn('ifOutDiscards_rate'),
            'inUnknownProtocols' => SortableFilter::make()->setColumn('ifInUnknownProtos'),
            'inUnknownProtocolsDelta' => SortableFilter::make()->setColumn('ifInUnknownProtos_delta'),
            'inUnknownProtocolsRate' => SortableFilter::make()->setColumn('ifInUnknownProtos_rate'),
            'inBroadcastPackets' => SortableFilter::make()->setColumn('ifInBroadcastPkts'),
            'inBroadcastPacketsDelta' => SortableFilter::make()->setColumn('ifInBroadcastPkts_delta'),
            'inBroadcastPacketsRate' => SortableFilter::make()->setColumn('ifInBroadcastPkts_rate'),
            'outBroadcastPackets' => SortableFilter::make()->setColumn('ifOutBroadcastPkts'),
            'outBroadcastPacketsDelta' => SortableFilter::make()->setColumn('ifOutBroadcastPkts_delta'),
            'outBroadcastPacketsRate' => SortableFilter::make()->setColumn('ifOutBroadcastPkts_rate'),
            'inMulticastPackets' => SortableFilter::make()->setColumn('ifInMulticastPkts'),
            'inMulticastPacketsDelta' => SortableFilter::make()->setColumn('ifInMulticastPkts_delta'),
            'inMulticastPacketsRate' => SortableFilter::make()->setColumn('ifInMulticastPkts_rate'),
            'outMulticastPackets' => SortableFilter::make()->setColumn('ifOutMulticastPkts'),
            'outMulticastPacketsDelta' => SortableFilter::make()->setColumn('ifOutMulticastPkts_delta'),
            'outMulticastPacketsRate' => SortableFilter::make()->setColumn('ifOutMulticastPkts_rate'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('inNonUnicastPackets', fn ($value, $model) => $model->ifInNUcastPkts)->readonly(),
            field('inNonUnicastPacketsDelta', fn ($value, $model) => $model->ifInNUcastPkts_delta)->readonly(),
            field('inNonUnicastPacketsRate', fn ($value, $model) => $model->ifInNUcastPkts_rate)->readonly(),
            field('outNonUnicastPackets', fn ($value, $model) => $model->ifOutNUcastPkts)->readonly(),
            field('outNonUnicastPacketsDelta', fn ($value, $model) => $model->ifOutNUcastPkts_delta)->readonly(),
            field('outNonUnicastPacketsRate', fn ($value, $model) => $model->ifOutNUcastPkts_rate)->readonly(),
            field('inDiscards', fn ($value, $model) => $model->ifInDiscards)->readonly(),
            field('inDiscardsDelta', fn ($value, $model) => $model->ifInDiscards_delta)->readonly(),
            field('inDiscardsRate', fn ($value, $model) => $model->ifInDiscards_rate)->readonly(),
            field('outDiscards', fn ($value, $model) => $model->ifOutDiscards)->readonly(),
            field('outDiscardsDelta', fn ($value, $model) => $model->ifOutDiscards_delta)->readonly(),
            field('outDiscardsRate', fn ($value, $model) => $model->ifOutDiscards_rate)->readonly(),
            field('inUnknownProtocols', fn ($value, $model) => $model->ifInUnknownProtos)->readonly(),
            field('inUnknownProtocolsDelta', fn ($value, $model) => $model->ifInUnknownProtos_delta)->readonly(),
            field('inUnknownProtocolsRate', fn ($value, $model) => $model->ifInUnknownProtos_rate)->readonly(),
            field('inBroadcastPackets', fn ($value, $model) => $model->ifInBroadcastPkts)->readonly(),
            field('inBroadcastPacketsDelta', fn ($value, $model) => $model->ifInBroadcastPkts_delta)->readonly(),
            field('inBroadcastPacketsRate', fn ($value, $model) => $model->ifInBroadcastPkts_rate)->readonly(),
            field('outBroadcastPackets', fn ($value, $model) => $model->ifOutBroadcastPkts)->readonly(),
            field('outBroadcastPacketsDelta', fn ($value, $model) => $model->ifOutBroadcastPkts_delta)->readonly(),
            field('outBroadcastPacketsRate', fn ($value, $model) => $model->ifOutBroadcastPkts_rate)->readonly(),
            field('inMulticastPackets', fn ($value, $model) => $model->ifInMulticastPkts)->readonly(),
            field('inMulticastPacketsDelta', fn ($value, $model) => $model->ifInMulticastPkts_delta)->readonly(),
            field('inMulticastPacketsRate', fn ($value, $model) => $model->ifInMulticastPkts_rate)->readonly(),
            field('outMulticastPackets', fn ($value, $model) => $model->ifOutMulticastPkts)->readonly(),
            field('outMulticastPacketsDelta', fn ($value, $model) => $model->ifOutMulticastPkts_delta)->readonly(),
            field('outMulticastPacketsRate', fn ($value, $model) => $model->ifOutMulticastPkts_rate)->readonly(),
        ];
    }

    /**
     * Port statistics are collected automatically by LibreNMS during the polling process not created manually.
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
