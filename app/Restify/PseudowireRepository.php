<?php

namespace App\Restify;

use App\Models\Pseudowire;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class PseudowireRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = Pseudowire::class;

    public static string $id = 'pseudowire_id';

    public static string $title = 'pw_descr';




    public static function searchables(): array
    {
        return [
            'description' => SearchableFilter::make()->setColumn('pw_descr'),
        ];
    }

    public static function matches(): array
    {
        return [
            'peerLdpId' => MatchFilter::make()->setType('text')->setColumn('peer_ldp_id'),
            'virtualCircuitId' => MatchFilter::make()->setType('integer')->setColumn('cpwVcID'),
            'oid' => MatchFilter::make()->setType('text')->setColumn('cpwOid'),
            'category' => MatchFilter::make()->setType('text')->setColumn('pw_type'),
            'psnCategory' => MatchFilter::make()->setType('text')->setColumn('pw_psntype'),
            'localMtu' => MatchFilter::make()->setType('integer')->setColumn('pw_local_mtu'),
            'peerMtu' => MatchFilter::make()->setType('integer')->setColumn('pw_peer_mtu'),
            'description' => MatchFilter::make()->setType('text')->setColumn('pw_descr'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'peerLdpId' => SortableFilter::make()->setColumn('peer_ldp_id'),
            'virtualCircuitId' => SortableFilter::make()->setColumn('cpwVcID'),
            'oid' => SortableFilter::make()->setColumn('cpwOid'),
            'category' => SortableFilter::make()->setColumn('pw_type'),
            'psnCategory' => SortableFilter::make()->setColumn('pw_psntype'),
            'localMtu' => SortableFilter::make()->setColumn('pw_local_mtu'),
            'peerMtu' => SortableFilter::make()->setColumn('pw_peer_mtu'),
            'description' => SortableFilter::make()->setColumn('pw_descr'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('peerLdpId', fn ($value, $model) => $model->peer_ldp_id)->readonly(),
            field('virtualCircuitId', fn ($value, $model) => $model->cpwVcID)->readonly(),
            field('oid', fn ($value, $model) => $model->cpwOid)->readonly(),
            field('category', fn ($value, $model) => $model->pw_type)->readonly(),
            field('psnCategory', fn ($value, $model) => $model->pw_psntype)->readonly(),
            field('localMtu', fn ($value, $model) => $model->pw_local_mtu)->readonly(),
            field('peerMtu', fn ($value, $model) => $model->pw_peer_mtu)->readonly(),
            field('description', fn ($value, $model) => $model->pw_descr)->readonly(),
        ];
    }

    /**
     * Pseudowires are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Pseudowires are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
