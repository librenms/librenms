<?php

namespace App\Restify;

use App\Models\PortAdsl;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class PortAdslRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = PortAdsl::class;

    public static string $id = 'port_id';

    public static string $title = 'adslLineCoding';



    public static function searchables(): array
    {
        return [];
    }

    public static function matches(): array
    {
        return [
            'lineCoding' => MatchFilter::make()->setType('text')->setColumn('adslLineCoding'),
            'lineCategory' => MatchFilter::make()->setType('text')->setColumn('adslLineType'),
            'atucVendorId' => MatchFilter::make()->setType('text')->setColumn('adslAtucInvVendorID'),
            'atucVersionNumber' => MatchFilter::make()->setType('text')->setColumn('adslAtucInvVersionNumber'),
            'atucCurrentSnrMargin' => MatchFilter::make()->setType('integer')->setColumn('adslAtucCurrSnrMgn'),
            'atucCurrentAttenuation' => MatchFilter::make()->setType('integer')->setColumn('adslAtucCurrAtn'),
            'atucCurrentOutputPower' => MatchFilter::make()->setType('integer')->setColumn('adslAtucCurrOutputPwr'),
            'atucCurrentAttainableRate' => MatchFilter::make()->setType('integer')->setColumn('adslAtucCurrAttainableRate'),
            'atucChannelCurrentTransmitRate' => MatchFilter::make()->setType('integer')->setColumn('adslAtucChanCurrTxRate'),
            'aturSerialNumber' => MatchFilter::make()->setType('text')->setColumn('adslAturInvSerialNumber'),
            'aturVendorId' => MatchFilter::make()->setType('text')->setColumn('adslAturInvVendorID'),
            'aturVersionNumber' => MatchFilter::make()->setType('text')->setColumn('adslAturInvVersionNumber'),
            'aturChannelCurrentTransmitRate' => MatchFilter::make()->setType('integer')->setColumn('adslAturChanCurrTxRate'),
            'aturCurrentSnrMargin' => MatchFilter::make()->setType('integer')->setColumn('adslAturCurrSnrMgn'),
            'aturCurrentAttenuation' => MatchFilter::make()->setType('integer')->setColumn('adslAturCurrAtn'),
            'aturCurrentOutputPower' => MatchFilter::make()->setType('integer')->setColumn('adslAturCurrOutputPwr'),
            'aturCurrentAttainableRate' => MatchFilter::make()->setType('integer')->setColumn('adslAturCurrAttainableRate'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'lineCoding' => SortableFilter::make()->setColumn('adslLineCoding'),
            'lineCategory' => SortableFilter::make()->setColumn('adslLineType'),
            'atucVendorId' => SortableFilter::make()->setColumn('adslAtucInvVendorID'),
            'atucVersionNumber' => SortableFilter::make()->setColumn('adslAtucInvVersionNumber'),
            'atucCurrentSnrMargin' => SortableFilter::make()->setColumn('adslAtucCurrSnrMgn'),
            'atucCurrentAttenuation' => SortableFilter::make()->setColumn('adslAtucCurrAtn'),
            'atucCurrentOutputPower' => SortableFilter::make()->setColumn('adslAtucCurrOutputPwr'),
            'atucCurrentAttainableRate' => SortableFilter::make()->setColumn('adslAtucCurrAttainableRate'),
            'atucChannelCurrentTransmitRate' => SortableFilter::make()->setColumn('adslAtucChanCurrTxRate'),
            'aturSerialNumber' => SortableFilter::make()->setColumn('adslAturInvSerialNumber'),
            'aturVendorId' => SortableFilter::make()->setColumn('adslAturInvVendorID'),
            'aturVersionNumber' => SortableFilter::make()->setColumn('adslAturInvVersionNumber'),
            'aturChannelCurrentTransmitRate' => SortableFilter::make()->setColumn('adslAturChanCurrTxRate'),
            'aturCurrentSnrMargin' => SortableFilter::make()->setColumn('adslAturCurrSnrMgn'),
            'aturCurrentAttenuation' => SortableFilter::make()->setColumn('adslAturCurrAtn'),
            'aturCurrentOutputPower' => SortableFilter::make()->setColumn('adslAturCurrOutputPwr'),
            'aturCurrentAttainableRate' => SortableFilter::make()->setColumn('adslAturCurrAttainableRate'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('lineCoding', fn ($value, $model) => $model->adslLineCoding)->readonly(),
            field('lineCategory', fn ($value, $model) => $model->adslLineType)->readonly(),
            field('atucVendorId', fn ($value, $model) => $model->adslAtucInvVendorID)->readonly(),
            field('atucVersionNumber', fn ($value, $model) => $model->adslAtucInvVersionNumber)->readonly(),
            field('atucCurrentSnrMargin', fn ($value, $model) => $model->adslAtucCurrSnrMgn)->readonly(),
            field('atucCurrentAttenuation', fn ($value, $model) => $model->adslAtucCurrAtn)->readonly(),
            field('atucCurrentOutputPower', fn ($value, $model) => $model->adslAtucCurrOutputPwr)->readonly(),
            field('atucCurrentAttainableRate', fn ($value, $model) => $model->adslAtucCurrAttainableRate)->readonly(),
            field('atucChannelCurrentTransmitRate', fn ($value, $model) => $model->adslAtucChanCurrTxRate)->readonly(),
            field('aturSerialNumber', fn ($value, $model) => $model->adslAturInvSerialNumber)->readonly(),
            field('aturVendorId', fn ($value, $model) => $model->adslAturInvVendorID)->readonly(),
            field('aturVersionNumber', fn ($value, $model) => $model->adslAturInvVersionNumber)->readonly(),
            field('aturChannelCurrentTransmitRate', fn ($value, $model) => $model->adslAturChanCurrTxRate)->readonly(),
            field('aturCurrentSnrMargin', fn ($value, $model) => $model->adslAturCurrSnrMgn)->readonly(),
            field('aturCurrentAttenuation', fn ($value, $model) => $model->adslAturCurrAtn)->readonly(),
            field('aturCurrentOutputPower', fn ($value, $model) => $model->adslAturCurrOutputPwr)->readonly(),
            field('aturCurrentAttainableRate', fn ($value, $model) => $model->adslAturCurrAttainableRate)->readonly(),
        ];
    }

    /**
     * ADSL port stats are discovered automatically by LibreNMS during the polling process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * ADSL port stats are managed by the LibreNMS polling process.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
