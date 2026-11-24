<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

/**
 * One stat, given by the log_stat request var, of each log in the application data
 */
class HttpAccessLogCombinedStatGraph extends ApplicationGraph
{
    private const array BYTE_STATS = ['bytes', 'bytes_max', 'bytes_mean', 'bytes_median', 'bytes_min', 'bytes_mode', 'bytes_range', 'size', 'error_size'];

    protected function validation(): array
    {
        return [...parent::validation(), 'log_stat' => 'required|string'];
    }

    public function getSeries(): array
    {
        $stat = $this->params->input->string('log_stat');

        // log names come from the application data, so they are not translated
        $series = [];
        foreach ($this->application()->data['logs'] ?? [] as $log) {
            $series["log_$log"] = new DataSeries($this->metric(['stat' => "logs___{$log}___$stat"]), 'data', (string) $log);
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        $stat = $this->params->input->string('log_stat');
        $legendTitle = in_array($stat, self::BYTE_STATS, true)
            ? __('graph.application.http_access_log_combined.stat_bytes', ['stat' => $stat])
            : __('graph.application.http_access_log_combined.stat_count', ['stat' => $stat]);

        return new GraphOptions(scaleMin: 0, legendTitle: $legendTitle, palette: 'psychedelic');
    }
}
