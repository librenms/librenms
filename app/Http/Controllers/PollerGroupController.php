<?php

namespace App\Http\Controllers;

use App\Models\PollerGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class PollerGroupController extends Controller
{
    /**
     * Remove the specified poller group from storage.
     *
     * @param  PollerGroup  $pollergroup
     * @return JsonResponse
     */
    public function destroy(PollerGroup $pollergroup): JsonResponse
    {
        if (Gate::allows('delete', $pollergroup)) {
            $pollergroup->delete();

            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'failure']);
    }

    /**
     * Display the specified poller group.
     *
     * @param  PollerGroup  $pollergroup
     * @return JsonResponse
     */
    public function show(PollerGroup $pollergroup): JsonResponse
    {
        Gate::authorize('view', $pollergroup);

        return response()->json([
            'group_name' => $pollergroup->group_name,
            'descr' => $pollergroup->descr,
        ]);
    }

    public function store(Request $request): Response
    {
        Gate::authorize('create', PollerGroup::class);

        $request->validate([
            'group_name' => 'required|string|max:255',
            'descr' => 'string|max:255',
        ]);

        $pollergroup = PollerGroup::create([
            'group_name' => $request->input('group_name'),
            'descr' => $request->input('descr'),
        ]);

        if ($pollergroup) {
            return response('Added new poller group');
        }

        return response('ERROR: Failed to create new poller group', 500);
    }

    /**
     * Update the specified poller group in storage.
     *
     * @param  Request  $request
     * @param  PollerGroup  $pollergroup
     * @return Response
     */
    public function update(Request $request, PollerGroup $pollergroup): Response
    {
        Gate::authorize('poller-group.update');

        $request->validate([
            'group_name' => 'required|string|max:255',
            'descr' => 'string|max:255',
        ]);

        if ($pollergroup->update([
            'group_name' => $request->input('group_name'),
            'descr' => $request->input('descr'),
        ])) {
            return response('Updated poller group');
        }

        return response('ERROR: Failed to update the poller group', 500);
    }
}
