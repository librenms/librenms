<?php

namespace App\Restify;

use App\Models\Transceiver;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class TransceiverRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = Transceiver::class;

    public static string $title = 'model';




    public static function searchables(): array
    {
        return [
            'vendor' => SearchableFilter::make()->setColumn('vendor'),
            'model' => SearchableFilter::make()->setColumn('model'),
            'serial' => SearchableFilter::make()->setColumn('serial'),
        ];
    }

    public static function matches(): array
    {
        return [
            'index' => MatchFilter::make()->setType('integer')->setColumn('index'),
            'entityPhysicalIndex' => MatchFilter::make()->setType('integer')->setColumn('entity_physical_index'),
            'category' => MatchFilter::make()->setType('text')->setColumn('type'),
            'vendor' => MatchFilter::make()->setType('text')->setColumn('vendor'),
            'oui' => MatchFilter::make()->setType('text')->setColumn('oui'),
            'model' => MatchFilter::make()->setType('text')->setColumn('model'),
            'revision' => MatchFilter::make()->setType('text')->setColumn('revision'),
            'serial' => MatchFilter::make()->setType('text')->setColumn('serial'),
            'manufacturedAt' => MatchFilter::make()->setType('datetime')->setColumn('date'),
            'hasDdm' => MatchFilter::make()->setType('bool')->setColumn('ddm'),
            'encoding' => MatchFilter::make()->setType('text')->setColumn('encoding'),
            'cable' => MatchFilter::make()->setType('text')->setColumn('cable'),
            'distance' => MatchFilter::make()->setType('integer')->setColumn('distance'),
            'wavelength' => MatchFilter::make()->setType('integer')->setColumn('wavelength'),
            'connector' => MatchFilter::make()->setType('text')->setColumn('connector'),
            'channels' => MatchFilter::make()->setType('integer')->setColumn('channels'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'index' => SortableFilter::make()->setColumn('index'),
            'entityPhysicalIndex' => SortableFilter::make()->setColumn('entity_physical_index'),
            'category' => SortableFilter::make()->setColumn('type'),
            'vendor' => SortableFilter::make()->setColumn('vendor'),
            'oui' => SortableFilter::make()->setColumn('oui'),
            'model' => SortableFilter::make()->setColumn('model'),
            'revision' => SortableFilter::make()->setColumn('revision'),
            'serial' => SortableFilter::make()->setColumn('serial'),
            'manufacturedAt' => SortableFilter::make()->setColumn('date'),
            'hasDdm' => SortableFilter::make()->setColumn('ddm'),
            'encoding' => SortableFilter::make()->setColumn('encoding'),
            'cable' => SortableFilter::make()->setColumn('cable'),
            'distance' => SortableFilter::make()->setColumn('distance'),
            'wavelength' => SortableFilter::make()->setColumn('wavelength'),
            'connector' => SortableFilter::make()->setColumn('connector'),
            'channels' => SortableFilter::make()->setColumn('channels'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('index')->readonly(),
            field('entityPhysicalIndex', fn ($value, $model) => $model->entity_physical_index)->readonly(),
            field('category', fn ($value, $model) => $model->type)->readonly(),
            field('vendor')->readonly(),
            field('oui')->readonly(),
            field('model')->readonly(),
            field('revision')->readonly(),
            field('serial')->readonly(),
            field('manufacturedAt', fn ($value, $model) => $model->date)->readonly(),
            field('hasDdm', fn ($value, $model) => $model->ddm)->readonly(),
            field('encoding')->readonly(),
            field('cable')->readonly(),
            field('distance')->readonly(),
            field('wavelength')->readonly(),
            field('connector')->readonly(),
            field('channels')->readonly(),
        ];
    }

    /**
     * Transceivers are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Transceivers are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
