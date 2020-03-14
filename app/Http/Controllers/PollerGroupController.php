<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\PollerGroups;
use Illuminate\Http\Request;

class PollerGroupController extends Controller
{
    public function destroy(Request $request, PollerGroups $pollergroup)
    {
        $this->authorize('admin', $request->user());

        $pollergroup->delete();

        return response()->json(['status' => 'success']);
    }
}
