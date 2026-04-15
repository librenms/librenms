<?php

namespace App\Restify;

use App\Models\OspfNbr;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class OspfNbrRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = OspfNbr::class;

    public static string $title = 'ospfNbrIpAddr';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'ipAddress' => SearchableFilter::make()->setColumn('ospfNbrIpAddr'),
        ];
    }

    public static function matches(): array
    {
        return [
            'neighborKey' => MatchFilter::make()->setType('text')->setColumn('ospf_nbr_id'),
            'ipAddress' => MatchFilter::make()->setType('text')->setColumn('ospfNbrIpAddr'),
            'addressLessIndex' => MatchFilter::make()->setType('integer')->setColumn('ospfNbrAddressLessIndex'),
            'routerId' => MatchFilter::make()->setType('text')->setColumn('ospfNbrRtrId'),
            'options' => MatchFilter::make()->setType('integer')->setColumn('ospfNbrOptions'),
            'priority' => MatchFilter::make()->setType('integer')->setColumn('ospfNbrPriority'),
            'state' => MatchFilter::make()->setType('text')->setColumn('ospfNbrState'),
            'events' => MatchFilter::make()->setType('integer')->setColumn('ospfNbrEvents'),
            'lsRetransmitQueueLength' => MatchFilter::make()->setType('integer')->setColumn('ospfNbrLsRetransQLen'),
            'nbmaStatus' => MatchFilter::make()->setType('text')->setColumn('ospfNbmaNbrStatus'),
            'nbmaPermanence' => MatchFilter::make()->setType('text')->setColumn('ospfNbmaNbrPermanence'),
            'isHelloSuppressed' => MatchFilter::make()->setType('bool')->setColumn('ospfNbrHelloSuppressed'),
            'contextName' => MatchFilter::make()->setType('text')->setColumn('context_name'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'neighborKey' => SortableFilter::make()->setColumn('ospf_nbr_id'),
            'ipAddress' => SortableFilter::make()->setColumn('ospfNbrIpAddr'),
            'addressLessIndex' => SortableFilter::make()->setColumn('ospfNbrAddressLessIndex'),
            'routerId' => SortableFilter::make()->setColumn('ospfNbrRtrId'),
            'options' => SortableFilter::make()->setColumn('ospfNbrOptions'),
            'priority' => SortableFilter::make()->setColumn('ospfNbrPriority'),
            'state' => SortableFilter::make()->setColumn('ospfNbrState'),
            'events' => SortableFilter::make()->setColumn('ospfNbrEvents'),
            'lsRetransmitQueueLength' => SortableFilter::make()->setColumn('ospfNbrLsRetransQLen'),
            'nbmaStatus' => SortableFilter::make()->setColumn('ospfNbmaNbrStatus'),
            'nbmaPermanence' => SortableFilter::make()->setColumn('ospfNbmaNbrPermanence'),
            'isHelloSuppressed' => SortableFilter::make()->setColumn('ospfNbrHelloSuppressed'),
            'contextName' => SortableFilter::make()->setColumn('context_name'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('neighborKey', fn ($value, $model) => $model->ospf_nbr_id)->readonly(),
            field('ipAddress', fn ($value, $model) => $model->ospfNbrIpAddr)->readonly(),
            field('addressLessIndex', fn ($value, $model) => $model->ospfNbrAddressLessIndex)->readonly(),
            field('routerId', fn ($value, $model) => $model->ospfNbrRtrId)->readonly(),
            field('options', fn ($value, $model) => $model->ospfNbrOptions)->readonly(),
            field('priority', fn ($value, $model) => $model->ospfNbrPriority)->readonly(),
            field('state', fn ($value, $model) => $model->ospfNbrState)->readonly(),
            field('events', fn ($value, $model) => $model->ospfNbrEvents)->readonly(),
            field('lsRetransmitQueueLength', fn ($value, $model) => $model->ospfNbrLsRetransQLen)->readonly(),
            field('nbmaStatus', fn ($value, $model) => $model->ospfNbmaNbrStatus)->readonly(),
            field('nbmaPermanence', fn ($value, $model) => $model->ospfNbmaNbrPermanence)->readonly(),
            field('isHelloSuppressed', fn ($value, $model) => $model->ospfNbrHelloSuppressed)->readonly(),
            field('contextName', fn ($value, $model) => $model->context_name)->readonly(),
        ];
    }

    /**
     * OSPF neighbors are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * OSPF neighbors are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
