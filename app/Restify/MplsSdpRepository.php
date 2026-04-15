<?php

namespace App\Restify;

use App\Models\MplsSdp;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class MplsSdpRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = MplsSdp::class;

    public static string $id = 'sdp_id';

    public static string $title = 'sdpDescription';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'description' => SearchableFilter::make()->setColumn('sdpDescription'),
        ];
    }

    public static function matches(): array
    {
        return [
            'oid' => MatchFilter::make()->setType('text')->setColumn('sdp_oid'),
            'rowStatus' => MatchFilter::make()->setType('text')->setColumn('sdpRowStatus'),
            'delivery' => MatchFilter::make()->setType('text')->setColumn('sdpDelivery'),
            'description' => MatchFilter::make()->setType('text')->setColumn('sdpDescription'),
            'adminStatus' => MatchFilter::make()->setType('text')->setColumn('sdpAdminStatus'),
            'operationalStatus' => MatchFilter::make()->setType('text')->setColumn('sdpOperStatus'),
            'adminPathMtu' => MatchFilter::make()->setType('integer')->setColumn('sdpAdminPathMtu'),
            'operationalPathMtu' => MatchFilter::make()->setType('integer')->setColumn('sdpOperPathMtu'),
            'updatedAt' => MatchFilter::make()->setType('datetime')->setColumn('sdpLastMgmtChange'),
            'statusChangedAt' => MatchFilter::make()->setType('datetime')->setColumn('sdpLastStatusChange'),
            'activeLspCategory' => MatchFilter::make()->setType('text')->setColumn('sdpActiveLspType'),
            'farEndAddressCategory' => MatchFilter::make()->setType('text')->setColumn('sdpFarEndInetAddressType'),
            'farEndAddress' => MatchFilter::make()->setType('text')->setColumn('sdpFarEndInetAddress'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'oid' => SortableFilter::make()->setColumn('sdp_oid'),
            'rowStatus' => SortableFilter::make()->setColumn('sdpRowStatus'),
            'delivery' => SortableFilter::make()->setColumn('sdpDelivery'),
            'description' => SortableFilter::make()->setColumn('sdpDescription'),
            'adminStatus' => SortableFilter::make()->setColumn('sdpAdminStatus'),
            'operationalStatus' => SortableFilter::make()->setColumn('sdpOperStatus'),
            'adminPathMtu' => SortableFilter::make()->setColumn('sdpAdminPathMtu'),
            'operationalPathMtu' => SortableFilter::make()->setColumn('sdpOperPathMtu'),
            'updatedAt' => SortableFilter::make()->setColumn('sdpLastMgmtChange'),
            'statusChangedAt' => SortableFilter::make()->setColumn('sdpLastStatusChange'),
            'activeLspCategory' => SortableFilter::make()->setColumn('sdpActiveLspType'),
            'farEndAddressCategory' => SortableFilter::make()->setColumn('sdpFarEndInetAddressType'),
            'farEndAddress' => SortableFilter::make()->setColumn('sdpFarEndInetAddress'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('oid', fn ($value, $model) => $model->sdp_oid)->readonly(),
            field('rowStatus', fn ($value, $model) => $model->sdpRowStatus)->readonly(),
            field('delivery', fn ($value, $model) => $model->sdpDelivery)->readonly(),
            field('description', fn ($value, $model) => $model->sdpDescription)->readonly(),
            field('adminStatus', fn ($value, $model) => $model->sdpAdminStatus)->readonly(),
            field('operationalStatus', fn ($value, $model) => $model->sdpOperStatus)->readonly(),
            field('adminPathMtu', fn ($value, $model) => $model->sdpAdminPathMtu)->readonly(),
            field('operationalPathMtu', fn ($value, $model) => $model->sdpOperPathMtu)->readonly(),
            field('updatedAt', fn ($value, $model) => $model->sdpLastMgmtChange)->readonly(),
            field('statusChangedAt', fn ($value, $model) => $model->sdpLastStatusChange)->readonly(),
            field('activeLspCategory', fn ($value, $model) => $model->sdpActiveLspType)->readonly(),
            field('farEndAddressCategory', fn ($value, $model) => $model->sdpFarEndInetAddressType)->readonly(),
            field('farEndAddress', fn ($value, $model) => $model->sdpFarEndInetAddress)->readonly(),
        ];
    }

    /**
     * MPLS SDPs are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * MPLS SDPs are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
