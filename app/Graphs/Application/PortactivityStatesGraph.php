<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

/**
 * Connections of the port in the port var by TCP state, the fields of one direction of app-portactivity-<id>-<port>.rrd
 */
abstract class PortactivityStatesGraph extends ApplicationGraph
{
    // prefix of the state fields, such as from for fromESTABLISHED
    protected const string FIELD_PREFIX = '';

    private const array STATES = [
        'CLOSED', 'SYN_SENT', 'SYN_RECEIVED', 'ESTABLISHED', 'CLOSE_WAIT', 'FIN_WAIT_1',
        'CLOSING', 'LAST_ACK', 'FIN_WAIT_2', 'TIME_WAIT', 'UNKNOWN', 'other',
    ];

    protected function validation(): array
    {
        return [...parent::validation(), 'port' => 'required|string'];
    }

    public function getSeries(): array
    {
        $metric = $this->metric(['port' => $this->params->input->string('port')]);
        $series = [];
        foreach (self::STATES as $state) {
            $series[$state] = new DataSeries($metric, static::FIELD_PREFIX . $state, $state);
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.connections'), palette: 'psychedelic');
    }
}
