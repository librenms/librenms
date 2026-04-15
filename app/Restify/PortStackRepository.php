<?php

namespace App\Restify;

use App\Models\PortStack;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class PortStackRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = PortStack::class;

    public static string $title = 'ifStackStatus';



    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [];
    }

    public static function matches(): array
    {
        return [
            'highInterfaceIndex' => MatchFilter::make()->setType('integer')->setColumn('high_ifIndex'),
            'lowInterfaceIndex' => MatchFilter::make()->setType('integer')->setColumn('low_ifIndex'),
            'stackStatus' => MatchFilter::make()->setType('text')->setColumn('ifStackStatus'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'highInterfaceIndex' => SortableFilter::make()->setColumn('high_ifIndex'),
            'lowInterfaceIndex' => SortableFilter::make()->setColumn('low_ifIndex'),
            'stackStatus' => SortableFilter::make()->setColumn('ifStackStatus'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('highInterfaceIndex', fn ($value, $model) => $model->high_ifIndex)->readonly(),
            field('lowInterfaceIndex', fn ($value, $model) => $model->low_ifIndex)->readonly(),
            field('stackStatus', fn ($value, $model) => $model->ifStackStatus)->readonly(),
        ];
    }

    /**
     * Port stacking relationships are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Port stacking relationships are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
