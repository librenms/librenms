<?php

namespace App\Http\Controllers;

use App\Actions\Port\FetchPortOctets;
use App\Models\Port;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RealtimeDataController extends Controller
{
    public function __invoke(Request $request, Port $port, FetchPortOctets $fetchOctets): Response
    {
        abort_if(! $port->device, 404);

        $this->authorize('view', $port);

        [$in, $out] = $fetchOctets->execute($port);

        return response(sprintf('%.6f|%s|%s', microtime(true), $in, $out), 200, [
            'Cache-Control' => 'no-store, max-age=0',
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
