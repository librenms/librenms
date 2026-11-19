<?php

namespace LibreNMS\Applications;

use App\Facades\Rrd;
use App\Models\Application;

class Ceph extends ApplicationDefinition
{
    public const string TYPE = 'ceph';

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $hostname = $application->device->hostname;
        $prefix = ['app', self::TYPE, (string) $application->app_id];

        foreach (Rrd::getRrdFiles($hostname, [...$prefix, 'pool']) as $file) {
            if (preg_match('/-pool-(.+)\.rrd$/', $file, $matches)) {
                $pool = $matches[1];
                $page->addGraphs(['pool_io' => "$pool Reads/Writes", 'pool_iops' => "$pool IOPS"], ['pool' => $pool]);
            }
        }
        foreach (Rrd::getRrdFiles($hostname, [...$prefix, 'osd']) as $file) {
            if (preg_match('/-osd-(.+)\.rrd$/', $file, $matches)) {
                $osd = $matches[1];
                $page->addGraphs(['osd_performance' => "$osd Latency"], ['osd' => $osd]);
            }
        }
        foreach (Rrd::getRrdFiles($hostname, [...$prefix, 'df']) as $file) {
            if (preg_match('/-df-(.+)\.rrd$/', $file, $matches)) {
                $pool = $matches[1];
                $graphs = $pool === 'c' ? ['pool_df' => 'Cluster Usage'] : ['pool_df' => "$pool Usage", 'pool_objects' => "$pool Objects"];
                $page->addGraphs($graphs, ['pool' => $pool]);
            }
        }
    }
}
