<?php

namespace LibreNMS\Tests\Unit;

use App\Facades\LibrenmsConfig;
use App\Models\User;
use App\Models\UserPref;
use Illuminate\Database\Eloquent\Collection;
use LibreNMS\Tests\TestCase;

class TrafficSameAxisTest extends TestCase
{
    public function testDefaultRetainsInvertedGraphs(): void
    {
        LibrenmsConfig::set('webui.graph_stacked', false);
        $this->assertSame(['transparency' => '', 'stacked' => '-1'], generate_stacked_graphs());
    }

    public function testPersonalPreferenceOverridesSiteDefaultAndDoesNotLeakBetweenUsers(): void
    {
        LibrenmsConfig::set('webui.graph_stacked', false);
        $enabled = new User;
        $enabled->setRelation('preferences', new Collection([new UserPref(['pref' => 'traffic_same_axis', 'value' => 1])]));
        $this->actingAs($enabled);
        $this->assertSame(['transparency' => '88', 'stacked' => '1'], generate_stacked_graphs());

        LibrenmsConfig::set('webui.graph_stacked', true);
        $disabled = new User;
        $disabled->setRelation('preferences', new Collection([new UserPref(['pref' => 'traffic_same_axis', 'value' => 0])]));
        $this->actingAs($disabled);
        $this->assertSame(['transparency' => '', 'stacked' => '-1'], generate_stacked_graphs());
        $this->assertSame('1', generate_stacked_graphs(true)['stacked']);
    }

    public function testExistingGlobalSettingAndCustomTransparencyRemainSupported(): void
    {
        LibrenmsConfig::set('webui.graph_stacked', true);
        $this->assertSame(['transparency' => '55', 'stacked' => '1'], generate_stacked_graphs(false, '55'));
    }

    public function testAggregateGraphKeepsTotalsAndHistoryDefinitionsValidInBothModes(): void
    {
        foreach ([0, 1] as $enabled) {
            $user = new User;
            $user->setRelation('preferences', new Collection([new UserPref(['pref' => 'traffic_same_axis', 'value' => $enabled])]));
            $this->actingAs($user);
            $graph_params = new \LibreNMS\Data\Graphing\GraphParameters(['type' => 'multiport_bits', 'previous' => 'yes']);
            $rrd_filenames = ['/tmp/example-port.rrd'];
            $rrd_options = [];
            $ds_in = 'INOCTETS';
            $ds_out = 'OUTOCTETS';
            $colour_area_in = 'CDEB8B';
            $colour_area_out = 'C3D9FF';
            $float_precision = 2;
            $from = $graph_params->from;
            $prev_from = $graph_params->prev_from;
            $period = $graph_params->period;
            require base_path('includes/html/graphs/generic_multi_data.inc.php');

            $direction = $enabled ? 1 : -1;
            $this->assertContains("CDEF:doutoctets=outoctets,$direction,*", $rrd_options);
            $this->assertContains("CDEF:doutoctetsX=outoctetsX,$direction,*", $rrd_options);
            $this->assertContains('CDEF:octets=inoctets,outoctets,+', $rrd_options);
            $this->assertContains('GPRINT:outbits:LAST:%6.2lf%s', $rrd_options);
            $names = [];
            foreach ($rrd_options as $option) {
                if (preg_match('/^(?:CDEF|VDEF|DEF):([^=]+)=/', $option, $match)) {
                    $this->assertNotContains($match[1], $names, 'RRD variable names must be unique, including historical percentiles');
                    $names[] = $match[1];
                }
            }
        }
    }
}
