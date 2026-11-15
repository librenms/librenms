<?php

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Poller;
use App\Models\PollerCluster;
use App\Models\PollerClusterStat;
use App\Models\PollerGroup;
use App\Queue\QueueStatus;
use App\Queue\WorkerNode;
use App\View\SettingPresenter;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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

        $rowClass = $this->checkTimeSinceLastPoll($since_last_poll);
        $poller->row_class = $rowClass;
        $poller->long_not_polled = $since_last_poll > ($this->rrdstep * 2);

        if ($poller instanceof PollerCluster) {
            $primaryStat = $poller->stats->firstWhere('poller_type', 'poller') ?? $poller->stats->first();
            $otherStats = $primaryStat
                ? $poller->stats->filter(fn (PollerClusterStat $s): bool => $s->id !== $primaryStat->id)->values()
                : new Collection();

            $poller->stats->each(function (PollerClusterStat $stat): void {
                $statMax = (int) $stat->frequency * (int) $stat->workers;
                $statPct = $statMax > 0 ? (int) round(((float) $stat->worker_seconds / $statMax) * 100) : 0;

                $stat->setAttribute('max_seconds', $statMax);
                $stat->setAttribute('util_pct', $statPct);
                $stat->setAttribute('bar_class', $statPct > 100 ? 'progress-bar-warning' : 'progress-bar-success');
                $stat->setAttribute('progress_width', min(100, max(1, $statPct)));
            });

            $maxSeconds = $primaryStat ? ((int) $primaryStat->frequency * (int) $primaryStat->workers) : 0;
            $consumedSeconds = $primaryStat ? (float) $primaryStat->worker_seconds : 0.0;
            $utilPct = $maxSeconds > 0 ? (int) round(($consumedSeconds / $maxSeconds) * 100) : 0;

            if ($rowClass === 'success' && $maxSeconds > 0 && $consumedSeconds > $maxSeconds) {
                $rowClass = 'warning';
                $poller->setAttribute('row_class', $rowClass);
            }

            $isDown = $rowClass === 'danger';
            $isWarning = ! $isDown && ($rowClass === 'warning' || $utilPct > 100);

            $poller->setAttribute('primary_stat', $primaryStat);
            $poller->setAttribute('other_stats', $otherStats);
            $poller->setAttribute('max_seconds', $maxSeconds);
            $poller->setAttribute('util_pct', $utilPct);
            $poller->setAttribute('is_down', $isDown);
            $poller->setAttribute('is_warning', $isWarning);
            $poller->setAttribute('dot_class', $isDown ? 'text-danger' : ($isWarning ? 'text-warning' : 'text-success'));
            $poller->setAttribute('bar_class', $isDown ? 'progress-bar-danger' : ($isWarning ? 'progress-bar-warning' : 'progress-bar-success'));
            $poller->setAttribute('progress_width', min(100, max(2, $utilPct)));
            $poller->setAttribute('show_queue_link', $poller->poller_version == WorkerNode::VERSION && QueueStatus::showPages());
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
        $healthyNodes = $pollerCluster->where('row_class', 'success')->count();
        $warningNodes = $pollerCluster->where('row_class', 'warning')->count();
        $dangerNodes = $pollerCluster->where('row_class', 'danger')->count();
        $anyDeletable = $pollerCluster->contains(fn ($p): bool => (bool) $p->long_not_polled);

        return [
            'total_nodes' => $pollerCluster->count(),
            'healthy_nodes' => $healthyNodes,
            'unhealthy_nodes' => $warningNodes + $dangerNodes,
            'any_deletable' => $anyDeletable,
            'total_cols' => $anyDeletable ? 11 : 10,
            'all_pollers_meta' => $pollerCluster->map(fn ($p): array => [
                'name' => (string) $p->poller_name,
                'nodeId' => (string) $p->node_id,
                'rowClass' => (string) $p->row_class,
            ])->values(),
        ];
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
            $grpWorkers = $grpPollers->sum(fn ($p): int => (int) ($p->stats->firstWhere('poller_type', 'poller')?->workers ?? 0));
            $grpConsumed = $grpPollers->sum(fn ($p): float => (float) ($p->stats->firstWhere('poller_type', 'poller')?->worker_seconds ?? 0));
            $grpMax = $grpPollers->sum(function ($p): int {
                $s = $p->stats->firstWhere('poller_type', 'poller');

                return $s ? ((int) $s->frequency * (int) $s->workers) : 0;
            });
            $grpLoadPct = $grpMax > 0 ? (int) round(($grpConsumed / $grpMax) * 100) : 0;

            $result->push([
                'id' => (int) $gid,
                'group_name' => $gid === 0 ? __('General') : ($groupModel ? $groupModel->group_name : __('Group') . ' #' . $gid),
                'devices_count' => $gid === 0 ? $ungroupedCount : ($groupModel ? (int) $groupModel->devices_count : 0),
                'workers' => $grpWorkers,
                'load_pct' => $grpLoadPct,
                'load_label_class' => $grpLoadPct > 100 ? 'label-warning' : 'label-default',
                'pollers_meta' => $grpPollers->map(fn ($p): array => [
                    'name' => (string) $p->poller_name,
                    'nodeId' => (string) $p->node_id,
                    'rowClass' => (string) $p->row_class,
                ])->values(),
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
