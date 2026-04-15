<?php

namespace App\Restify;

use App\Models\Ipv4Address;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class Ipv4AddressRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = Ipv4Address::class;

    public static string $id = 'ipv4_address_id';

    public static string $title = 'ipv4_address';




    public static function searchables(): array
    {
        return [
            'address' => SearchableFilter::make()->setColumn('ipv4_address'),
        ];
    }

    public static function matches(): array
    {
        return [
            'address' => MatchFilter::make()->setType('text')->setColumn('ipv4_address'),
            'prefixLength' => MatchFilter::make()->setType('integer')->setColumn('ipv4_prefixlen'),
            'contextName' => MatchFilter::make()->setType('text')->setColumn('context_name'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'address' => SortableFilter::make()->setColumn('ipv4_address'),
            'prefixLength' => SortableFilter::make()->setColumn('ipv4_prefixlen'),
            'contextName' => SortableFilter::make()->setColumn('context_name'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('address', fn ($value, $model) => $model->ipv4_address)->readonly(),
            field('prefixLength', fn ($value, $model) => $model->ipv4_prefixlen)->readonly(),
            field('contextName', fn ($value, $model) => $model->context_name)->readonly(),
        ];
    }

    /**
     * IPv4 addresses are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * IPv4 addresses are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
