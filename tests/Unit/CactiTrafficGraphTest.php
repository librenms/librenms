<?php

namespace LibreNMS\Tests\Unit;

use App\Facades\LibrenmsConfig;
use LibreNMS\Data\Graphing\GraphParameters;
use LibreNMS\Tests\TestCase;

class CactiTrafficGraphTest extends TestCase
{
    public function testStyleIsOptInAndLimitedToSupportedGraphs(): void
    {
        foreach (['device_bits', 'port_bits'] as $type) {
            $this->assertSame('default', (new GraphParameters(['type' => $type]))->trafficStyle);
            $this->assertSame('cacti', (new GraphParameters(['type' => $type, 'traffic_style' => 'cacti']))->trafficStyle);
            $this->assertSame('default', (new GraphParameters(['type' => $type, 'traffic_style' => 'invalid']))->trafficStyle);
        }
        foreach (['device_processor', 'port_pkts', 'multiport_bits'] as $type) {
            $this->assertSame('default', (new GraphParameters(['type' => $type, 'traffic_style' => 'cacti']))->trafficStyle);
        }
    }

    public function testCactiUsesPositiveAxisAndExactColoursWithEitherSiteSetting(): void
    {
        foreach ([false, true] as $stacked) {
            LibrenmsConfig::set('webui.graph_stacked', $stacked);
            foreach (['port_bits', 'device_bits'] as $type) {
                $options = $this->graphOptions($type, ['traffic_style' => 'cacti', 'traffic_same_axis' => '1']);
                $this->assertContains('CDEF:doutoctets=outoctets,1,*', $options);
                $this->assertContains('LINE1.5:doutbits#002A97:Out', $options);
                $this->assertNotEmpty(preg_grep('/^AREA:inbits#00CF00:/', $options));
                $this->assertEmpty(preg_grep('/^AREA:dout/', $options));
                $this->assertContains('CDEF:octets=inoctets,outoctets,+', $options);
                $this->assertContains('GPRINT:outbits:LAST:%6.2lf%s', $options);
                $this->assertContains('VDEF:totout=outoctets,TOTAL', $options);
                $this->assertContains('LINE1:dpercentile_out#aa0000', $options);
            }
        }
    }

    public function testDefaultRetainsExistingAxisColoursAndMaximumStatistics(): void
    {
        LibrenmsConfig::set('webui.graph_stacked', false);
        $default = $this->graphOptions('port_bits');
        $this->assertSame($default, $this->graphOptions('port_bits', ['traffic_style' => 'invalid']));
        $this->assertContains('CDEF:doutoctets=outoctets,-1,*', $default);
        $this->assertContains('AREA:inbits#90B040:', $default);
        $this->assertContains('AREA:doutbits#8080C0:', $default);
        $this->assertContains('GPRINT:outbits_max:MAX:%6.2lf%s', $default);
        $this->assertContains('GPRINT:outbits_max:MAX:%6.2lf%s', $this->graphOptions('port_bits', ['traffic_style' => 'cacti']));
        LibrenmsConfig::set('webui.graph_stacked', true);
        $this->assertContains('AREA:doutbits#8080C088:', $this->graphOptions('port_bits'));
        $this->assertContains('CDEF:doutoctets=outoctets,1,*', $this->graphOptions('port_bits'));
    }

    public function testPreviousAndInverseKeepValidDefinitionsAndPositiveHistory(): void
    {
        foreach (['port_bits', 'device_bits'] as $type) {
            $options = $this->graphOptions($type, ['traffic_style' => 'cacti', 'previous' => 'yes', 'inverse' => 1]);
            $this->assertContains('CDEF:doutoctetsX=outoctetsX,1,*', $options);
            $this->assertContains('VDEF:percentile_outX=outbitsX,95,PERCENT', $options);
            $this->assertEmpty(preg_grep('/^AREA:dout/', $options));
            $names = [];
            foreach ($options as $option) {
                if (preg_match('/^(?:DEF|CDEF|VDEF):([^=]+)=/', $option, $match)) {
                    $this->assertNotContains($match[1], $names, 'RRD variable names must be unique');
                    $names[] = $match[1];
                }
            }
            $this->assertNotEmpty(preg_grep($type === 'port_bits'
                ? '/^DEF:outoctets=.*:INOCTETS:AVERAGE/'
                : '/^CDEF:outoctets=inoctets0,/', $options));
        }
    }

    public function testPortSpeedZoomAndAsymmetricSpeedMarkersUseTheSelectedAxis(): void
    {
        $cacti = $this->graphOptions('port_bits', ['traffic_style' => 'cacti', 'port_speed_zoom' => true], 100000000, 200000000);
        $this->assertContains('CDEF:doutoctets=outoctets,1,*', $cacti);
        $this->assertNotEmpty(preg_grep('/^LINE2:100000000#000000:Out Port Speed/', $cacti));
        $default = $this->graphOptions('port_bits', ['port_speed_zoom' => false], 100000000, 200000000);
        $this->assertNotEmpty(preg_grep('/^HRULE:-100000000#000000:Out Port Speed/', $default));
    }

    public function testAggregateAndHiddenLegendRemainSupported(): void
    {
        $options = $this->graphOptions('device_bits', ['traffic_style' => 'cacti', 'legend' => 'no']);
        $this->assertContains('AREA:inbits#00CF00:', $options);
        $this->assertContains('LINE1.5:doutbits#002A97:', $options);
        $this->assertEmpty(preg_grep('/^GPRINT:/', $options));
        $this->assertContains('CDEF:inoctets=inoctets0,UN,0,inoctets0,IF,inoctets1,UN,0,inoctets1,IF,+', $options);
    }

    /**
     * @param  array<string, mixed>  $vars
     * @return list<string>
     */
    private function graphOptions(string $type, array $vars = [], int $egress_speed = 0, int $ingress_speed = 0): array
    {
        LibrenmsConfig::set('percentile_value', 95);
        $graph_params = new GraphParameters(['type' => $type, 'from' => 1700000000, 'to' => 1700003600, ...$vars]);
        $rrd_filename = '/tmp/cacti-port.rrd';
        $rrd_filenames = [$rrd_filename, '/tmp/cacti-port2.rrd'];
        $rrd_options = [];
        $ds_in = 'INOCTETS';
        $ds_out = 'OUTOCTETS';
        $colour_area_in = '91B13C';
        $colour_area_out = '8080BD';
        $float_precision = 2;
        $from = $graph_params->from;
        $to = $graph_params->to;
        $prev_from = $graph_params->prev_from;
        $period = $graph_params->period;
        $inverse = $graph_params->inverse;
        $legend = $vars['legend'] ?? null;
        require base_path($type === 'port_bits'
            ? 'includes/html/graphs/port/bits.inc.php'
            : 'includes/html/graphs/generic_multi_data.inc.php');

        return $rrd_options;
    }
}
