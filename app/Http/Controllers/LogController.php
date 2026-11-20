<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Syslog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function eventlog(Request $request, ?Device $device = null): View
    {
        $this->authorizeLogPage(Eventlog::class, $device);

        $request->validate([
            ...($device ? ['port' => 'nullable|int'] : []),
            ...Eventlog::filterValidationRules(),
        ]);

        $filter = $request->array('filter');
        if ($device && $request->filled('port')) {
            // Only an explicit query parameter overrides the port shortcut.
            parse_str($request->getQueryString() ?? '', $query);
            if (! array_key_exists('filter', $query)) {
                $filter = ['port' => ['eq' => $request->integer('port')]];
            }
        }

        return view($device ? 'device.tabs.logs.eventlog' : 'logs.eventlog', [
            'device' => $device,
            'filter' => $filter,
            'filterFields' => Eventlog::filterFieldDefinitions($device),
        ]);
    }

    public function syslog(Request $request, ?Device $device = null): View
    {
        $this->authorizeLogPage(Syslog::class, $device);

        $request->validate([
            'from' => 'nullable|date_or_relative',
            'to' => 'nullable|date_or_relative',
            ...Syslog::filterValidationRules(),
        ]);

        return view($device ? 'device.tabs.logs.syslog' : 'logs.syslog', [
            'device' => $device,
            'filter' => $request->array('filter'),
            'filterFields' => Syslog::filterFieldDefinitions(withDevice: $device === null),
            'from' => $request->string('from')->value(),
            'to' => $request->string('to')->value(),
        ]);
    }

    public function confirmSyslogExpunge(): View
    {
        $this->authorize('syslog.delete');

        return view('logs.syslog-expunge');
    }

    public function expungeSyslog(): RedirectResponse
    {
        $this->authorize('syslog.delete');

        Syslog::truncate();

        return to_route('syslog')->with('syslogCleared', true);
    }

    public function graylog(Request $request, ?Device $device = null): View
    {
        $this->authorizeLogPage(Syslog::class, $device); // Graylog replaces syslog

        $request->validate([
            ...($device ? [] : ['device' => 'nullable|int']),
            'stream' => 'nullable|alpha_num',
            'range' => 'nullable|int',
            'loglevel' => 'nullable|int|min:0|max:7',
        ]);

        return view($device ? 'device.tabs.logs.graylog' : 'logs.graylog', [
            'device' => $device ?? $this->requestedDevice($request),
            'stream' => $request->string('stream')->value(),
            'range' => $request->integer('range'),
            'loglevel' => $request->filled('loglevel') ? $request->integer('loglevel') : null,
        ]);
    }

    private function authorizeLogPage(string $model, ?Device $device): void
    {
        if ($device) {
            $this->authorize('view', $device);
        }

        $this->authorize('viewAny', $model);
    }

    private function requestedDevice(Request $request): ?Device
    {
        return $request->filled('device')
            ? Device::hasAccess($request->user())->find($request->integer('device'))
            : null;
    }
}
