<?php

namespace App\Graphs\Application;

class PoudrierePackageSizeGraph extends PoudriereGraph
{
    protected function stats(): array
    {
        return [
            'package_size_all' => __('graph.application.poudriere.all'),
            'package_size_building' => __('graph.application.poudriere.building'),
            'package_size_latest' => __('graph.application.poudriere.latest'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.bytes');
    }
}
