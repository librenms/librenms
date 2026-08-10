<?php

namespace App\Http\Controllers;

use App\Models\Port;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RealtimeGraphController extends Controller
{
    private const DEFAULT_INTERVAL = 1.0;

    public function __invoke(Request $request, Port $port): Response
    {
        $request->validate([
            'interval' => ['nullable', 'numeric', 'min:0.1'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        abort_if(! $port->device, 404);

        $this->authorize('view', $port);

        $interval = (float) ($request->input('interval') ?? self::DEFAULT_INTERVAL);
        $title = $request->input('title') ?: $port->getLabel();
        $nbPlot = 240;

        return response()
            ->view('graphs.realtime', [
                'width' => 300,
                'height' => 125,
                'nbPlot' => $nbPlot,
                'scaleType' => 'follow',
                'timeInterval' => $interval,
                'graphDuration' => $interval * $nbPlot,
                'fetchLink' => route('realtime.data', ['port' => $port->port_id]),
                'graphTitle' => $title,
                'deviceName' => $port->device->displayName(),
                'errorText' => "Cannot get data about interface $port->ifIndex",
            ])
            ->header('Content-Type', 'image/svg+xml; charset=UTF-8');
    }
}
