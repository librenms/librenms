<?php

namespace App\Http\Controllers;

use App\Models\Vminfo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VminfoController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Vminfo::class);

        $request->validate(Vminfo::filterValidationRules());

        return view('vminfo', [
            'filterFields' => Vminfo::filterFieldDefinitions(),
            'filter' => $request->array('filter'),
        ]);
    }
}
