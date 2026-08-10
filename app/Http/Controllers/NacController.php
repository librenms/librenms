<?php

namespace App\Http\Controllers;

use App\Models\PortsNac;

class NacController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', PortsNac::class);

        return view('nac');
    }
}
