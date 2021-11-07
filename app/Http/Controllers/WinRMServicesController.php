<?php
/**
 * WinRMServicesController.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2021 Thomas Ford
 * @author     Thomas Ford <tford@thomasaford.com>
 */

namespace App\Http\Controllers;

use App\Models\WinRMServices;
use Illuminate\Http\Request;

class WinRMServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request, string $service_name = null)
    {
        $data = [
            'service_name' => $service_name ? $service_name : '',
        ];

        return view('winrm.services', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  WinRMServices  $id
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, string $id) // Why is update(Request $request, WinRMServices $service)  not working?
    {
        $service = WinRMServices::find($id);
        // $this->authorize('admin', $request->user());

        if ($request->has('alerts')) {
            $this->validate($request, [
                'alerts' => 'boolean',
            ]);

            $service->fill($request->only(['alerts']));
            $service->save();
        } else {
            return response()->json(['status' => 'invalid']);
        }

        return response()->json(['status' => 'success']);
    }
}
