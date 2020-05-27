<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Poller;
use App\Models\PollerCluster;
use App\Models\PollerGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use LibreNMS\Config;

class PollerController extends Controller
{
    public $rrdstep;

    public function __construct()
    {
        $this->rrdstep = Config::get('rrd.step');
    }

    public function logTab(Request $request)
    {
        return view('poller.log', [
            'current_tab' => 'log',
            'filter' => $request->input('filter', 'active')
        ]);
    }

    public function groupsTab()
    {
        return view('poller.groups', [
            'current_tab' => 'groups',
            'poller_groups' => PollerGroup::query()->withCount('devices')->get(),
            'default_group_id' => Config::get('default_poller_group'),
            'ungrouped_count' => Device::where('poller_group', 0)->count(),
        ]);
    }

    public function pollerTab()
    {
        return view('poller.poller', [
            'current_tab' => 'poller',
            'pollers' => $this->poller(),
            'poller_cluster' => $this->pollerCluster(),
        ]);
    }

    public function settingsTab()
    {
        $pollerClusters = PollerCluster::all()->keyBy('id');
        return view('poller.settings', [
            'current_tab' => 'settings',
            'settings' => $this->pollerSettings($pollerClusters),
            'poller_cluster' => $pollerClusters,
        ]);
    }

    public function performanceTab()
    {
        return view('poller.performance', ['current_tab' => 'performance']);
    }

    protected function pollerStatus($poller, $last)
    {
        $since_last_poll = Carbon::parse($last)->diffInSeconds();

        $poller->row_class = $this->checkTimeSinceLastPoll($since_last_poll);
        $poller->long_not_polled = (\Auth::user()->hasGlobalAdmin() && ($since_last_poll > ($this->rrdstep * 2)));

        return $poller;
    }

    private function poller()
    {
        return Poller::query()->orderBy('poller_name')->get()->map(function ($poller) {
            return $this->pollerStatus($poller, $poller->last_polled);
        });
    }

    private function pollerCluster()
    {
        return PollerCluster::with('stats')->orderBy('poller_name')->get()->map(function ($poller) {
            return $this->pollerStatus($poller, $poller->last_report);
        });
    }

    private function checkTimeSinceLastPoll($seconds)
    {
        if ($seconds >= $this->rrdstep) {
            return 'danger';
        } elseif ($seconds >= ($this->rrdstep * 0.95)) {
            return 'warning';
        }

        return 'success';
    }

    private function pollerSettings($pollers): Collection
    {
        $groups = PollerGroup::all()->pluck('group_name', 'id')->prepend(__('General'), 0);

        return $pollers->map(function ($poller) use ($groups) {
            return [
                [
                    'name' => 'poller_groups',
                    'default' => Config::get('distributed_poller_group'),
                    'value' => $poller->poller_group ?? Config::get('distributed_poller_group'),
                    'type' => 'multiple',
                    'options' => $groups,
                ],
                [
                    'name' => 'poller_enabled',
                    'default' => Config::get('service_poller_enabled'),
                    'value' => $poller->poller_enabled ?? Config::get('service_poller_enabled'),
                    'type' => 'boolean',
                ],
                [
                    'name' => 'poller_workers',
                    'default' => Config::get('service_poller_workers'),
                    'value' => $poller->poller_workers ?? Config::get('service_poller_workers'),
                    'type' => 'integer',
                    'units' => 'workers',
                ],
                [
                    'name' => 'poller_frequency',
                    'default' => Config::get('service_poller_frequency'),
                    'value' => $poller->poller_workers ?? Config::get('service_poller_frequency'),
                    'type' => 'integer',
                    'units' => 'seconds',
                    'advanced' => true,
                ],
                [
                    'name' => 'poller_down_retry',
                    'default' => Config::get('service_poller_down_retry'),
                    'value' => $poller->poller_down_retry ?? Config::get('service_poller_down_retry'),
                    'type' => 'integer',
                    'units' => 'seconds',
                ],
                [
                    'name' => 'discovery_enabled',
                    'default' => Config::get('service_discovery_enabled'),
                    'value' => $poller->discovery_enabled ?? Config::get('service_discovery_enabled'),
                    'type' => 'boolean',
                ],
                [
                    'name' => 'discovery_workers',
                    'default' => Config::get('service_discovery_workers'),
                    'value' => $poller->discovery_workers ?? Config::get('service_discovery_workers'),
                    'type' => 'integer',
                    'units' => 'workers',
                ],
                [
                    'name' => 'discovery_frequency',
                    'default' => Config::get('service_discovery_frequency'),
                    'value' => $poller->discovery_frequency ?? Config::get('service_discovery_frequency'),
                    'type' => 'integer',
                    'units' => 'seconds',
                    'advanced' => true,
                ],
                [
                    'name' => 'services_enabled',
                    'default' => Config::get('service_services_enabled'),
                    'value' => $poller->services_enabled ?? Config::get('service_services_enabled'),
                    'type' => 'boolean',
                ],
                [
                    'name' => 'services_workers',
                    'default' => Config::get('service_services_workers'),
                    'value' => $poller->services_workers ?? Config::get('service_services_workers'),
                    'type' => 'integer',
                    'units' => 'workers',
                ],
                [
                    'name' => 'services_frequency',
                    'default' => Config::get('service_services_frequency'),
                    'value' => $poller->services_frequency ?? Config::get('service_services_frequency'),
                    'type' => 'integer',
                    'units' => 'seconds',
                    'advanced' => true,
                ],
                [
                    'name' => 'billing_enabled',
                    'default' => Config::get('service_billing_enabled'),
                    'value' => $poller->billing_enabled ?? Config::get('service_billing_enabled'),
                    'type' => 'boolean',
                ],
                [
                    'name' => 'billing_frequency',
                    'default' => Config::get('service_billing_frequency'),
                    'value' => $poller->billing_frequency ?? Config::get('service_billing_frequency'),
                    'type' => 'integer',
                    'units' => 'seconds',
                    'advanced' => true,
                ],
                [
                    'name' => 'billing_calculate_frequency',
                    'default' => Config::get('service_billing_calculate_frequency'),
                    'value' => $poller->billing_calculate_frequency ?? Config::get('service_billing_calculate_frequency'),
                    'type' => 'integer',
                    'units' => 'seconds',
                    'advanced' => true,
                ],
                [
                    'name' => 'alerting_enabled',
                    'default' => Config::get('service_alerting_enabled'),
                    'value' => $poller->alerting_enabled ?? Config::get('service_alerting_enabled'),
                    'type' => 'boolean',
                ],
                [
                    'name' => 'alerting_frequency',
                    'default' => Config::get('service_alerting_frequency'),
                    'value' => $poller->alerting_frequency ?? Config::get('service_alerting_frequency'),
                    'type' => 'integer',
                    'units' => 'seconds',
                    'advanced' => true,
                ],
                [
                    'name' => 'ping_enabled',
                    'default' => Config::get('service_ping_enabled'),
                    'value' => $poller->ping_enabled ?? Config::get('service_ping_enabled'),
                    'type' => 'boolean',
                ],
                [
                    'name' => 'ping_frequency',
                    'default' => Config::get('ping_rrd_step'),
                    'value' => $poller->ping_frequency ?? Config::get('ping_rrd_step'),
                    'type' => 'integer',
                    'units' => 'seconds',
                    'advanced' => true,
                ],
                [
                    'name' => 'update_enabled',
                    'default' => Config::get('service_update_enabled'),
                    'value' => $poller->update_enabled ?? Config::get('service_update_enabled'),
                    'type' => 'boolean',
                ],
                [
                    'name' => 'update_frequency',
                    'default' => Config::get('service_update_frequency'),
                    'value' => $poller->update_frequency ?? Config::get('service_update_frequency'),
                    'type' => 'integer',
                    'units' => 'seconds',
                    'advanced' => true,
                ],
                [
                    'name' => 'loglevel',
                    'default' => Config::get('service_loglevel'),
                    'value' => $poller->loglevel ?? Config::get('service_loglevel'),
                    'type' => 'select',
                    'options' => [
                        'DEBUG' => 'DEBUG',
                        'INFO' => 'INFO',
                        'WARNING' => 'WARNING',
                        'ERROR' => 'ERROR',
                        'CRITICAL' => 'CRITICAL'
                    ],
                ],
                [
                    'name' => 'watchdog_enabled',
                    'default' => Config::get('service_watchdog_enabled'),
                    'value' => $poller->watchdog_enabled ?? Config::get('service_watchdog_enabled'),
                    'type' => 'boolean',
                ],
                [
                    'name' => 'watchdog_log',
                    'default' => Config::get('log_file'),
                    'value' => $poller->watchdog_log ?? Config::get('log_file'),
                    'type' => 'text',
                    'advanced' => true,
                ],
            ];
        });
    }
}
