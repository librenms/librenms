<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Poller;
use App\Models\PollerCluster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PollersMetrics
{
    use Traits\MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Legacy pollers metrics
        $this->addPollersMetrics($lines);

        // Poller cluster metrics
        $this->addPollerClusterMetrics($lines);

        // Poller cluster stats metrics
        $this->addPollerClusterStatsMetrics($lines);

        return implode("\n", $lines) . "\n";
    }

    private function addPollersMetrics(array &$lines): void
    {
        // Gather global metrics
        $total = Poller::count();
        $active = Poller::isActive()->count();
        $this->appendMetricBlock($lines, 'librenms_pollers_total', 'Total number of legacy pollers', 'gauge', [$total]);
        $this->appendMetricBlock($lines, 'librenms_pollers_active', 'Number of active legacy pollers', 'gauge', [$active]);

        // Prepare per-poller arrays
        $devices_lines = [];
        $time_taken_lines = [];

        // Gather per-poller metrics
        foreach (Poller::all() as $poller) {
            $labels = sprintf('poller_id="%s",poller_name="%s"',
                $poller->id,
                $this->escapeLabel((string) $poller->poller_name)
            );

            $devices_lines[] = "librenms_pollers_devices{{$labels}} " . ((int) $poller->devices);
            $time_taken_lines[] = "librenms_pollers_time_taken_seconds{{$labels}} " . ((float) $poller->time_taken);
        }

        // Append per-poller metrics
        if (!empty($devices_lines)) {
            $this->appendMetricBlock($lines, 'librenms_pollers_devices', 'Number of devices polled by this poller', 'gauge', $devices_lines);
            $this->appendMetricBlock($lines, 'librenms_pollers_time_taken_seconds', 'Time taken for last polling cycle in seconds', 'gauge', $time_taken_lines);
        }
    }

    private function addPollersClusterMetrics(array &$lines): void
    {
        // Gather global metrics
        $total = PollerCluster::count();
        $active = PollerCluster::isActive()->count();

        $this->appendMetricBlock($lines, 'librenms_poller_cluster_total', 'Total number of poller cluster nodes', 'gauge', [$total]);
        $this->appendMetricBlock($lines, 'librenms_poller_cluster_active', 'Number of active poller cluster nodes', 'gauge', [$active]);

        // Prepare per-cluster-node arrays
        $is_master_lines = [];
        $is_active_lines = [];
        $last_report_lines = [];

        // Gather per-cluster-node metrics
        foreach (PollerCluster::all() as $cluster) {
            $labels = sprintf('cluster_id="%s",node_id="%s",poller_name="%s",poller_version="%s",poller_groups="%s"',
                $cluster->id,
                $this->escapeLabel((string) $cluster->node_id),
                $this->escapeLabel((string) $cluster->poller_name),
                $this->escapeLabel((string) $cluster->poller_version),
                $this->escapeLabel((string) $cluster->poller_groups)
            );

            $is_master_lines[] = "librenms_poller_cluster_is_master{{$labels}} " . ($cluster->master ? '1' : '0');
            
            // Check if cluster is active (reported recently)
            $is_active = PollerCluster::where('id', $cluster->id)->isActive()->exists();
            $is_active_lines[] = "librenms_poller_cluster_is_active{{$labels}} " . ($is_active ? '1' : '0');
            
            // Convert last_report to unix timestamp
            $last_report_timestamp = $cluster->last_report ? $cluster->last_report->timestamp : 0;
            $last_report_lines[] = "librenms_poller_cluster_last_report_timestamp{{$labels}} " . $last_report_timestamp;
        }

        // Append per-cluster-node metrics
        if (!empty($is_master_lines)) {
            $this->appendMetricBlock($lines, 'librenms_poller_cluster_is_master', 'Whether this cluster node is a master (1) or not (0)', 'gauge', $is_master_lines);
            $this->appendMetricBlock($lines, 'librenms_poller_cluster_is_active', 'Whether this cluster node is active (1) or not (0)', 'gauge', $is_active_lines);
            $this->appendMetricBlock($lines, 'librenms_poller_cluster_last_report_timestamp', 'Unix timestamp of last report from this cluster node', 'gauge', $last_report_lines);
        }
    }

    private function addPollerClusterStatsMetrics(array &$lines): void
    {
        // Gather cluster stats
        $stats = DB::table('poller_cluster_stats')
            ->join('poller_cluster', 'poller_cluster_stats.parent_poller', '=', 'poller_cluster.id')
            ->select(
                'poller_cluster_stats.*',
                'poller_cluster.node_id',
                'poller_cluster.poller_name'
            )
            ->get();

        // Prepare stats arrays
        $devices_lines = [];
        $worker_seconds_lines = [];
        $workers_lines = [];
        $frequency_lines = [];
        $depth_lines = [];

        foreach ($stats as $stat) {
            $labels = sprintf('cluster_id="%s",node_id="%s",poller_name="%s",poller_type="%s"',
                $stat->parent_poller,
                $this->escapeLabel((string) $stat->node_id),
                $this->escapeLabel((string) $stat->poller_name),
                $this->escapeLabel((string) $stat->poller_type)
            );

            $devices_lines[] = "librenms_poller_cluster_stats_devices{{$labels}} " . ((int) $stat->devices);
            $worker_seconds_lines[] = "librenms_poller_cluster_stats_worker_seconds{{$labels}} " . ((float) $stat->worker_seconds);
            $workers_lines[] = "librenms_poller_cluster_stats_workers{{$labels}} " . ((int) $stat->workers);
            $frequency_lines[] = "librenms_poller_cluster_stats_frequency{{$labels}} " . ((int) $stat->frequency);
            $depth_lines[] = "librenms_poller_cluster_stats_depth{{$labels}} " . ((int) $stat->depth);
        }

        // Append cluster stats metrics
        if (!empty($devices_lines)) {
            $this->appendMetricBlock($lines, 'librenms_poller_cluster_stats_devices', 'Number of devices handled by this cluster poller', 'gauge', $devices_lines);
            $this->appendMetricBlock($lines, 'librenms_poller_cluster_stats_worker_seconds', 'Worker seconds consumed by this cluster poller', 'gauge', $worker_seconds_lines);
            $this->appendMetricBlock($lines, 'librenms_poller_cluster_stats_workers', 'Number of workers for this cluster poller', 'gauge', $workers_lines);
            $this->appendMetricBlock($lines, 'librenms_poller_cluster_stats_frequency', 'Frequency setting for this cluster poller', 'gauge', $frequency_lines);
            $this->appendMetricBlock($lines, 'librenms_poller_cluster_stats_depth', 'Depth setting for this cluster poller', 'gauge', $depth_lines);
        }
    }
}