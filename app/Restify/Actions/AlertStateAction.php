<?php

namespace App\Restify\Actions;

use App\Models\Alert;
use Binaryk\LaravelRestify\Actions\Action;
use Binaryk\LaravelRestify\Http\Requests\ActionRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

abstract class AlertStateAction extends Action
{
    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string'],
        ];
    }

    public function handle(ActionRequest $request, Model|Collection $models): JsonResponse
    {
        $models = $models instanceof Model ? collect([$models]) : $models;
        $user = $request->user();

        // Restify does not apply the repository's indexQuery (DeviceScopedRepository)
        // to action requests, so filter per-alert via the view policy which enforces
        // device access.
        [$allowed, $denied] = $models->partition(fn (Alert $alert) => $user->can('view', $alert));

        $note = $request->input('note', '');
        $allowed->each(function (Alert $alert) use ($note, $user) {
            if ($note !== '') {
                $entry = now()->toDateTimeString() . ' - ' . $user->username . ': ' . $note;
                $alert->note = $alert->note ? $alert->note . "\n" . $entry : $entry;
            }
            $this->mutate($alert);
            $alert->save();
        });

        return response()->json([
            'message' => $this->successMessage(),
            'data' => $allowed->map(fn (Alert $a) => [
                'id' => $a->id,
                'state' => $a->state,
                'note' => $a->note,
            ])->values(),
            'skipped' => $denied->pluck('id')->values(),
        ]);
    }

    abstract protected function mutate(Alert $alert): void;

    abstract protected function successMessage(): string;
}
