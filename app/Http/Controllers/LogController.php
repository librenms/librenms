<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Syslog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function eventlog(Request $request): View
    {
        $this->authorize('viewAny', Eventlog::class);

        $request->validate([
            'device' => 'nullable|int',
            'eventtype' => 'nullable|string',
        ]);

        return view('logs.eventlog', [
            'device' => $this->device($request),
            'eventtype' => $request->string('eventtype')->value(),
        ]);
    }

    public function syslog(Request $request): View
    {
        $this->authorize('viewAny', Syslog::class);

        $request->validate([
            'from' => 'nullable|date_or_relative',
            'to' => 'nullable|date_or_relative',
            ...Syslog::filterValidationRules(),
        ]);

        return view('logs.syslog', [
            'filter' => $request->array('filter'),
            'filterFields' => Syslog::filterFieldDefinitions(),
            'from' => $request->string('from')->value(),
            'to' => $request->string('to')->value(),
        ]);
    }

    public function graylog(Request $request): View
    {
        $this->authorize('viewAny', Syslog::class); // Graylog replaces syslog

        $request->validate([
            'device' => 'nullable|int',
            'stream' => 'nullable|alpha_num',
            'range' => 'nullable|int',
            'loglevel' => 'nullable|int|min:0|max:7',
        ]);

        return view('logs.graylog', [
            'device' => $this->device($request),
            'stream' => $request->string('stream')->value(),
            'range' => $request->integer('range'),
            'loglevel' => $request->filled('loglevel') ? $request->integer('loglevel') : null,
        ]);
    }

    private function device(Request $request): ?Device
    {
        return $request->filled('device')
            ? Device::hasAccess($request->user())->find($request->integer('device'))
            : null;
    }
}
