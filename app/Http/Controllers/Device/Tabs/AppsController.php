<?php

/**
 * AppsController.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use LibreNMS\Interfaces\UI\DeviceTab;
use LibreNMS\Util\Html;

class AppsController extends Controller implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return Gate::allows('viewAny', Application::class) && $device->applications()->exists();
    }

    public function slug(): string
    {
        return 'apps';
    }

    public function icon(): string
    {
        return 'fa-cubes';
    }

    public function name(): string
    {
        return __('Apps');
    }

    public function data(Device $device, Request $request): array
    {
        return [];
    }

    public function index(Device $device, Request $request): View
    {
        $this->authorize('view', $device);
        $this->authorize('viewAny', Application::class);

        $selection = $request->validate([
            'app' => 'nullable|string',
            'instance' => 'nullable|integer',
        ]);

        $type = $selection['app'] ?? null;
        $instance = isset($selection['instance']) ? (int) $selection['instance'] : null;
        $applications = $device->applications()->orderBy('app_id')->get()
            ->sortBy('show_name', SORT_NATURAL | SORT_FLAG_CASE);
        $selected = $applications->first(fn (Application $application) => ($type === null || $application->app_type === $type)
            && ($instance === null || $application->app_id === $instance));
        abort_if($selected === null && ($type !== null || $instance !== null), 404);
        $selected?->setRelation('device', $device);

        $periods = array_keys(LibrenmsConfig::get(session('widescreen') ? 'graphs.mini.widescreen' : 'graphs.mini.normal', []));

        return view('device.tabs.apps', [
            'device' => $device,
            'appOptions' => $applications->mapWithKeys(fn (Application $application) => [$application->app_id => $this->appOption($application)])->all(),
            'selected' => $selected,
            'page' => $selected?->definition()->devicePage($selected, $request->query()),
            'periods' => array_map(fn (string $period) => [
                'from' => LibrenmsConfig::get("time.$period"),
                'to' => LibrenmsConfig::get('time.now'),
            ], $periods),
        ]);
    }

    /**
     * @return array{text: string, link: string, icon: ?string, title: string}
     */
    private function appOption(Application $application): array
    {
        $state = Html::appStateIcon($application->app_state);

        return [
            'text' => $application->displayName() . (filled($application->app_instance) ? " ($application->app_instance)" : ''),
            'link' => $application->deviceAppsUrl(),
            'icon' => $state['icon'] ? $state['icon'] . ' fa-fw' : null,
            'title' => __($state['hover_text']),
        ];
    }
}
