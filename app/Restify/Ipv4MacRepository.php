<?php

namespace App\Restify;

use App\Models\Ipv4Mac;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class Ipv4MacRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = Ipv4Mac::class;

    public static string $title = 'ipv4_address';




    public static function searchables(): array
    {
        return [
            'macAddress' => SearchableFilter::make()->setColumn('mac_address'),
            'ipv4Address' => SearchableFilter::make()->setColumn('ipv4_address'),
        ];
    }

    public static function matches(): array
    {
        return [
            'macAddress' => MatchFilter::make()->setType('text')->setColumn('mac_address'),
            'ipv4Address' => MatchFilter::make()->setType('text')->setColumn('ipv4_address'),
            'contextName' => MatchFilter::make()->setType('text')->setColumn('context_name'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'macAddress' => SortableFilter::make()->setColumn('mac_address'),
            'ipv4Address' => SortableFilter::make()->setColumn('ipv4_address'),
            'contextName' => SortableFilter::make()->setColumn('context_name'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('macAddress', fn ($value, $model) => $model->mac_address)->readonly(),
            field('ipv4Address', fn ($value, $model) => $model->ipv4_address)->readonly(),
            field('contextName', fn ($value, $model) => $model->context_name)->readonly(),
        ];
    }

    /**
     * ARP entries are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * ARP entries are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
