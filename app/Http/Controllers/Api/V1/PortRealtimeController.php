<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Port\FetchPortOctets;
use App\Http\Controllers\Controller;
use App\Models\Port;
use Illuminate\Http\JsonResponse;

/**
 * A port's octet counters read from the device now, the same values the realtime graph uses.
 */
class PortRealtimeController extends Controller
{
    public function __invoke(string $portId, FetchPortOctets $fetchOctets): JsonResponse
    {
        $port = Port::with('device')->find((int) $portId);
        abort_if($port?->device === null, 404);

        $this->authorize('view', $port);

        [$in, $out] = $fetchOctets->execute($port);

        return response()->json(['data' => [
            'type' => 'port-realtime',
            'id' => (string) $port->port_id,
            'attributes' => [
                'timestamp' => round(microtime(true), 6),
                'in_octets' => is_numeric($in) ? $in : null,
                'out_octets' => is_numeric($out) ? $out : null,
            ],
        ]])->header('Cache-Control', 'no-store, max-age=0');
    }
}
