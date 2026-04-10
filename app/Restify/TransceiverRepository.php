<?php

namespace App\Restify;

use App\Models\Transceiver;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class TransceiverRepository extends Repository
{
    public static string $model = Transceiver::class;

    public static string $title = 'model';

    public static array $search = [
        'vendor',
        'model',
        'serial',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('device_id')->readonly(),
            field('port_id')->readonly(),
            field('index')->readonly(),
            field('entity_physical_index')->readonly(),
            field('type')->readonly(),
            field('vendor')->readonly(),
            field('oui')->readonly(),
            field('model')->readonly(),
            field('revision')->readonly(),
            field('serial')->readonly(),
            field('date')->readonly(),
            field('ddm')->readonly(),
            field('encoding')->readonly(),
            field('cable')->readonly(),
            field('distance')->readonly(),
            field('wavelength')->readonly(),
            field('connector')->readonly(),
            field('channels')->readonly(),
        ];
    }

    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        if ($user = $request->user()) {
            return $query->hasAccess($user);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return static::indexQuery($request, $query);
    }

    /**
     * Transceivers are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Transceivers are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
