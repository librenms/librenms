<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindOutgoingGraph extends ApplicationGraph
{
    private const RECORD_TYPES = [
        'any', 'a', 'aaaa', 'cname', 'mx', 'ns', 'ptr', 'soa', 'srv', 'spf', 'afsdb', 'apl', 'caa', 'cdnskey', 'cds',
        'cert', 'dhcid', 'dlv', 'dnskey', 'ds', 'ipseckey', 'key', 'kx', 'loc', 'naptr', 'nsec', 'nsec3', 'nsec3param',
        'rrsig', 'rp', 'sig', 'sshfp', 'ta', 'tkey', 'tlsa', 'tsig', 'txt', 'uri', 'dname', 'axfr', 'ixfr', 'opt',
    ];

    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'outgoing']);

        $series = [];
        foreach (self::RECORD_TYPES as $type) {
            $series[$type] = new DataSeries($metric, $type, strtoupper($type));
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.queries_per_second'), palette: 'psychedelic');
    }
}
