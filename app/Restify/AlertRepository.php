<?php

namespace App\Restify;

use App\Models\Alert;
use App\Restify\Actions\AcknowledgeAlertAction;
use App\Restify\Actions\UnmuteAlertAction;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class AlertRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Alert::class;

    public static string $title = 'id';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
            'rule' => BelongsTo::make('rule', AlertRuleRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [];
    }

    public static function matches(): array
    {
        return [
            'state' => MatchFilter::make()->setType('integer')->setColumn('state'),
            'note' => MatchFilter::make()->setType('text')->setColumn('note'),
            'isAlerted' => MatchFilter::make()->setType('bool')->setColumn('alerted'),
            'isOpen' => MatchFilter::make()->setType('bool')->setColumn('open'),
            'createdAt' => MatchFilter::make()->setType('datetime')->setColumn('timestamp'),
            'info' => MatchFilter::make()->setType('text')->setColumn('info'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'state' => SortableFilter::make()->setColumn('state'),
            'note' => SortableFilter::make()->setColumn('note'),
            'isAlerted' => SortableFilter::make()->setColumn('alerted'),
            'isOpen' => SortableFilter::make()->setColumn('open'),
            'createdAt' => SortableFilter::make()->setColumn('timestamp'),
            'info' => SortableFilter::make()->setColumn('info'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('state')->readonly(),
            field('note')->readonly(),
            field('isAlerted', fn ($value, $model) => $model->alerted)->readonly(),
            field('isOpen', fn ($value, $model) => $model->open)->readonly(),
            field('createdAt', fn ($value, $model) => $model->timestamp)->readonly(),
            field('info')->readonly(),
        ];
    }

    public function actions(RestifyRequest $request): array
    {
        // canSee gates BOTH visibility in GET /alerts/actions AND the ability to execute.
        // (Restify's HTTP path does not call authorizedToRun, only authorizedToSee.)
        // Per-alert device access is enforced inside each action's handle() because
        // Restify does not apply the repository's indexQuery to action requests.
        $canSee = fn (Request $req) => $req->user()?->can('update', Alert::class) ?? false;

        return [
            AcknowledgeAlertAction::make()->canSee($canSee),
            UnmuteAlertAction::make()->canSee($canSee),
        ];
    }

    /**
     * Alerts are created automatically by the alerting engine not manually via the API.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Alerts are managed by the alerting engine lifecycle use acknowledge/unmute actions to change state instead.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }

    /**
     * Block the standard write verbs at the route layer so Laravel returns 405 (with an Allow header)
     * instead of Restify's 403. Registered before the default CRUD routes by CustomRoutesBoot.
     */
    public static function routes(Router $router, array $attributes, $wrap = true)
    {
        $deny = fn () => abort(405, 'Method Not Allowed', ['Allow' => 'GET, HEAD']);

        $router->match(['put', 'patch'], '{alertId}', $deny)->where('alertId', '[0-9]+');
        $router->delete('{alertId}', $deny)->where('alertId', '[0-9]+');
        $router->post('/', $deny);
    }
}
