<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class FbsdNfsClientStatsGraph extends ApplicationGraph
{
    /**
     * NFS operations, each a field of app-fbsd-nfs-client-<id>.rrd. The operation name is the label.
     */
    private const array OPERATIONS = [
        'getattr', 'setattr', 'lookup', 'readlink', 'read', 'write', 'create', 'remove', 'rename', 'link', 'symlink',
        'mkdir', 'rmdir', 'readdir', 'rdirplus', 'access', 'mknod', 'fsstat', 'fsinfo', 'pathconf', 'commit',
    ];

    public function getSeries(): array
    {
        $metric = $this->metric();
        $style = new SeriesStyle(fill: true);

        $series = [];
        foreach (self::OPERATIONS as $operation) {
            $series[$operation] = new DataSeries($metric, $operation, $operation, style: $style);
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.per_second'), palette: 'mixed');
    }
}
