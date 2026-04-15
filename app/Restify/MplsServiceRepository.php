<?php

namespace App\Restify;

use App\Models\MplsService;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class MplsServiceRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = MplsService::class;

    /**
     * Override to avoid ambiguity with the existing ServiceRepository ('services').
     */
    public static string $uriKey = 'mpls-services';

    public static string $id = 'svc_id';

    public static string $title = 'svcDescription';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'description' => SearchableFilter::make()->setColumn('svcDescription'),
        ];
    }

    public static function matches(): array
    {
        return [
            'oid' => MatchFilter::make()->setType('text')->setColumn('svc_oid'),
            'rowStatus' => MatchFilter::make()->setType('text')->setColumn('svcRowStatus'),
            'category' => MatchFilter::make()->setType('text')->setColumn('svcType'),
            'customerId' => MatchFilter::make()->setType('integer')->setColumn('svcCustId'),
            'adminStatus' => MatchFilter::make()->setType('text')->setColumn('svcAdminStatus'),
            'operationalStatus' => MatchFilter::make()->setType('text')->setColumn('svcOperStatus'),
            'description' => MatchFilter::make()->setType('text')->setColumn('svcDescription'),
            'mtu' => MatchFilter::make()->setType('integer')->setColumn('svcMtu'),
            'sapCount' => MatchFilter::make()->setType('integer')->setColumn('svcNumSaps'),
            'sdpCount' => MatchFilter::make()->setType('integer')->setColumn('svcNumSdps'),
            'updatedAt' => MatchFilter::make()->setType('datetime')->setColumn('svcLastMgmtChange'),
            'statusChangedAt' => MatchFilter::make()->setType('datetime')->setColumn('svcLastStatusChange'),
            'virtualRouterId' => MatchFilter::make()->setType('integer')->setColumn('svcVRouterId'),
            'tlsMacLearning' => MatchFilter::make()->setType('text')->setColumn('svcTlsMacLearning'),
            'tlsStpAdminStatus' => MatchFilter::make()->setType('text')->setColumn('svcTlsStpAdminStatus'),
            'tlsStpOperationalStatus' => MatchFilter::make()->setType('text')->setColumn('svcTlsStpOperStatus'),
            'tlsFdbTableSize' => MatchFilter::make()->setType('integer')->setColumn('svcTlsFdbTableSize'),
            'tlsFdbEntryCount' => MatchFilter::make()->setType('integer')->setColumn('svcTlsFdbNumEntries'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'oid' => SortableFilter::make()->setColumn('svc_oid'),
            'rowStatus' => SortableFilter::make()->setColumn('svcRowStatus'),
            'category' => SortableFilter::make()->setColumn('svcType'),
            'customerId' => SortableFilter::make()->setColumn('svcCustId'),
            'adminStatus' => SortableFilter::make()->setColumn('svcAdminStatus'),
            'operationalStatus' => SortableFilter::make()->setColumn('svcOperStatus'),
            'description' => SortableFilter::make()->setColumn('svcDescription'),
            'mtu' => SortableFilter::make()->setColumn('svcMtu'),
            'sapCount' => SortableFilter::make()->setColumn('svcNumSaps'),
            'sdpCount' => SortableFilter::make()->setColumn('svcNumSdps'),
            'updatedAt' => SortableFilter::make()->setColumn('svcLastMgmtChange'),
            'statusChangedAt' => SortableFilter::make()->setColumn('svcLastStatusChange'),
            'virtualRouterId' => SortableFilter::make()->setColumn('svcVRouterId'),
            'tlsMacLearning' => SortableFilter::make()->setColumn('svcTlsMacLearning'),
            'tlsStpAdminStatus' => SortableFilter::make()->setColumn('svcTlsStpAdminStatus'),
            'tlsStpOperationalStatus' => SortableFilter::make()->setColumn('svcTlsStpOperStatus'),
            'tlsFdbTableSize' => SortableFilter::make()->setColumn('svcTlsFdbTableSize'),
            'tlsFdbEntryCount' => SortableFilter::make()->setColumn('svcTlsFdbNumEntries'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('oid', fn ($value, $model) => $model->svc_oid)->readonly(),
            field('rowStatus', fn ($value, $model) => $model->svcRowStatus)->readonly(),
            field('category', fn ($value, $model) => $model->svcType)->readonly(),
            field('customerId', fn ($value, $model) => $model->svcCustId)->readonly(),
            field('adminStatus', fn ($value, $model) => $model->svcAdminStatus)->readonly(),
            field('operationalStatus', fn ($value, $model) => $model->svcOperStatus)->readonly(),
            field('description', fn ($value, $model) => $model->svcDescription)->readonly(),
            field('mtu', fn ($value, $model) => $model->svcMtu)->readonly(),
            field('sapCount', fn ($value, $model) => $model->svcNumSaps)->readonly(),
            field('sdpCount', fn ($value, $model) => $model->svcNumSdps)->readonly(),
            field('updatedAt', fn ($value, $model) => $model->svcLastMgmtChange)->readonly(),
            field('statusChangedAt', fn ($value, $model) => $model->svcLastStatusChange)->readonly(),
            field('virtualRouterId', fn ($value, $model) => $model->svcVRouterId)->readonly(),
            field('tlsMacLearning', fn ($value, $model) => $model->svcTlsMacLearning)->readonly(),
            field('tlsStpAdminStatus', fn ($value, $model) => $model->svcTlsStpAdminStatus)->readonly(),
            field('tlsStpOperationalStatus', fn ($value, $model) => $model->svcTlsStpOperStatus)->readonly(),
            field('tlsFdbTableSize', fn ($value, $model) => $model->svcTlsFdbTableSize)->readonly(),
            field('tlsFdbEntryCount', fn ($value, $model) => $model->svcTlsFdbNumEntries)->readonly(),
        ];
    }

    /**
     * MPLS services are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * MPLS services are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
