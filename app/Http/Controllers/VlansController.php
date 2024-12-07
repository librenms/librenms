<?php

namespace App\Http\Controllers;

use App\Models\Vlan;
use Illuminate\Contracts\View\View;

class VlansController extends Controller
{
    public function index(): View
    {
        return view('vlans.index', [
            'vlanIds' => Vlan::distinct()
                ->where('vlan_vlan', '>', 0)
                ->orderBy('vlan_vlan')
                ->pluck('vlan_vlan'),
        ]);
    }
}
