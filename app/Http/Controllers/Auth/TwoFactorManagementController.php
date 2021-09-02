<?php
/**
 * TwoFactorManagementController.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\TwoFactorManagementRequest;
use App\Models\User;
use App\Models\UserPref;

class TwoFactorManagementController extends Controller
{
    public function unlock(TwoFactorManagementRequest $request, User $user)
    {
        $twofactor = UserPref::getPref($user, 'twofactor');
        $twofactor['fails'] = 0;

        if (UserPref::setPref($user, 'twofactor', $twofactor)) {
            return response()->json(['msg' => __('Two-Factor unlocked.')]);
        }

        return response()->json(['error' => __('Failed to unlock Two-Factor.')]);
    }

    public function destroy(TwoFactorManagementRequest $request, User $user)
    {
        if (UserPref::forgetPref($user, 'twofactor')) {
            return response()->json(['msg' => __('Two-Factor removed.')]);
        }

        return response()->json(['error' => __('Failed to remove Two-Factor.')]);
    }
}
