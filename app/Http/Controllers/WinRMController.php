<?php
/**
 * WinRMController.php
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

use App\Models\WinRM;
use Illuminate\Http\Request;

class WinRMController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function applications()
    {
        // $data = '';
        return view('winrm.software');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WinRM  $winRM
     * @return \Illuminate\Http\Response
     */
    public function show(WinRM $winRM)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WinRM  $winRM
     * @return \Illuminate\Http\Response
     */
    public function edit(WinRM $winRM)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WinRM  $winRM
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WinRM $winRM)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WinRM  $winRM
     * @return \Illuminate\Http\Response
     */
    public function destroy(WinRM $winRM)
    {
        //
    }
}
