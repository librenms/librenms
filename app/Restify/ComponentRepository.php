<?php

namespace App\Restify;

use App\Models\Component;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class ComponentRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Component::class;

    public static string $title = 'label';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'label' => SearchableFilter::make()->setColumn('label'),
        ];
    }

    public static function matches(): array
    {
        return [
            'category' => MatchFilter::make()->setType('text')->setColumn('type'),
            'label' => MatchFilter::make()->setType('text')->setColumn('label'),
            'status' => MatchFilter::make()->setType('text')->setColumn('status'),
            'isEnabled' => MatchFilter::make()->setType('bool')->setColumn('disabled'),
            'isIgnored' => MatchFilter::make()->setType('bool')->setColumn('ignore'),
            'error' => MatchFilter::make()->setType('text')->setColumn('error'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'category' => SortableFilter::make()->setColumn('type'),
            'label' => SortableFilter::make()->setColumn('label'),
            'status' => SortableFilter::make()->setColumn('status'),
            'isEnabled' => SortableFilter::make()->setColumn('disabled'),
            'isIgnored' => SortableFilter::make()->setColumn('ignore'),
            'error' => SortableFilter::make()->setColumn('error'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('category', fn ($value, $model) => $model->type)->readonly(),
            field('label')->readonly(),
            field('status')->readonly(),
            field('isEnabled', fn ($value, $model) => ! $model->disabled)->readonly(),
            field('isIgnored', fn ($value, $model) => $model->ignore)->readonly(),
            field('error')->readonly(),
        ];
    }

    /**
     * Components are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Components are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
