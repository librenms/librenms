<?php

namespace App\Restify;

use App\Models\Ipv6Address;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class Ipv6AddressRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = Ipv6Address::class;

    public static string $id = 'ipv6_address_id';

    public static string $title = 'ipv6_compressed';




    public static function searchables(): array
    {
        return [
            'address' => SearchableFilter::make()->setColumn('ipv6_address'),
        ];
    }

    public static function matches(): array
    {
        return [
            'address' => MatchFilter::make()->setType('text')->setColumn('ipv6_address'),
            'compressedAddress' => MatchFilter::make()->setType('text')->setColumn('ipv6_compressed'),
            'prefixLength' => MatchFilter::make()->setType('integer')->setColumn('ipv6_prefixlen'),
            'origin' => MatchFilter::make()->setType('text')->setColumn('ipv6_origin'),
            'contextName' => MatchFilter::make()->setType('text')->setColumn('context_name'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'address' => SortableFilter::make()->setColumn('ipv6_address'),
            'compressedAddress' => SortableFilter::make()->setColumn('ipv6_compressed'),
            'prefixLength' => SortableFilter::make()->setColumn('ipv6_prefixlen'),
            'origin' => SortableFilter::make()->setColumn('ipv6_origin'),
            'contextName' => SortableFilter::make()->setColumn('context_name'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('address', fn ($value, $model) => $model->ipv6_address)->readonly(),
            field('compressedAddress', fn ($value, $model) => $model->ipv6_compressed)->readonly(),
            field('prefixLength', fn ($value, $model) => $model->ipv6_prefixlen)->readonly(),
            field('origin', fn ($value, $model) => $model->ipv6_origin)->readonly(),
            field('contextName', fn ($value, $model) => $model->context_name)->readonly(),
        ];
    }

    /**
     * IPv6 addresses are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * IPv6 addresses are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
