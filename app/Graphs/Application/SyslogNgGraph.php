<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

/**
 * A graph of one syslog-ng stat, of the source named by the syslog_ng_source request var or the global stats without one.
 * It is stored in app-syslog-ng-<app_id>-source_-_<stat>_-_<source>.rrd, or app-syslog-ng-<app_id>-global_-_<stat>.rrd.
 */
abstract class SyslogNgGraph extends ApplicationGraph
{
    protected const string STAT = '';

    /** translation key of the legend title */
    protected const string LEGEND = 'graph.units.messages_per_second';

    /** translation key of the single data field a source stores for this stat, null when it stores the summary fields */
    protected const ?string SOURCE_LABEL = null;

    protected function validation(): array
    {
        return [...parent::validation(), 'syslog_ng_source' => 'nullable|string'];
    }

    public function getSeries(): array
    {
        $source = $this->params->input->string('syslog_ng_source');
        $stat = $source === null ? 'global_-_' . static::STAT : 'source_-_' . static::STAT . '_-_' . $source;
        $metric = $this->metric(['stat' => $stat]);

        if ($source !== null && static::SOURCE_LABEL !== null) {
            return ['data' => new DataSeries($metric, 'data', __(static::SOURCE_LABEL))];
        }

        return [
            'max' => new DataSeries($metric, 'max', __('graph.series.max')),
            'mean' => new DataSeries($metric, 'mean', __('graph.series.mean')),
            'median' => new DataSeries($metric, 'median', __('graph.series.median')),
            'min' => new DataSeries($metric, 'min', __('graph.series.min')),
            'mode' => new DataSeries($metric, 'mode', __('graph.series.mode')),
            'sum' => new DataSeries($metric, 'sum', __('graph.series.sum')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __(static::LEGEND), palette: 'rainbow');
    }
}
