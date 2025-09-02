<?php

/**
 * PageTabs.php
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\View\Components\Device;

use App\Models\Device;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Request;
use Illuminate\View\Component;
use LibreNMS\Interfaces\UI\DeviceTab;

class PageTabs extends Component
{
    public array $tabs = [];
    public readonly string $currentTab;
    public static array $tabsClasses = [
        'overview' => \App\Http\Controllers\Device\Tabs\OverviewController::class,
        'graphs' => \App\Http\Controllers\Device\Tabs\GraphsController::class,
        'health' => \App\Http\Controllers\Device\Tabs\HealthController::class,
        'apps' => \App\Http\Controllers\Device\Tabs\AppsController::class,
        'processes' => \App\Http\Controllers\Device\Tabs\ProcessesController::class,
        'collectd' => \App\Http\Controllers\Device\Tabs\CollectdController::class,
        'munin' => \App\Http\Controllers\Device\Tabs\MuninController::class,
        'ports' => \App\Http\Controllers\Device\Tabs\PortsController::class,
        'port' => \App\Http\Controllers\Device\Tabs\PortController::class,
        'slas' => \App\Http\Controllers\Device\Tabs\SlasController::class,
        'wireless' => \App\Http\Controllers\Device\Tabs\WirelessController::class,
        'accesspoints' => \App\Http\Controllers\Device\Tabs\AccessPointsController::class,
        'vlans' => \App\Http\Controllers\Device\Tabs\VlansController::class,
        'vm' => \App\Http\Controllers\Device\Tabs\VmInfoController::class,
        'mef' => \App\Http\Controllers\Device\Tabs\MefController::class,
        'tnmsne' => \App\Http\Controllers\Device\Tabs\TnmsneController::class,
        'loadbalancer' => \App\Http\Controllers\Device\Tabs\LoadBalancerController::class,
        'routing' => \App\Http\Controllers\Device\Tabs\RoutingController::class,
        'pseudowires' => \App\Http\Controllers\Device\Tabs\PseudowiresController::class,
        'neighbours' => \App\Http\Controllers\Device\Tabs\NeighboursController::class,
        'stp' => \App\Http\Controllers\Device\Tabs\StpController::class,
        'packages' => \App\Http\Controllers\Device\Tabs\PackagesController::class,
        'inventory' => \App\Http\Controllers\Device\Tabs\InventoryController::class,
        'services' => \App\Http\Controllers\Device\Tabs\ServicesController::class,
        'printer' => \App\Http\Controllers\Device\Tabs\PrinterController::class,
        'logs' => \App\Http\Controllers\Device\Tabs\LogsController::class,
        'alerts' => \App\Http\Controllers\Device\Tabs\AlertsController::class,
        'alert-stats' => \App\Http\Controllers\Device\Tabs\AlertStatsController::class,
        'showconfig' => \App\Http\Controllers\Device\Tabs\ShowConfigController::class,
        'netflow' => \App\Http\Controllers\Device\Tabs\NetflowController::class,
        'qos' => \App\Http\Controllers\Device\Tabs\QosController::class,
        'latency' => \App\Http\Controllers\Device\Tabs\LatencyController::class,
        'nac' => \App\Http\Controllers\Device\Tabs\NacController::class,
        'notes' => \App\Http\Controllers\Device\Tabs\NotesController::class,
        'edit' => \App\Http\Controllers\Device\Tabs\EditController::class,
        'capture' => \App\Http\Controllers\Device\Tabs\CaptureController::class,
    ];

    public function __construct(
        public readonly Device $device,
        public readonly array $dropdownLinks = [],
    ) {
        // remove tab= for legacy urls
        $this->currentTab = str_replace('tab=', '', Request::segment(3, 'overview'));

        foreach (self::$tabsClasses as $tab => $class) {
            $this->tabs[$tab] = app()->make($class);
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.device.page-tabs');
    }

    public function getCurrentTab(): DeviceTab
    {
        return $this->tabs[$this->currentTab];
    }

    public static function getTab(string $tab): DeviceTab
    {
        if (! isset(self::$tabsClasses[$tab])) {
            abort(404);
        }

        return app()->make(self::$tabsClasses[$tab]);
    }
}
