<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;

class PortSecuritySearchController extends Controller
{
    /**
     * Display the port security search page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('search.portsecurity', [
            'pagetitle' => __('Port Security'),
        ]);
    }
}
