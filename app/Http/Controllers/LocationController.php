<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Location $location
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Location $location)
    {
        $this->authorize('admin', $request->user());

        $this->validate($request, [
            'lat' => 'required|numeric|max:90|min:-90',
            'lng' => 'required|numeric|max:180|min:-180',
        ]);

        $location->fill($request->only(['lat', 'lng']));
        $location->save();

        return response()->json(['status' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Location $location
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Request $request, Location $location)
    {
        $this->authorize('admin', $request->user());

        $location->delete();

        return response()->json(['status' => 'success']);
    }
}
