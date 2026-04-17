<?php

namespace App\Restify;

use App\Models\Ospfv3Nbr;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class Ospfv3NbrRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = Ospfv3Nbr::class;

    public static string $uriKey = 'ospfv3-neighbors';

    public static string $title = 'ospfv3NbrRtrId';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'routerId' => SearchableFilter::make()->setColumn('ospfv3NbrRtrId'),
        ];
    }

    public static function matches(): array
    {
        return [
            'routerId' => MatchFilter::make()->setType('text')->setColumn('ospfv3NbrRtrId'),
            'address' => MatchFilter::make()->setType('text')->setColumn('ospfv3NbrAddress'),
            'addressCategory' => MatchFilter::make()->setType('text')->setColumn('ospfv3NbrAddressType'),
            'options' => MatchFilter::make()->setType('integer')->setColumn('ospfv3NbrOptions'),
            'priority' => MatchFilter::make()->setType('integer')->setColumn('ospfv3NbrPriority'),
            'state' => MatchFilter::make()->setType('text')->setColumn('ospfv3NbrState'),
            'events' => MatchFilter::make()->setType('integer')->setColumn('ospfv3NbrEvents'),
            'lsRetransmitQueueLength' => MatchFilter::make()->setType('integer')->setColumn('ospfv3NbrLsRetransQLen'),
            'nbmaStatus' => MatchFilter::make()->setType('text')->setColumn('ospfv3NbmaNbrStatus'),
            'nbmaPermanence' => MatchFilter::make()->setType('text')->setColumn('ospfv3NbmaNbrPermanence'),
            'isHelloSuppressed' => MatchFilter::make()->setType('bool')->setColumn('ospfv3NbrHelloSuppressed'),
            'interfaceId' => MatchFilter::make()->setType('integer')->setColumn('ospfv3NbrIfId'),
            'restartHelperStatus' => MatchFilter::make()->setType('text')->setColumn('ospfv3NbrRestartHelperStatus'),
            'restartHelperAge' => MatchFilter::make()->setType('integer')->setColumn('ospfv3NbrRestartHelperAge'),
            'restartHelperExitReason' => MatchFilter::make()->setType('text')->setColumn('ospfv3NbrRestartHelperExitReason'),
            'contextName' => MatchFilter::make()->setType('text')->setColumn('context_name'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'routerId' => SortableFilter::make()->setColumn('ospfv3NbrRtrId'),
            'address' => SortableFilter::make()->setColumn('ospfv3NbrAddress'),
            'addressCategory' => SortableFilter::make()->setColumn('ospfv3NbrAddressType'),
            'options' => SortableFilter::make()->setColumn('ospfv3NbrOptions'),
            'priority' => SortableFilter::make()->setColumn('ospfv3NbrPriority'),
            'state' => SortableFilter::make()->setColumn('ospfv3NbrState'),
            'events' => SortableFilter::make()->setColumn('ospfv3NbrEvents'),
            'lsRetransmitQueueLength' => SortableFilter::make()->setColumn('ospfv3NbrLsRetransQLen'),
            'nbmaStatus' => SortableFilter::make()->setColumn('ospfv3NbmaNbrStatus'),
            'nbmaPermanence' => SortableFilter::make()->setColumn('ospfv3NbmaNbrPermanence'),
            'isHelloSuppressed' => SortableFilter::make()->setColumn('ospfv3NbrHelloSuppressed'),
            'interfaceId' => SortableFilter::make()->setColumn('ospfv3NbrIfId'),
            'restartHelperStatus' => SortableFilter::make()->setColumn('ospfv3NbrRestartHelperStatus'),
            'restartHelperAge' => SortableFilter::make()->setColumn('ospfv3NbrRestartHelperAge'),
            'restartHelperExitReason' => SortableFilter::make()->setColumn('ospfv3NbrRestartHelperExitReason'),
            'contextName' => SortableFilter::make()->setColumn('context_name'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('routerId', fn ($value, $model) => $model->ospfv3NbrRtrId)->readonly(),
            field('address', fn ($value, $model) => $model->ospfv3NbrAddress)->readonly(),
            field('addressCategory', fn ($value, $model) => $model->ospfv3NbrAddressType)->readonly(),
            field('options', fn ($value, $model) => $model->ospfv3NbrOptions)->readonly(),
            field('priority', fn ($value, $model) => $model->ospfv3NbrPriority)->readonly(),
            field('state', fn ($value, $model) => $model->ospfv3NbrState)->readonly(),
            field('events', fn ($value, $model) => $model->ospfv3NbrEvents)->readonly(),
            field('lsRetransmitQueueLength', fn ($value, $model) => $model->ospfv3NbrLsRetransQLen)->readonly(),
            field('nbmaStatus', fn ($value, $model) => $model->ospfv3NbmaNbrStatus)->readonly(),
            field('nbmaPermanence', fn ($value, $model) => $model->ospfv3NbmaNbrPermanence)->readonly(),
            field('isHelloSuppressed', fn ($value, $model) => $model->ospfv3NbrHelloSuppressed)->readonly(),
            field('interfaceId', fn ($value, $model) => $model->ospfv3NbrIfId)->readonly(),
            field('restartHelperStatus', fn ($value, $model) => $model->ospfv3NbrRestartHelperStatus)->readonly(),
            field('restartHelperAge', fn ($value, $model) => $model->ospfv3NbrRestartHelperAge)->readonly(),
            field('restartHelperExitReason', fn ($value, $model) => $model->ospfv3NbrRestartHelperExitReason)->readonly(),
            field('contextName', fn ($value, $model) => $model->context_name)->readonly(),
        ];
    }

    /**
     * OSPFv3 neighbors are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * OSPFv3 neighbors are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
