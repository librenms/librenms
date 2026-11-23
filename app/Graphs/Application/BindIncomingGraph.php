<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindIncomingGraph extends ApplicationGraph
{
    /**
     * Common record types, stored in app-bind-<id>.rrd
     */
    private const array COMMON_TYPES = ['any', 'a', 'aaaa', 'cname', 'mx', 'ns', 'ptr', 'soa', 'srv', 'spf'];

    /**
     * The other record types, stored in app-bind-<id>-incoming.rrd
     */
    private const array OTHER_TYPES = [
        'afsdb', 'apl', 'caa', 'cdnskey', 'cds', 'cert', 'dhcid', 'dlv', 'dnskey', 'ds', 'ipseckey', 'key', 'kx', 'loc',
        'naptr', 'nsec', 'nsec3', 'nsec3param', 'rrsig', 'rp', 'sig', 'sshfp', 'ta', 'tkey', 'tlsa', 'tsig', 'txt', 'uri',
        'dname', 'axfr', 'ixfr', 'opt',
    ];

    public function getSeries(): array
    {
        $series = [];
        $common_metric = $this->metric();
        foreach (self::COMMON_TYPES as $type) {
            $series[$type] = new DataSeries($common_metric, $type, strtoupper($type));
        }

        $other_metric = $this->metric(['stat' => 'incoming']);
        foreach (self::OTHER_TYPES as $type) {
            $series[$type] = new DataSeries($other_metric, $type, strtoupper($type));
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.queries_per_second'), palette: 'psychedelic');
    }
}
