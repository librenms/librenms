<?php
/**
 * WinRMSoftwareController.php
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

use Illuminate\Http\Request;

class WinRMSoftwareController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request, string $software_detail = null, string $software_filter = null)
    {
        switch($software_detail){
            case 'vend':
                $data = [
                    'software_id' => null,
                    'software_version' => null,
                    'software_vendor' => $software_filter,
                ];
                break;
            default:
                $data = [
                    'software_id' => $software_detail,
                    'software_version' => $software_filter,
                    'software_vendor' => null,
                ];
                break;
        }
        return view('winrm.software', $data);
    }
}
