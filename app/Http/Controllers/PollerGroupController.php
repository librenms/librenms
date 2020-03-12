<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Poller;
use App\Models\PollerGroups;
use App\Models\PollerCluster;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use LibreNMS\Alerting\QueryBuilderFilter;
use LibreNMS\Alerting\QueryBuilderFluentParser;
use LibreNMS\Config;
use Toastr;

class PollerGroupController extends Controller
{
    public $rrdstep;
    public $is_distributed_poller;
    public $default_poller_id;

    public $default_poller_tab = 'poller';

    public $default_group = ['id' => 0,
                             'group_name' => 'General',
                             'descr' => ''];
    public $default_poller_marker = '(default Poller)';

    public function __construct()
    {
        $this->authorizeResource(PollerGroup::class, 'poller_groups');

        $this->rrdstep = \LibreNMS\Config::get('rrd.step', 300);

        $this->is_distributed_poller = \LibreNMS\Config::get('distributed_poller');
        $this->default_poller_id = \LibreNMS\Config::get('distributed_poller_group');
    }

    public function index(Request $request)
    {
        $current_tab = $request['tab'] ?: $this->default_poller_tab;

        switch ($current_tab) {
            case 'poller':
                $data = $this->pollerTab();
                break;
            case 'groups':
                $data = $this->groupsTab();
                break;
            case 'performance':
                $data = [];
                break;
            case 'log':
                $data = $this->logTab($request);
                break;
            default:
                $current_tab = $this->default_poller_tab;
                $data = $this->pollerTab();
                break;
        }

        $navbar_tab_data = [
            'navbar' => $this->navbar(),
            'current_tab' => $current_tab,
        ];

        return view('poller-group.'.$current_tab, array_merge($data, $navbar_tab_data));
    }

    public function create()
    {
        return redirect()->route('poller-groups.index');
    }

    public function store(Request $request)
    {
        return redirect()->route('poller-groups.index');
    }

    public function show(PollerGroup $pollerGroup)
    {
        return redirect()->route('poller-groups.index');
    }

    public function edit(PollerGroup $pollerGroup)
    {
        return redirect()->route('poller-groups.index');
    }

    public function update(Request $request, PollerGroup $pollerGroup)
    {
        return redirect()->route('poller-groups.index');
    }

    public function destroy(PollerGroup $pollerGroup)
    {
        return redirect()->route('poller-groups.index');
    }

    public function navbar()
    {
        $_tabs = [];
        $_tabs[] = [
            'name' => 'Poller',
            'icon' => 'fa-th-large',
        ];

        if ($this->is_distributed_poller) {
            $_tabs[] = [
                'name' => 'Groups',
                'icon' => 'fa-th',
            ];
        }

        $_tabs[] = [
            'name' => 'Performance',
            'icon' => 'fa-line-chart',
        ];

        $_tabs[] = [
            'name' => 'Log',
            'icon' => 'fa-file-text',
        ];

        // inject taburl string out of name
        $tabs = [];
        foreach ($_tabs as $t) {
            $t['taburl'] = strtolower($t['name']);
            $tabs[] = $t;
        }
        return $tabs;
    }

    public function logTab($request)
    {
        return ['filter' => $request['filter'] ?: 'active'];
    }

    // output for poller groups
    public function groupsTab()
    {
        $group_list = PollerGroups::get();

        # default poller_group
        $default_group = $this->default_group;
        $default_group['devices'] = Device::where('poller_group', $default_group['id'])->get();
        $default_group['is_default_poller'] = ($default_group['id'] == $this->default_poller_id) ? true : false;

        # poller_groups
        $poller_group_list = [];
        foreach ($group_list as $group) {
            $group['is_default_poller'] = ($group['id'] == $this->default_poller_id) ? true : false;

            $poller_group_list[] = $group;
        }

        return ['default_poller_marker' => $this->default_poller_marker,
                'poller_groups' => $poller_group_list,
                'default_poller_group' => $default_group];
    }

    // data output for poller view
    public function pollerTab()
    {
        return ['pollers' => $this->poller(),
                'poller_cluster' => $this->pollerCluster()];
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
