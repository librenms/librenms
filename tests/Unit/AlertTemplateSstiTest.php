<?php

namespace LibreNMS\Tests\Unit;

use App\Models\AlertTemplate;
use LibreNMS\Alert\Template;
use LibreNMS\Tests\TestCase;

class AlertTemplateSstiTest extends TestCase
{
    public function testDefaultTitleDoesNotExecuteBladeInDeviceDisplayName(): void
    {
        $flag = false;
        $GLOBALS['__test_ssti_flag'] = &$flag;

        $maliciousDisplay = 'Router1 {{ ($GLOBALS["__test_ssti_flag"] = true) ? "malicious" : "" }}';

        $data = [
            'state' => 1,
            'display' => $maliciousDisplay,
            'name' => 'Test Rule',
            'title' => 'Alert for device ' . $maliciousDisplay . ' - Test Rule',
            'template' => (new AlertTemplate)->forceFill([
                'name' => 'Default Alert Template',
                'title' => '',
                'title_rec' => '',
                'template' => '{{ $alert->title }}',
            ]),
            'alert' => [
                'state' => 1,
                'display' => $maliciousDisplay,
                'name' => 'Test Rule',
                'title' => 'Alert for device ' . $maliciousDisplay . ' - Test Rule',
            ],
        ];

        $tpl = new Template();
        $renderedTitle = $tpl->getTitle($data);

        $this->assertFalse($flag, 'Blade expressions in device display string should not be evaluated');
        $this->assertEquals('Alert for device ' . e($maliciousDisplay) . ' - Test Rule', $renderedTitle);
    }

    public function testCustomTemplateTitleRendersSafely(): void
    {
        $data = [
            'state' => 1,
            'display' => 'my-switch-01',
            'name' => 'Ping Latency',
            'template' => (new AlertTemplate)->forceFill([
                'name' => 'Custom Template',
                'title' => 'Custom Alert: {{ $alert->display }} - {{ $alert->name }}',
                'title_rec' => 'Recovered: {{ $alert->display }}',
                'template' => '{{ $alert->title }}',
            ]),
            'alert' => [
                'state' => 1,
                'display' => 'my-switch-01',
                'name' => 'Ping Latency',
                'title' => 'Fallback Title',
            ],
        ];

        $tpl = new Template();
        $renderedTitle = $tpl->getTitle($data);

        $this->assertEquals('Custom Alert: my-switch-01 - Ping Latency', $renderedTitle);
    }

    public function testDirectivesInDeviceDisplayAreNotEvaluated(): void
    {
        $flag = false;
        $GLOBALS['__test_ssti_flag_directive'] = &$flag;

        $maliciousDisplay = 'Router1 @php($GLOBALS["__test_ssti_flag_directive"] = true)';

        $data = [
            'state' => 1,
            'display' => $maliciousDisplay,
            'name' => 'Test Rule',
            'title' => 'Alert for device ' . $maliciousDisplay . ' - Test Rule',
            'template' => (new AlertTemplate)->forceFill([
                'name' => 'Default Alert Template',
                'title' => '',
                'title_rec' => '',
                'template' => '{{ $alert->title }}',
            ]),
            'alert' => [
                'state' => 1,
                'display' => $maliciousDisplay,
                'name' => 'Test Rule',
                'title' => 'Alert for device ' . $maliciousDisplay . ' - Test Rule',
            ],
        ];

        $tpl = new Template();
        $renderedTitle = $tpl->getTitle($data);

        $this->assertFalse($flag, 'Blade directives like @php in device display string should not be evaluated');
        $this->assertEquals('Alert for device ' . e($maliciousDisplay) . ' - Test Rule', $renderedTitle);
    }
}
