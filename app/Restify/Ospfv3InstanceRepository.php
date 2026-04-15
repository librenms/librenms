<?php

namespace App\Restify;

use App\Models\Ospfv3Instance;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class Ospfv3InstanceRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Ospfv3Instance::class;

    public static string $title = 'ospfv3RouterId';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'routerId' => SearchableFilter::make()->setColumn('ospfv3RouterId'),
        ];
    }

    public static function matches(): array
    {
        return [
            'routerId' => MatchFilter::make()->setType('text')->setColumn('ospfv3RouterId'),
            'adminStatus' => MatchFilter::make()->setType('text')->setColumn('ospfv3AdminStatus'),
            'versionNumber' => MatchFilter::make()->setType('integer')->setColumn('ospfv3VersionNumber'),
            'isAreaBorderRouter' => MatchFilter::make()->setType('bool')->setColumn('ospfv3AreaBdrRtrStatus'),
            'isAsBorderRouter' => MatchFilter::make()->setType('bool')->setColumn('ospfv3ASBdrRtrStatus'),
            'asScopeLsaCount' => MatchFilter::make()->setType('integer')->setColumn('ospfv3AsScopeLsaCount'),
            'asScopeLsaChecksumSum' => MatchFilter::make()->setType('integer')->setColumn('ospfv3AsScopeLsaCksumSum'),
            'externalLsaCount' => MatchFilter::make()->setType('integer')->setColumn('ospfv3ExtLsaCount'),
            'originateNewLsas' => MatchFilter::make()->setType('integer')->setColumn('ospfv3OriginateNewLsas'),
            'receiveNewLsas' => MatchFilter::make()->setType('integer')->setColumn('ospfv3RxNewLsas'),
            'externalAreaLsdbLimit' => MatchFilter::make()->setType('integer')->setColumn('ospfv3ExtAreaLsdbLimit'),
            'exitOverflowInterval' => MatchFilter::make()->setType('integer')->setColumn('ospfv3ExitOverflowInterval'),
            'referenceBandwidth' => MatchFilter::make()->setType('integer')->setColumn('ospfv3ReferenceBandwidth'),
            'restartSupport' => MatchFilter::make()->setType('text')->setColumn('ospfv3RestartSupport'),
            'restartInterval' => MatchFilter::make()->setType('integer')->setColumn('ospfv3RestartInterval'),
            'restartStrictLsaChecking' => MatchFilter::make()->setType('bool')->setColumn('ospfv3RestartStrictLsaChecking'),
            'restartStatus' => MatchFilter::make()->setType('text')->setColumn('ospfv3RestartStatus'),
            'restartAge' => MatchFilter::make()->setType('integer')->setColumn('ospfv3RestartAge'),
            'restartExitReason' => MatchFilter::make()->setType('text')->setColumn('ospfv3RestartExitReason'),
            'stubRouterSupport' => MatchFilter::make()->setType('text')->setColumn('ospfv3StubRouterSupport'),
            'stubRouterAdvertisement' => MatchFilter::make()->setType('text')->setColumn('ospfv3StubRouterAdvertisement'),
            'discontinuityTime' => MatchFilter::make()->setType('integer')->setColumn('ospfv3DiscontinuityTime'),
            'restartTime' => MatchFilter::make()->setType('integer')->setColumn('ospfv3RestartTime'),
            'contextName' => MatchFilter::make()->setType('text')->setColumn('context_name'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'routerId' => SortableFilter::make()->setColumn('ospfv3RouterId'),
            'adminStatus' => SortableFilter::make()->setColumn('ospfv3AdminStatus'),
            'versionNumber' => SortableFilter::make()->setColumn('ospfv3VersionNumber'),
            'isAreaBorderRouter' => SortableFilter::make()->setColumn('ospfv3AreaBdrRtrStatus'),
            'isAsBorderRouter' => SortableFilter::make()->setColumn('ospfv3ASBdrRtrStatus'),
            'asScopeLsaCount' => SortableFilter::make()->setColumn('ospfv3AsScopeLsaCount'),
            'asScopeLsaChecksumSum' => SortableFilter::make()->setColumn('ospfv3AsScopeLsaCksumSum'),
            'externalLsaCount' => SortableFilter::make()->setColumn('ospfv3ExtLsaCount'),
            'originateNewLsas' => SortableFilter::make()->setColumn('ospfv3OriginateNewLsas'),
            'receiveNewLsas' => SortableFilter::make()->setColumn('ospfv3RxNewLsas'),
            'externalAreaLsdbLimit' => SortableFilter::make()->setColumn('ospfv3ExtAreaLsdbLimit'),
            'exitOverflowInterval' => SortableFilter::make()->setColumn('ospfv3ExitOverflowInterval'),
            'referenceBandwidth' => SortableFilter::make()->setColumn('ospfv3ReferenceBandwidth'),
            'restartSupport' => SortableFilter::make()->setColumn('ospfv3RestartSupport'),
            'restartInterval' => SortableFilter::make()->setColumn('ospfv3RestartInterval'),
            'restartStrictLsaChecking' => SortableFilter::make()->setColumn('ospfv3RestartStrictLsaChecking'),
            'restartStatus' => SortableFilter::make()->setColumn('ospfv3RestartStatus'),
            'restartAge' => SortableFilter::make()->setColumn('ospfv3RestartAge'),
            'restartExitReason' => SortableFilter::make()->setColumn('ospfv3RestartExitReason'),
            'stubRouterSupport' => SortableFilter::make()->setColumn('ospfv3StubRouterSupport'),
            'stubRouterAdvertisement' => SortableFilter::make()->setColumn('ospfv3StubRouterAdvertisement'),
            'discontinuityTime' => SortableFilter::make()->setColumn('ospfv3DiscontinuityTime'),
            'restartTime' => SortableFilter::make()->setColumn('ospfv3RestartTime'),
            'contextName' => SortableFilter::make()->setColumn('context_name'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('routerId', fn ($value, $model) => $model->ospfv3RouterId)->readonly(),
            field('adminStatus', fn ($value, $model) => $model->ospfv3AdminStatus)->readonly(),
            field('versionNumber', fn ($value, $model) => $model->ospfv3VersionNumber)->readonly(),
            field('isAreaBorderRouter', fn ($value, $model) => $model->ospfv3AreaBdrRtrStatus)->readonly(),
            field('isAsBorderRouter', fn ($value, $model) => $model->ospfv3ASBdrRtrStatus)->readonly(),
            field('asScopeLsaCount', fn ($value, $model) => $model->ospfv3AsScopeLsaCount)->readonly(),
            field('asScopeLsaChecksumSum', fn ($value, $model) => $model->ospfv3AsScopeLsaCksumSum)->readonly(),
            field('externalLsaCount', fn ($value, $model) => $model->ospfv3ExtLsaCount)->readonly(),
            field('originateNewLsas', fn ($value, $model) => $model->ospfv3OriginateNewLsas)->readonly(),
            field('receiveNewLsas', fn ($value, $model) => $model->ospfv3RxNewLsas)->readonly(),
            field('externalAreaLsdbLimit', fn ($value, $model) => $model->ospfv3ExtAreaLsdbLimit)->readonly(),
            field('exitOverflowInterval', fn ($value, $model) => $model->ospfv3ExitOverflowInterval)->readonly(),
            field('referenceBandwidth', fn ($value, $model) => $model->ospfv3ReferenceBandwidth)->readonly(),
            field('restartSupport', fn ($value, $model) => $model->ospfv3RestartSupport)->readonly(),
            field('restartInterval', fn ($value, $model) => $model->ospfv3RestartInterval)->readonly(),
            field('restartStrictLsaChecking', fn ($value, $model) => $model->ospfv3RestartStrictLsaChecking)->readonly(),
            field('restartStatus', fn ($value, $model) => $model->ospfv3RestartStatus)->readonly(),
            field('restartAge', fn ($value, $model) => $model->ospfv3RestartAge)->readonly(),
            field('restartExitReason', fn ($value, $model) => $model->ospfv3RestartExitReason)->readonly(),
            field('stubRouterSupport', fn ($value, $model) => $model->ospfv3StubRouterSupport)->readonly(),
            field('stubRouterAdvertisement', fn ($value, $model) => $model->ospfv3StubRouterAdvertisement)->readonly(),
            field('discontinuityTime', fn ($value, $model) => $model->ospfv3DiscontinuityTime)->readonly(),
            field('restartTime', fn ($value, $model) => $model->ospfv3RestartTime)->readonly(),
            field('contextName', fn ($value, $model) => $model->context_name)->readonly(),
        ];
    }

    /**
     * OSPFv3 instances are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * OSPFv3 instances are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
