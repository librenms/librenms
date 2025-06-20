<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\Mac;

class OuiLookupController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): \Illuminate\Contracts\View\View
    {
        $results = [];
        $query = $request->get('query');

        if ($query) {
            $lines = preg_split('/\r\n|\n|\r/', $query, flags: PREG_SPLIT_NO_EMPTY);
            foreach ($lines as $line) {
                $mac = Mac::parsePartial($line);

                $results[] = [
                    'mac' => $mac->readable() ?: $line,
                    'vendor' => $mac->vendor(),
                ];
            }
        }

        return view('oui_lookup', [
            'query' => $query,
            'results' => $results,
            'db_populated' => DB::table('vendor_ouis')->exists(),
        ]);
    }
}
