<?php

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Poller;
use App\Models\PollerCluster;
use App\Models\PollerClusterStat;
use App\Models\PollerGroup;
use App\View\SettingPresenter;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class PollerController extends Controller
{
    public $rrdstep;

    public function __construct()
    {
        $this->rrdstep = LibrenmsConfig::get('rrd.step');
    }

    public function logTab(Request $request)
    {
        $this->authorize('viewAny', PollerCluster::class);

        return view('poller.log', [
            'current_tab' => 'log',
            'filter' => $request->input('filter', 'active'),
        ]);
    }

    public function groupsTab()
    {
        $this->authorize('viewAny', PollerGroup::class);

        return view('poller.groups', [
            'current_tab' => 'groups',
            'poller_groups' => PollerGroup::query()->withCount('devices')->get(),
            'default_group_id' => LibrenmsConfig::get('default_poller_group'),
            'ungrouped_count' => Device::where('poller_group', 0)->count(),
        ]);
    }

    public function pollerTab()
    {
        $this->authorize('viewAny', PollerCluster::class);

        $pollerCluster = $this->pollerCluster();

        return view('poller.poller', [
            'current_tab' => 'poller',
            'pollers' => $this->poller(),
            'poller_cluster' => $pollerCluster,
            'cluster_groups' => $this->clusterGroups($pollerCluster),
            'cluster_summary' => $this->clusterSummary($pollerCluster),
        ]);
    }

    public function settingsTab()
    {
        $this->authorize('poller.update');
        $pollerClusters = PollerCluster::all()->keyBy('id');

        return view('poller.settings', [
            'current_tab' => 'settings',
            'settings' => $this->pollerSettings($pollerClusters),
            'poller_cluster' => $pollerClusters,
        ]);
    }

    public function performanceTab()
    {
        $this->authorize('viewAny', PollerCluster::class);

        return view('poller.performance', ['current_tab' => 'performance']);
    }

    protected function pollerStatus($poller, $last)
    {
        $since_last_poll = (int) Carbon::parse($last)->diffInSeconds(null, true);

        $poller->row_class = $this->checkTimeSinceLastPoll($since_last_poll);
        $poller->long_not_polled = $since_last_poll > ($this->rrdstep * 2);

        if ($poller instanceof PollerCluster && $poller->row_class === 'success' && $poller->primaryStat()?->isOverloaded()) {
            $poller->row_class = 'warning';
        }

        return $poller;
    }

    private function poller()
    {
        return Poller::query()->orderBy('poller_name')->get()->map(fn ($poller) => $this->pollerStatus($poller, $poller->last_polled));
    }

    private function pollerCluster()
    {
        return PollerCluster::with('stats')->orderBy('poller_name')->get()->map(fn ($poller) => $this->pollerStatus($poller, $poller->last_report));
    }

    /**
     * @return array<string, mixed>
     */
    private function clusterSummary(Collection $pollerCluster): array
    {
        $totalNodes = $pollerCluster->count();
        $healthyNodes = $pollerCluster->where('row_class', 'success')->count();
        $anyDeletable = $pollerCluster->contains(fn ($p): bool => (bool) $p->long_not_polled && Gate::allows('delete', $p));

        return [
            'total_nodes' => $totalNodes,
            'healthy_nodes' => $healthyNodes,
            'unhealthy_nodes' => $totalNodes - $healthyNodes,
            'any_deletable' => $anyDeletable,
            'total_cols' => $anyDeletable ? 11 : 10,
            'all_pollers_meta' => $this->pollersMeta($pollerCluster),
        ];
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function pollersMeta(Collection $pollers): Collection
    {
        return $pollers->map(fn ($p): array => [
            'name' => (string) $p->poller_name,
            'nodeId' => (string) $p->node_id,
            'rowClass' => (string) $p->row_class,
        ])->values();
    }

    private function clusterGroups(Collection $pollerCluster): Collection
    {
        $groups = PollerGroup::query()->withCount('devices')->orderBy('id')->get()->keyBy('id');
        $defaultGroup = (string) LibrenmsConfig::get('distributed_poller_group', '0');
        $grouped = [];

        foreach ($pollerCluster as $poller) {
            $rawGroups = ($poller->poller_groups === null || $poller->poller_groups === '')
                ? $defaultGroup
                : (string) $poller->poller_groups;

            $groupIds = array_values(array_unique(array_map(
                intval(...),
                array_filter(explode(',', $rawGroups), fn (string $v): bool => trim($v) !== '')
            )));

            if ($groupIds === []) {
                $groupIds = [0];
            }

            foreach ($groupIds as $gid) {
                $grouped[$gid][] = $poller;
            }
        }

        ksort($grouped);
        $ungroupedCount = isset($grouped[0]) ? Device::where('poller_group', 0)->count() : 0;

        $result = new Collection();
        foreach ($grouped as $gid => $groupPollers) {
            $groupModel = $groups->get($gid);
            $grpPollers = new Collection($groupPollers);
            $pollerStats = $grpPollers
                ->map(fn (PollerCluster $p): ?PollerClusterStat => $p->stats->firstWhere('poller_type', 'poller'))
                ->filter();
            $grpMax = $pollerStats->sum(fn (PollerClusterStat $s): int => $s->maxSeconds());
            $grpConsumed = $pollerStats->sum(fn (PollerClusterStat $s): float => (float) $s->worker_seconds);

            $result->push([
                'id' => (int) $gid,
                'group_name' => $gid === 0 ? __('General') : ($groupModel ? $groupModel->group_name : __('Group') . ' #' . $gid),
                'devices_count' => $gid === 0 ? $ungroupedCount : ($groupModel ? (int) $groupModel->devices_count : 0),
                'workers' => $pollerStats->sum('workers'),
                'load_pct' => $grpMax > 0 ? (int) round(($grpConsumed / $grpMax) * 100) : 0,
                'pollers_meta' => $this->pollersMeta($grpPollers),
                'pollers' => $grpPollers,
            ]);
        }

        return $result;
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

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function pollerSettings($pollers): Collection
    {
        $groups = PollerGroup::list();

        return $pollers->map(fn (PollerCluster $poller) => array_map(
            fn (array $setting) => SettingPresenter::present($setting, 'poller.settings'),
            $poller->configDefinition($groups)
        ));
    }

    /**
     * Remove the specified poller from storage.
     *
     * @param  Poller  $poller
     * @return JsonResponse
     */
    public function destroy(Poller $poller): JsonResponse
    {
        $this->authorize('delete', $poller);

        $id = $poller->id;
        $poller_name = e($poller->poller_name);

        if ($poller->delete()) {
            return response()->json([
                'status' => 0,
                'message' => "Poller: <i>$poller_name ($id), has been deleted.</i>",
            ]);
        }

        return response()->json([
            'status' => 1,
            'message' => "Poller: <i>$poller_name ($id), has NOT been deleted.</i>",
        ]);
    }

    /**
     * Remove the specified poller cluster node from storage.
     *
     * @param  PollerCluster  $pollerCluster
     * @return JsonResponse
     */
    public function destroyCluster(PollerCluster $pollerCluster): JsonResponse
    {
        $this->authorize('delete', $pollerCluster);

        $id = $pollerCluster->id;
        $poller_name = e($pollerCluster->poller_name);

        if ($pollerCluster->stats()->delete() !== false && $pollerCluster->delete()) {
            return response()->json([
                'status' => 0,
                'message' => "Poller: <i>$poller_name ($id), has been deleted.</i>",
            ]);
        }

        return response()->json([
            'status' => 1,
            'message' => "Poller: <i>$poller_name ($id), has NOT been deleted.</i>",
        ]);
    }
}
