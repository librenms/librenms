<?php

namespace App\Http\Controllers;

use App\Models\Port;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SnmpQuery;

class RealtimeDataController extends Controller
{
    public function __invoke(Request $request, Port $port): Response
    {
        abort_if(! $port->device, 404);

        $this->authorize('view', $port);

        [$in, $out] = $this->fetchCounters($port);

        return response(sprintf('%.6f|%s|%s', microtime(true), $in, $out), 200, [
            'Cache-Control' => 'no-store, max-age=0',
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    /**
     * @return array{string, string}
     */
    private function fetchCounters(Port $port): array
    {
        $ifIndex = $port->ifIndex;

        $response = SnmpQuery::device($port->device)->get([
            "IF-MIB::ifHCInOctets.$ifIndex",
            "IF-MIB::ifInOctets.$ifIndex",
            "IF-MIB::ifHCOutOctets.$ifIndex",
            "IF-MIB::ifOutOctets.$ifIndex",
        ]);

        if (! $response->isValid(true)) {
            return ['', ''];
        }

        $in = $response->value("IF-MIB::ifHCInOctets.$ifIndex");
        if ($in === '') {
            $in = $response->value("IF-MIB::ifInOctets.$ifIndex");
        }

        $out = $response->value("IF-MIB::ifHCOutOctets.$ifIndex");
        if ($out === '') {
            $out = $response->value("IF-MIB::ifOutOctets.$ifIndex");
        }

        return [$in, $out];
    }
}
