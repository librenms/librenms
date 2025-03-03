<?php

namespace App\Http\Controllers;

use App\Models\PollerGroup;
use Illuminate\Http\Request;

class PollerGroupController extends Controller
{
    public function destroy(Request $request, PollerGroup $pollergroup)
    {
        if ($request->user()->isAdmin()) {
            $pollergroup->delete();

            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'failure']);
        }
    }
}
