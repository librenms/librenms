<?php

namespace LibreNMS\Tests\Unit\Graphs\Rrd;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\TrafficOptions;
use App\Data\TimeSeries\MetricIdentity;
use App\Graphs\Rrd\RrdLegend;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\TestWith;

class RrdLegendTest extends TestCase
{
    /**
     * @param  array<string, mixed>  $params
     */
    private function legend(array $params, string $label = 'In', ?TrafficOptions $traffic = null): RrdLegend
    {
        $series = ['in' => new DataSeries(new MetricIdentity('port', ['id' => 1]), 'INOCTETS', $label, 'bps')];

        return new RrdLegend(new GraphParameters($params), new GraphOptions(legendTitle: 'bps', traffic: $traffic, percentiles: [95]), $series,
            fn (string $id, int $index) => "{$id}p$index");
    }

    /**
     * @return list<string> the value each column shows
     */
    private function columns(RrdLegend $legend): array
    {
        return array_map(fn (string $value) => preg_replace('/^GPRINT:(\w+(:[A-Z]+)?):.*/', '$1', $value), $legend->values('ds0', 'bps'));
    }

    /**
     * @param  list<string>  $columns
     */
    #[TestWith([50, ['ds0:LAST']])]
    #[TestWith([215, ['ds0:LAST']])]
    #[TestWith([300, ['ds0:LAST', 'ds0max:MAX', 'ds0:AVERAGE']])]
    #[TestWith([500, ['ds0:LAST', 'ds0max:MAX', 'ds0:AVERAGE', 'ds0p0']])]
    #[TestWith([700, ['ds0:LAST', 'ds0min:MIN', 'ds0max:MAX', 'ds0:AVERAGE', 'ds0p0']])]
    public function test_narrow_graphs_keep_now_avg_and_max_before_the_percentiles_and_min(int $width, array $columns): void
    {
        $this->assertSame($columns, $this->columns($this->legend(['width' => $width])));
    }

    public function test_headings_line_up_with_the_columns(): void
    {
        $legend = $this->legend(['width' => 500]);

        $this->assertSame("COMMENT:         Now         Max         Avg        95th\l", $legend->header()[1]);
        $this->assertStringEndsWith('\n', $legend->values('ds0', 'bps')[3]);
    }

    public function test_labels_widen_to_the_longest_label_where_the_row_has_room(): void
    {
        $long = str_repeat('x', 50);

        $this->assertSame(str_pad('In', 16) . ' ', $this->legend(['width' => 1000])->label('In'));
        $this->assertSame(str_repeat('x', 16) . ' ', $this->legend(['width' => 300], $long)->label($long));
        $this->assertSame(str_repeat('x', 25) . ' ', $this->legend(['width' => 500], $long)->label($long));
        $this->assertSame(str_repeat('x', 40) . ' ', $this->legend(['width' => 1000], $long)->label($long));
        $this->assertSame('core-switch port 1 In ', $this->legend(['width' => 1000], 'core-switch port 1 In')->label('core-switch port 1 In'));
        $this->assertSame('COMMENT:' . str_pad('bps', 23) . ' ', $this->legend(['width' => 1000], 'core-switch port 1 In')->header()[0]);
    }

    public function test_previous_period_rows_of_traffic_count_toward_the_longest_label(): void
    {
        $label = 'core-switch port 1 In';

        $this->assertSame($label . ' ', $this->legend(['width' => 1000], $label, new TrafficOptions)->label($label));
        // Prev core-switch port 1 In
        $this->assertSame(str_pad($label, 26) . ' ', $this->legend(['width' => 1000, 'previous' => 'yes'], $label, new TrafficOptions)->label($label));
    }

    public function test_full_size_images_include_the_axis_in_the_width(): void
    {
        $this->assertCount(3, $this->columns($this->legend(['width' => 382])));
        $this->assertCount(2, $this->columns($this->legend(['width' => 382, 'absolute' => 1])));
    }

    public function test_totals_continue_on_the_next_line_where_they_do_not_fit(): void
    {
        $totals = [['trafficbytes', 'Total'], ['ds4total', 'Total In'], ['ds5total', 'Total Out']];

        $this->assertSame([
            'GPRINT:trafficbytes:Total %6.2lf%sB',
            'GPRINT:ds4total:Total In %6.2lf%sB',
            'GPRINT:ds5total:Total Out %6.2lf%sB\l',
        ], $this->legend(['width' => 400])->totals($totals, 'B'));
        $this->assertSame([
            'GPRINT:trafficbytes:Total %6.2lf%sB',
            'GPRINT:ds4total:Total In %6.2lf%sB\l',
            'GPRINT:ds5total:Total Out %6.2lf%sB\l',
        ], $this->legend(['width' => 382, 'absolute' => 1])->totals($totals, 'B'));
    }
}
