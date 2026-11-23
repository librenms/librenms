<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class MailcowPostfixEmailsGraph extends ApplicationGraph
{
    /**
     * Field of app-mailcow-postfix-<id>.rrd => translation key of its label
     */
    private const array FIELDS = [
        'received' => 'graph.series.received',
        'delivered' => 'graph.application.mailcow_postfix.delivered',
        'forwarded' => 'graph.application.mailcow_postfix.forwarded',
        'deferred' => 'graph.application.mailcow_postfix.deferred',
        'bounced' => 'graph.application.mailcow_postfix.bounced',
        'rejected' => 'graph.application.mailcow_postfix.rejected',
        'held' => 'graph.application.mailcow_postfix.held',
        'discarded' => 'graph.application.mailcow_postfix.discarded',
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
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.application.mailcow_postfix.emails'), palette: 'mixed');
    }
}
