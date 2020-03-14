<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Poller;
use App\Models\PollerCluster;
use App\Models\PollerGroups;
use Illuminate\Http\Request;

class PollerController extends Controller
{
    public $rrdstep;
    public $defaultPollerId;

    public $defaultGroup = [
        'id' => 0,
        'group_name' => 'General',
        'descr' => ''
    ];
    public $defaultPollerMarker = '(default Poller)';

    public function __construct()
    {
        $this->authorizeResource(PollerGroups::class, 'poller_groups'); // FIXME is this correct? not a resource anymore
        $this->rrdstep = \LibreNMS\Config::get('rrd.step');
        $this->defaultPollerId = \LibreNMS\Config::get('distributed_poller_group');
    }

    public function logTab(Request $request)
    {
        return view('poller.log', [
            'current_tab' => 'log',
            'filter' => $request->input('filter', 'active')
        ]);
    }

    // output for poller groups
    public function groupsTab()
    {
        $group_list = PollerGroups::get();

        # default poller_group
        $defaultGroup = $this->defaultGroup;
        $defaultGroup['devices'] = Device::where('poller_group', $defaultGroup['id'])->get();
        $defaultGroup['is_default_poller'] = ($defaultGroup['id'] == $this->defaultPollerId) ? true : false;

        # poller_groups
        $poller_group_list = [];
        foreach ($group_list as $group) {
            $group['is_default_poller'] = ($group['id'] == $this->defaultPollerId) ? true : false;

            $poller_group_list[] = $group;
        }

        return view('poller.groups', [
            'current_tab' => 'groups',
            'default_poller_marker' => $this->defaultPollerMarker,
            'poller_groups' => $poller_group_list,
            'default_poller_group' => $defaultGroup,
        ]);
    }

    // data output for poller view
    public function pollerTab()
    {
        return view('poller.poller', [
            'current_tab' => 'poller',
            'pollers' => $this->poller(),
            'poller_cluster' => $this->pollerCluster(),
        ]);
    }

    public function performanceTab()
    {
        return view('poller.performance', ['current_tab' => 'performance']);
    }

    protected function pollerStatus($poller)
    {
        $old = $poller['now'] - strtotime($poller['last_polled']);

        if ($old >= $this->rrdstep) {
            $poller['row_class'] = 'danger';
        } elseif ($old >= ($this->rrdstep * 0.95)) {
            $poller['row_class'] = 'warning';
        } else {
            $poller['row_class'] = 'success';
        }

        $poller['long_not_polled'] = (\Auth::user()->hasGlobalAdmin() && ($old > ($this->rrdstep * 2))) ? true : false;

        return $poller;
    }

    private function poller()
    {
        $rows = Poller::orderBy('poller_name')->get();

        $time = time();

        $groups = [];

        foreach ($rows as $poller) {
            $poller['now'] = $time;

            $poller = $this->pollerStatus($poller);

            $groups[] = $poller;
        }

        return $groups;
    }

    private function pollerCluster()
    {
        $rows = PollerCluster::orderBy('poller_name')->get();

        $cluster = [];

        foreach ($rows as $poller) {
            $poller = $this->pollerStatus($poller);

            $cluster[] = $poller;
        }

        return $cluster;
    }
}
