<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class MojoCapeSubmitSubsTop12Graph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $slugs = array_slice(array_keys($this->application()->data['slugs'] ?? []), 0, 12);

        $series = [];
        foreach ($slugs as $slug) {
            $series["slug_$slug"] = new DataSeries($this->metric(['slug' => "slugs___-___{$slug}"]), 'sub_count', (string) $slug);
        }

        return $series;
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.bytes'), palette: 'rainbow');
    }
}
