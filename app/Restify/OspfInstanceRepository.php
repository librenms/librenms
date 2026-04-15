<?php

namespace App\Restify;

use App\Models\OspfInstance;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class OspfInstanceRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = OspfInstance::class;

    public static string $title = 'ospfRouterId';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'routerId' => SearchableFilter::make()->setColumn('ospfRouterId'),
        ];
    }

    public static function matches(): array
    {
        return [
            'routerId' => MatchFilter::make()->setType('text')->setColumn('ospfRouterId'),
            'adminStatus' => MatchFilter::make()->setType('text')->setColumn('ospfAdminStat'),
            'versionNumber' => MatchFilter::make()->setType('integer')->setColumn('ospfVersionNumber'),
            'isAreaBorderRouter' => MatchFilter::make()->setType('bool')->setColumn('ospfAreaBdrRtrStatus'),
            'isAsBorderRouter' => MatchFilter::make()->setType('bool')->setColumn('ospfASBdrRtrStatus'),
            'externLsaCount' => MatchFilter::make()->setType('integer')->setColumn('ospfExternLsaCount'),
            'externLsaChecksumSum' => MatchFilter::make()->setType('integer')->setColumn('ospfExternLsaCksumSum'),
            'hasTosSupport' => MatchFilter::make()->setType('bool')->setColumn('ospfTOSSupport'),
            'originateNewLsas' => MatchFilter::make()->setType('integer')->setColumn('ospfOriginateNewLsas'),
            'receiveNewLsas' => MatchFilter::make()->setType('integer')->setColumn('ospfRxNewLsas'),
            'externalLsdbLimit' => MatchFilter::make()->setType('integer')->setColumn('ospfExtLsdbLimit'),
            'multicastExtensions' => MatchFilter::make()->setType('text')->setColumn('ospfMulticastExtensions'),
            'exitOverflowInterval' => MatchFilter::make()->setType('integer')->setColumn('ospfExitOverflowInterval'),
            'demandExtensions' => MatchFilter::make()->setType('text')->setColumn('ospfDemandExtensions'),
            'contextName' => MatchFilter::make()->setType('text')->setColumn('context_name'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'routerId' => SortableFilter::make()->setColumn('ospfRouterId'),
            'adminStatus' => SortableFilter::make()->setColumn('ospfAdminStat'),
            'versionNumber' => SortableFilter::make()->setColumn('ospfVersionNumber'),
            'isAreaBorderRouter' => SortableFilter::make()->setColumn('ospfAreaBdrRtrStatus'),
            'isAsBorderRouter' => SortableFilter::make()->setColumn('ospfASBdrRtrStatus'),
            'externLsaCount' => SortableFilter::make()->setColumn('ospfExternLsaCount'),
            'externLsaChecksumSum' => SortableFilter::make()->setColumn('ospfExternLsaCksumSum'),
            'hasTosSupport' => SortableFilter::make()->setColumn('ospfTOSSupport'),
            'originateNewLsas' => SortableFilter::make()->setColumn('ospfOriginateNewLsas'),
            'receiveNewLsas' => SortableFilter::make()->setColumn('ospfRxNewLsas'),
            'externalLsdbLimit' => SortableFilter::make()->setColumn('ospfExtLsdbLimit'),
            'multicastExtensions' => SortableFilter::make()->setColumn('ospfMulticastExtensions'),
            'exitOverflowInterval' => SortableFilter::make()->setColumn('ospfExitOverflowInterval'),
            'demandExtensions' => SortableFilter::make()->setColumn('ospfDemandExtensions'),
            'contextName' => SortableFilter::make()->setColumn('context_name'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('routerId', fn ($value, $model) => $model->ospfRouterId)->readonly(),
            field('adminStatus', fn ($value, $model) => $model->ospfAdminStat)->readonly(),
            field('versionNumber', fn ($value, $model) => $model->ospfVersionNumber)->readonly(),
            field('isAreaBorderRouter', fn ($value, $model) => $model->ospfAreaBdrRtrStatus)->readonly(),
            field('isAsBorderRouter', fn ($value, $model) => $model->ospfASBdrRtrStatus)->readonly(),
            field('externLsaCount', fn ($value, $model) => $model->ospfExternLsaCount)->readonly(),
            field('externLsaChecksumSum', fn ($value, $model) => $model->ospfExternLsaCksumSum)->readonly(),
            field('hasTosSupport', fn ($value, $model) => $model->ospfTOSSupport)->readonly(),
            field('originateNewLsas', fn ($value, $model) => $model->ospfOriginateNewLsas)->readonly(),
            field('receiveNewLsas', fn ($value, $model) => $model->ospfRxNewLsas)->readonly(),
            field('externalLsdbLimit', fn ($value, $model) => $model->ospfExtLsdbLimit)->readonly(),
            field('multicastExtensions', fn ($value, $model) => $model->ospfMulticastExtensions)->readonly(),
            field('exitOverflowInterval', fn ($value, $model) => $model->ospfExitOverflowInterval)->readonly(),
            field('demandExtensions', fn ($value, $model) => $model->ospfDemandExtensions)->readonly(),
            field('contextName', fn ($value, $model) => $model->context_name)->readonly(),
        ];
    }

    /**
     * OSPF instances are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * OSPF instances are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
