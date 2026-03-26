<?php

namespace App\Http\Controllers;

use App\Models\PollerGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class PollerGroupController
{
    public function destroy(PollerGroup $pollergroup): JsonResponse
    {
        if (Gate::allows('delete', $pollergroup)) {
            $pollergroup->delete();

            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'failure']);
    }
}
