<?php

namespace App\Http\Controllers;

use App\Models\Vlan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class VlansController extends Controller
{
    public function index(Request $request): View
    {
        return view('vlans.index', [
            'vlans' => Vlan::distinct()
                ->hasAccess($request->user())
                ->where('vlan_vlan', '>', 0)
                ->orderBy('vlan_vlan')
                ->get(['vlan_vlan', 'vlan_name']),
        ]);
    }
}
