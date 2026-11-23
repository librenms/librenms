<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class MailcowPostfixDomainsGraph extends ApplicationGraph
{
    /**
     * Field of app-mailcow-postfix-<id>.rrd => translation key of its label
     */
    private const array FIELDS = [
        'senders' => 'graph.application.mailcow_postfix.senders',
        'sendinghostsdomains' => 'graph.application.mailcow_postfix.sender_domains',
        'recipients' => 'graph.application.mailcow_postfix.recipients',
        'recipienthostsdomains' => 'graph.application.mailcow_postfix.recipient_domains',
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
        return new GraphOptions(scaleMin: 0, legendTitle: __('graph.application.mailcow_postfix.domains'), palette: 'mixed');
    }
}
