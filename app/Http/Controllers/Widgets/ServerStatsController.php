<?php
/**
 * ServerStatsController.php
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

namespace App\Http\Controllers\Widgets;

use App\Models\Device;
use Illuminate\Http\Request;

class ServerStatsController extends WidgetController
{
    protected $title = 'Server Stats';
    protected $defaults = [
        'title' => null,
        'columnsize' => 3,
        'device' => null,
        'cpu' => 0,
        'mempools' => [],
        'disks' => [],
    ];

    public function title(Request $request)
    {
        $settings = $this->getSettings();
        if ($settings['title']) {
            return $settings['title'];
        }

        $device = Device::hasAccess($request->user())->find($settings['device']);
        if ($device) {
            return $device->displayName() . ' Stats';
        }

        return $this->title;
    }

    public function getView(Request $request)
    {
        $data = $this->getSettings();

        if (is_null($data['device'])) {
            return $this->getSettingsView($request);
        }

        $device = Device::hasAccess($request->user())->find($data['device']);
        if ($device) {
            $data['cpu'] = $device->processors()->avg('processor_usage');
            $data['mempools'] = $device->mempools()->select(\DB::raw('mempool_descr, ROUND(mempool_used / (1024*1024), 0) as used, ROUND(mempool_total /(1024*1024), 0) as total'))->get();
            $data['disks'] = $device->storage()->select(\DB::raw('storage_descr, ROUND(storage_used / (1024*1024), 0) as used, ROUND(storage_size / (1024*1024), 0) as total'))->get();
        }

        return view('widgets.server-stats', $data);
    }

    public function getSettingsView(Request $request)
    {
        $settings = $this->getSettings(true);
        $settings['device'] = Device::hasAccess($request->user())->find($settings['device']) ?: null;

        return view('widgets.settings.server-stats', $settings);
    }

    public function getSettings($settingsView = false)
    {
        $settings = parent::getSettings($settingsView);
        $settings['columns'] = 12 / $settings['columnsize'];

        return $settings;
    }
}
