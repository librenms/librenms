<?php

namespace App\Restify;

use App\Models\MplsSap;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class MplsSapRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = MplsSap::class;

    public static string $id = 'sap_id';

    public static string $title = 'sapDescription';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
            'service' => BelongsTo::make('service', MplsServiceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'interfaceName' => SearchableFilter::make()->setColumn('ifName'),
        ];
    }

    public static function matches(): array
    {
        return [
            'serviceOid' => MatchFilter::make()->setType('text')->setColumn('svc_oid'),
            'portId' => MatchFilter::make()->setType('text')->setColumn('sapPortId'),
            'interfaceName' => MatchFilter::make()->setType('text')->setColumn('ifName'),
            'encapsulationValue' => MatchFilter::make()->setType('text')->setColumn('sapEncapValue'),
            'rowStatus' => MatchFilter::make()->setType('text')->setColumn('sapRowStatus'),
            'category' => MatchFilter::make()->setType('text')->setColumn('sapType'),
            'description' => MatchFilter::make()->setType('text')->setColumn('sapDescription'),
            'adminStatus' => MatchFilter::make()->setType('text')->setColumn('sapAdminStatus'),
            'operationalStatus' => MatchFilter::make()->setType('text')->setColumn('sapOperStatus'),
            'updatedAt' => MatchFilter::make()->setType('datetime')->setColumn('sapLastMgmtChange'),
            'statusChangedAt' => MatchFilter::make()->setType('datetime')->setColumn('sapLastStatusChange'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'serviceOid' => SortableFilter::make()->setColumn('svc_oid'),
            'portId' => SortableFilter::make()->setColumn('sapPortId'),
            'interfaceName' => SortableFilter::make()->setColumn('ifName'),
            'encapsulationValue' => SortableFilter::make()->setColumn('sapEncapValue'),
            'rowStatus' => SortableFilter::make()->setColumn('sapRowStatus'),
            'category' => SortableFilter::make()->setColumn('sapType'),
            'description' => SortableFilter::make()->setColumn('sapDescription'),
            'adminStatus' => SortableFilter::make()->setColumn('sapAdminStatus'),
            'operationalStatus' => SortableFilter::make()->setColumn('sapOperStatus'),
            'updatedAt' => SortableFilter::make()->setColumn('sapLastMgmtChange'),
            'statusChangedAt' => SortableFilter::make()->setColumn('sapLastStatusChange'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('serviceOid', fn ($value, $model) => $model->svc_oid)->readonly(),
            field('portId', fn ($value, $model) => $model->sapPortId)->readonly(),
            field('interfaceName', fn ($value, $model) => $model->ifName)->readonly(),
            field('encapsulationValue', fn ($value, $model) => $model->sapEncapValue)->readonly(),
            field('rowStatus', fn ($value, $model) => $model->sapRowStatus)->readonly(),
            field('category', fn ($value, $model) => $model->sapType)->readonly(),
            field('description', fn ($value, $model) => $model->sapDescription)->readonly(),
            field('adminStatus', fn ($value, $model) => $model->sapAdminStatus)->readonly(),
            field('operationalStatus', fn ($value, $model) => $model->sapOperStatus)->readonly(),
            field('updatedAt', fn ($value, $model) => $model->sapLastMgmtChange)->readonly(),
            field('statusChangedAt', fn ($value, $model) => $model->sapLastStatusChange)->readonly(),
        ];
    }

    /**
     * MPLS SAPs are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * MPLS SAPs are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
