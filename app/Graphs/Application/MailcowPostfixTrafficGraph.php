<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class MailcowPostfixTrafficGraph extends ApplicationGraph
{
    /**
     * Field of app-mailcow-postfix-<id>.rrd => translation key of its label
     */
    private const array FIELDS = [
        'bytesreceived' => 'graph.application.mailcow_postfix.bytes_received',
        'bytesdelivered' => 'graph.application.mailcow_postfix.bytes_delivered',
    ];

    public function getSeries(): array
    {
        $metric = $this->metric();
        $series = [];
        foreach (self::FIELDS as $field => $label) {
            $series[$field] = new DataSeries($metric, $field, __($label), style: new SeriesStyle(fill: true));
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.application.mailcow_postfix.traffic'), palette: 'mixed');
    }
}
