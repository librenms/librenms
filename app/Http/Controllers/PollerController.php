<?php

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Poller;
use App\Models\PollerCluster;
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
        $clusterGroups = $this->clusterGroups($pollerCluster);

        return view('poller.poller', [
            'current_tab' => 'poller',
            'pollers' => $this->poller(),
            'poller_cluster' => $pollerCluster,
            'poller_cluster_meta' => $this->pollersMeta($pollerCluster),
            'cluster_groups' => $clusterGroups,
            'healthy_count' => $pollerCluster->where('row_class', 'success')->count(),
            'can_delete_any' => $pollerCluster->contains(fn ($poller) => $poller->long_not_polled && Gate::allows('delete', $poller)),
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

        if ($poller instanceof PollerCluster) {
            foreach ($poller->stats as $stat) {
                $maxSeconds = (int) $stat->frequency * (int) $stat->workers;
                $stat->setAttribute('max_seconds', $maxSeconds);
                $stat->setAttribute('util_pct', $maxSeconds > 0 ? (int) round($stat->worker_seconds / $maxSeconds * 100) : 0);
            }

            $primaryStat = $poller->stats->firstWhere('poller_type', 'poller') ?? $poller->stats->first();
            $poller->setAttribute('primary_stat', $primaryStat);
            $poller->setAttribute('other_stats', $poller->stats->reject(fn ($stat) => $stat === $primaryStat));

            if ($poller->row_class === 'success' && $primaryStat?->getAttribute('util_pct') > 100) {
                $poller->row_class = 'warning';
            }
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
     * Name, node id, status and groups of each poller for the search/status/group filter
     *
     * @return Collection<int, array{name: string, nodeId: string, rowClass: string, groupIds: array<mixed>}>
     */
    private function pollersMeta(Collection $pollers): Collection
    {
        return $pollers->map(fn ($p): array => [
            'name' => (string) $p->poller_name,
            'nodeId' => (string) $p->node_id,
            'rowClass' => (string) $p->row_class,
            'groupIds' => (array) ($p->getAttribute('group_ids') ?? [0]),
        ])->values();
    }

    private function clusterGroups(Collection $pollerCluster): Collection
    {
        $groups = PollerGroup::query()->withCount('devices')->orderBy('id')->get()->keyBy('id');
        $defaultGroup = (string) LibrenmsConfig::get('distributed_poller_group', '0');
        $groupName = function (int $gid) use ($groups): string {
            if ($gid === 0) {
                return __('General');
            }
            $groupModel = $groups->get($gid);

            return $groupModel ? $groupModel->group_name : __('Group') . ' #' . $gid;
        };
        $usedGroupIds = [];

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

            $poller->setAttribute('group_ids', $groupIds);
            $poller->setAttribute('groups_served', implode(', ', array_map($groupName, $groupIds)));

            foreach ($groupIds as $gid) {
                $usedGroupIds[$gid] = true;
            }
        }

        ksort($usedGroupIds);
        $ungroupedCount = isset($usedGroupIds[0]) ? Device::where('poller_group', 0)->count() : 0;

        $result = new Collection();
        foreach (array_keys($usedGroupIds) as $gid) {
            $groupModel = $groups->get($gid);

            $result->push([
                'id' => (int) $gid,
                'group_name' => $groupName((int) $gid),
                'devices_count' => $gid === 0 ? $ungroupedCount : ($groupModel ? (int) $groupModel->devices_count : 0),
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
