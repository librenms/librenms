<?php

namespace App\Restify;

use App\Models\Alert;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Enum\AlertState;

class AlertRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Alert::class;

    public static string $title = 'id';

    public static array $search = [];

    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
            'rule' => BelongsTo::make('rule', AlertRuleRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('device_id')->readonly(),
            field('rule_id')->readonly(),
            field('state')->rules('integer', 'in:0,1,2'),
            field('note')->rules('nullable', 'string'),
            field('alerted')->readonly(),
            field('open')->readonly(),
            field('timestamp')->readonly(),
            field('info')->readonly(),
        ];
    }

    /**
     * Alerts are created automatically by the alerting engine based on alert rules — not manually via the API.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Alerts are managed by the alerting engine lifecycle — use acknowledge/unmute to change state instead.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }

    public function acknowledge(Request $request, int $alertId)
    {
        $alert = Alert::query()->hasAccess($request->user())->findOrFail($alertId);

        Gate::authorize('update', $alert);

        $note = $request->input('note', '');
        $existingNote = $alert->note ?? '';
        if ($note) {
            $alert->note = $existingNote
                ? $existingNote . "\n" . now()->toDateTimeString() . ' - ' . $request->user()->username . ': ' . $note
                : now()->toDateTimeString() . ' - ' . $request->user()->username . ': ' . $note;
        }
        $alert->state = AlertState::ACKNOWLEDGED;
        $alert->open = 0;
        $alert->save();

        return response()->json([
            'message' => 'Alert acknowledged.',
            'data' => [
                'id' => $alert->id,
                'state' => $alert->state,
                'note' => $alert->note,
            ],
        ]);
    }

    public function unmute(Request $request, int $alertId)
    {
        $alert = Alert::query()->hasAccess($request->user())->findOrFail($alertId);

        Gate::authorize('update', $alert);

        $note = $request->input('note', '');
        $existingNote = $alert->note ?? '';
        if ($note) {
            $alert->note = $existingNote
                ? $existingNote . "\n" . now()->toDateTimeString() . ' - ' . $request->user()->username . ': ' . $note
                : now()->toDateTimeString() . ' - ' . $request->user()->username . ': ' . $note;
        }
        $alert->state = AlertState::ACTIVE;
        $alert->open = 1;
        $alert->save();

        return response()->json([
            'message' => 'Alert unmuted.',
            'data' => [
                'id' => $alert->id,
                'state' => $alert->state,
                'note' => $alert->note,
            ],
        ]);
    }
}
