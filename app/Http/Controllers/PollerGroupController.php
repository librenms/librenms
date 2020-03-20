<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\PollerGroups;
use Illuminate\Http\Request;

class PollerGroupController extends Controller
{
    public function destroy(Request $request, PollerGroups $pollergroup)
    {
        if ($request->user()->isAdmin()) {
            $pollergroup->delete();
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'failure']);
        }
    }
}
