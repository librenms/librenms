<?php

namespace App\Restify;

use App\Models\CefSwitching;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class CefSwitchingRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = CefSwitching::class;

    public static string $id = 'cef_switching_id';

    public static string $title = 'cef_path';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'path' => SearchableFilter::make()->setColumn('cef_path'),
        ];
    }

    public static function matches(): array
    {
        return [
            'entityPhysicalIndex' => MatchFilter::make()->setType('integer')->setColumn('entPhysicalIndex'),
            'afi' => MatchFilter::make()->setType('text')->setColumn('afi'),
            'index' => MatchFilter::make()->setType('integer')->setColumn('cef_index'),
            'path' => MatchFilter::make()->setType('text')->setColumn('cef_path'),
            'drops' => MatchFilter::make()->setType('integer')->setColumn('drop'),
            'punts' => MatchFilter::make()->setType('integer')->setColumn('punt'),
            'puntToHost' => MatchFilter::make()->setType('integer')->setColumn('punt2host'),
            'updatedAt' => MatchFilter::make()->setType('datetime')->setColumn('updated'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'entityPhysicalIndex' => SortableFilter::make()->setColumn('entPhysicalIndex'),
            'afi' => SortableFilter::make()->setColumn('afi'),
            'index' => SortableFilter::make()->setColumn('cef_index'),
            'path' => SortableFilter::make()->setColumn('cef_path'),
            'drops' => SortableFilter::make()->setColumn('drop'),
            'punts' => SortableFilter::make()->setColumn('punt'),
            'puntToHost' => SortableFilter::make()->setColumn('punt2host'),
            'updatedAt' => SortableFilter::make()->setColumn('updated'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('entityPhysicalIndex', fn ($value, $model) => $model->entPhysicalIndex)->readonly(),
            field('afi')->readonly(),
            field('index', fn ($value, $model) => $model->cef_index)->readonly(),
            field('path', fn ($value, $model) => $model->cef_path)->readonly(),
            field('drops', fn ($value, $model) => $model->drop)->readonly(),
            field('punts', fn ($value, $model) => $model->punt)->readonly(),
            field('puntToHost', fn ($value, $model) => $model->punt2host)->readonly(),
            field('updatedAt', fn ($value, $model) => $model->updated)->readonly(),
        ];
    }

    /**
     * CEF switching entries are discovered automatically by LibreNMS during the polling process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * CEF switching entries are managed by the LibreNMS polling process.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
