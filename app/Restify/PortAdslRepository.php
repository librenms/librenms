<?php

namespace App\Restify;

use App\Models\PortAdsl;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class PortAdslRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = PortAdsl::class;

    public static string $id = 'port_id';

    public static string $title = 'adslLineCoding';

    public static array $match = [
        'port_id' => 'integer',
        'adslLineCoding' => 'text',
        'adslLineType' => 'text',
        'adslAtucInvVendorID' => 'text',
        'adslAtucInvVersionNumber' => 'text',
        'adslAtucCurrSnrMgn' => 'integer',
        'adslAtucCurrAtn' => 'integer',
        'adslAtucCurrOutputPwr' => 'integer',
        'adslAtucCurrAttainableRate' => 'integer',
        'adslAtucChanCurrTxRate' => 'integer',
        'adslAturInvSerialNumber' => 'text',
        'adslAturInvVendorID' => 'text',
        'adslAturInvVersionNumber' => 'text',
        'adslAturChanCurrTxRate' => 'integer',
        'adslAturCurrSnrMgn' => 'integer',
        'adslAturCurrAtn' => 'integer',
        'adslAturCurrOutputPwr' => 'integer',
        'adslAturCurrAttainableRate' => 'integer',
    ];

    public static array $sort = [
        'port_id',
        'adslLineCoding',
        'adslLineType',
        'adslAtucInvVendorID',
        'adslAtucInvVersionNumber',
        'adslAtucCurrSnrMgn',
        'adslAtucCurrAtn',
        'adslAtucCurrOutputPwr',
        'adslAtucCurrAttainableRate',
        'adslAtucChanCurrTxRate',
        'adslAturInvSerialNumber',
        'adslAturInvVendorID',
        'adslAturInvVersionNumber',
        'adslAturChanCurrTxRate',
        'adslAturCurrSnrMgn',
        'adslAturCurrAtn',
        'adslAturCurrOutputPwr',
        'adslAturCurrAttainableRate',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('port_id')->readonly(),
            field('adslLineCoding')->readonly(),
            field('adslLineType')->readonly(),
            field('adslAtucInvVendorID')->readonly(),
            field('adslAtucInvVersionNumber')->readonly(),
            field('adslAtucCurrSnrMgn')->readonly(),
            field('adslAtucCurrAtn')->readonly(),
            field('adslAtucCurrOutputPwr')->readonly(),
            field('adslAtucCurrAttainableRate')->readonly(),
            field('adslAtucChanCurrTxRate')->readonly(),
            field('adslAturInvSerialNumber')->readonly(),
            field('adslAturInvVendorID')->readonly(),
            field('adslAturInvVersionNumber')->readonly(),
            field('adslAturChanCurrTxRate')->readonly(),
            field('adslAturCurrSnrMgn')->readonly(),
            field('adslAturCurrAtn')->readonly(),
            field('adslAturCurrOutputPwr')->readonly(),
            field('adslAturCurrAttainableRate')->readonly(),
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
