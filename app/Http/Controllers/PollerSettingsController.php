<?php
/**
 * PollerSettingsController.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers;

use App\Models\PollerCluster;
use Illuminate\Http\Request;

class PollerSettingsController extends Controller
{
    public function update(Request $request, $id, $setting)
    {
        $poller = PollerCluster::findOrFail($id);
        $this->authorize('update', $poller);

        $definition = collect($poller->configDefinition())->keyBy('name');
        if (! $definition->has($setting)) {
            return response()->json(['error' => 'Invalid setting'], 422);
        }

        $poller->$setting = $request->get('value');
        $poller->save();

        return response()->json(['value' => $poller->$setting]);
    }

    public function destroy($id, $setting)
    {
        $poller = PollerCluster::findOrFail($id);
        $this->authorize('delete', $poller);

        $definition = collect($poller->configDefinition())->keyBy('name');
        if (! $definition->has($setting)) {
            return response()->json(['error' => 'Invalid setting'], 422);
        }

        $poller->$setting = $definition->get($setting)['default'];
        $poller->save();

        return response()->json(['value' => $poller->$setting]);
    }
}
