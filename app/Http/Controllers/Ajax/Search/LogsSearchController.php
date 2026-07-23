<?php

namespace App\Http\Controllers\Ajax\Search;

use App\Models\Eventlog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\Url;

class LogsSearchController extends GroupedSearchController
{
    protected function groups(string $search, string $like, int $limit, ?User $user): array
    {
        $eventlog = Eventlog::hasAccess($user)->with('device')
            ->where(fn (Builder $q) => $q->where('message', 'like', $like)
                ->orWhere('type', 'like', $like)
                ->orWhere('username', 'like', $like))
            ->orderBy('event_id', 'desc')->limit($limit)->get()
            ->map(fn (Eventlog $e) => [
                'name' => $e->message,
                'subtitle' => trim($e->device?->display . ' ' . $e->datetime),
                'icon' => 'fa fa-bookmark',
                'status' => match ($e->severity) {
                    Severity::Ok => 'tw:border-l-green-600!',
                    Severity::Info, Severity::Notice => 'tw:border-l-blue-500!',
                    Severity::Warning => 'tw:border-l-amber-500!',
                    Severity::Error => 'tw:border-l-red-600!',
                    default => 'tw:border-l-gray-400!',
                },
                'url' => Url::deviceUrl($e->device_id, ['tab' => 'logs']),
            ]);

        return [$eventlog->isEmpty() ? null : ['type' => 'eventlog', 'label' => __('Eventlog'), 'results' => $eventlog]];
    }
}
