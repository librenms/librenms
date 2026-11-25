<?php

namespace LibreNMS\Tests\Feature\Alert;

use App\Models\AlertFault;
use App\Models\AlertOperation;
use App\Models\AlertRule;
use App\Models\AlertTemplate;
use App\Models\AlertTemplateMap;
use App\Models\AlertTransport;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LibreNMS\Alert\AlertData;
use LibreNMS\Alert\LegacyTemplateConverter;
use LibreNMS\Alert\RunAlerts;
use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;
use LibreNMS\Tests\Mocks\CapturingAlertTransport;
use LibreNMS\Tests\Mocks\CapturingLegacyAlertTransport;
use LibreNMS\Tests\TestCase;

/**
 * Runs real alerts through RunAlerts with custom templates and checks what a
 * modern (AlertData) transport and a legacy (array) transport both receive.
 */
final class AlertTemplateRenderingTest extends TestCase
{
    use RefreshDatabase;

    private const string MODERN = 'capmodern';
    private const string LEGACY = 'caplegacy';

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([self::MODERN => CapturingAlertTransport::class, self::LEGACY => CapturingLegacyAlertTransport::class] as $type => $class) {
            if (! class_exists(Transport::getClass($type))) {
                class_alias($class, Transport::getClass($type));
            }
        }

        CapturingAlertTransport::$received = [];
        CapturingLegacyAlertTransport::$received = [];
    }

    public function testModernTemplate(): void
    {
        $device = $this->seedActiveAlert(AlertTemplate::factory()->create([
            'title' => '{{ $alert->hostname }}: {{ $alert->name }} ({{ $alert->severity }})',
            'template' => implode(PHP_EOL, [
                'Title: {{ $alert->title }}',
                'Sent via {{ $alert->transport }}',
                '@foreach ($alert->faults as $key => $fault)',
                '#{{ $key }} {{ $fault[\'ifName\'] }}: {{ $fault[\'msg\'] }} [{{ $fault[\'string\'] }}]',
                '@endforeach',
                'Status: @if ($alert->state == ' . AlertState::ACTIVE . ') PROBLEM @else OK @endif',
                'Unknown: {{ $alert->not_a_field }}',
            ]),
        ]));

        (new RunAlerts)->runAlerts();

        [$modern, $legacy] = $this->received();
        $title = "$device->hostname: Port down rule (critical)";
        $this->assertSame($title, $modern->title);
        $this->assertSame($title, $legacy['title']);

        foreach ([self::MODERN => $modern->msg, self::LEGACY => $legacy['msg']] as $type => $msg) {
            $this->assertStringContainsString("Title: $title", $msg);
            $this->assertStringContainsString("Sent via $type", $msg);
            $this->assertStringContainsString("#1 eth0: link down [id = $device->device_id; msg = link down; ]", $msg);
            $this->assertStringContainsString('Status:  PROBLEM', $msg);
            $this->assertStringContainsString('Unknown: not_a_field is not a valid $alert data name', $msg);
        }
    }

    public function testConvertedLegacyTemplate(): void
    {
        $legacyTitle = '%hostname alert: %name';
        $legacyBody = implode(PHP_EOL, [
            'Alert for %hostname',
            'Severity: %severity',
            '{if %state == 1}Status: PROBLEM{else}Status: OK{/if}',
            '{foreach %faults}#%key: %value.string{/foreach}',
        ]);
        $this->assertTrue(LegacyTemplateConverter::isLegacy($legacyBody));

        $converter = new LegacyTemplateConverter;
        $device = $this->seedActiveAlert(AlertTemplate::factory()->create([
            'title' => $converter->convert($legacyTitle),
            'template' => $converter->convert($legacyBody),
        ]));

        (new RunAlerts)->runAlerts();

        [$modern, $legacy] = $this->received();
        $this->assertSame("$device->hostname alert: Port down rule", trim((string) $modern->title));
        $this->assertSame($modern->title, $legacy['title']);

        foreach ([$modern->msg, $legacy['msg']] as $msg) {
            $this->assertStringContainsString("Alert for $device->hostname", $msg);
            $this->assertStringContainsString('Severity: critical', $msg);
            $this->assertStringContainsString('Status: PROBLEM', $msg);
            $this->assertStringNotContainsString('Status: OK', $msg);
            $this->assertStringContainsString("#1: id = $device->device_id; msg = link down;", $msg);
        }
    }

    public function testArrayAccessTemplate(): void
    {
        $device = $this->seedActiveAlert(AlertTemplate::factory()->create([
            'title' => '{{ $alert[\'hostname\'] }} down',
            'template' => implode(PHP_EOL, [
                'Host: {{ $alert[\'hostname\'] }}',
                'Rule: {{ $alert[\'name\'] }}',
                '@foreach ($alert[\'faults\'] as $fault)Port {{ $fault[\'ifName\'] }} @endforeach',
                '@if ($alert[\'not_a_field\']) leaked @endif',
                'Proc: {{ $alert[\'proc\'] ?? \'none\' }}',
            ]),
        ]));

        (new RunAlerts)->runAlerts();

        [$modern, $legacy] = $this->received();
        $this->assertSame("$device->hostname down", $modern->title);
        $this->assertSame($modern->title, $legacy['title']);

        foreach ([$modern->msg, $legacy['msg']] as $msg) {
            $this->assertStringContainsString("Host: $device->hostname", $msg);
            $this->assertStringContainsString('Rule: Port down rule', $msg);
            $this->assertStringContainsString('Port eth0', $msg);
            $this->assertStringNotContainsString('leaked', $msg);
            $this->assertStringContainsString('Proc: none', $msg);
        }
    }

    public function testRuleWithoutTemplateUsesDefaultTemplate(): void
    {
        AlertTemplate::factory()->create([
            'name' => 'Default Alert Template',
            'title' => '',
            'template' => 'Default: {{ $alert->hostname }} via {{ $alert->transport_name }}',
        ]);
        $device = $this->seedActiveAlert(null);

        (new RunAlerts)->runAlerts();

        [$modern, $legacy] = $this->received();
        $this->assertSame("Alert for device $device->display - Port down rule", $modern->title);
        $this->assertSame("Default: $device->hostname via Modern capture", $modern->msg);
        $this->assertSame("Default: $device->hostname via Legacy capture", $legacy['msg']);
        $this->assertSame('Default Alert Template', $legacy['template']->name);
    }

    public function testBrokenTemplateFallsBackAndStillDelivers(): void
    {
        $device = $this->seedActiveAlert(AlertTemplate::factory()->create([
            'name' => 'Broken Template',
            'title' => '{{ $alert->hostname }} broken',
            'template' => '@if(true) never closed',
        ]));

        (new RunAlerts)->runAlerts();

        [$modern, $legacy] = $this->received();
        $this->assertSame("$device->hostname broken", $modern->title);

        foreach ([$modern->msg, $legacy['msg']] as $msg) {
            $this->assertStringContainsString("$device->hostname broken", $msg);
            $this->assertStringContainsString('Severity: critical', $msg);
            $this->assertStringContainsString('Warning! Fallback template used due to error in template Broken Template', $msg);
        }
    }

    public function testLegacyTransportReceivesTheFullPayload(): void
    {
        $template = AlertTemplate::factory()->create(['title' => 'T', 'template' => 'B']);
        $device = $this->seedActiveAlert($template);

        (new RunAlerts)->runAlerts();

        [$modern, $legacy] = $this->received();
        $this->assertSame($device->hostname, $legacy['hostname']);
        $this->assertSame(self::LEGACY, $legacy['transport']);
        $this->assertSame('Legacy capture', $legacy['transport_name']);
        $this->assertSame($template->id, $legacy['template']->id);
        $this->assertInstanceOf(AlertData::class, $legacy['alert']);
        $this->assertSame($device->hostname, $legacy['alert']['hostname']);
        $this->assertSame(self::MODERN, $modern->transport);
        $this->assertSame($modern->uid, $legacy['uid']);
    }

    /**
     * @return array{0: AlertData, 1: array<string, mixed>}
     */
    private function received(): array
    {
        $this->assertCount(1, CapturingAlertTransport::$received, 'modern transport should receive one alert');
        $this->assertCount(1, CapturingLegacyAlertTransport::$received, 'legacy transport should receive one alert');

        return [CapturingAlertTransport::$received[0], CapturingLegacyAlertTransport::$received[0]];
    }

    /**
     * An open, active alert on one port of a new device, for a rule mapped to $template
     * whose operation sends to both capturing transports.
     */
    private function seedActiveAlert(?AlertTemplate $template): Device
    {
        $device = Device::factory()->create();

        $operation = AlertOperation::create([
            'name' => 'op ' . uniqid(),
            'default_operation_step_duration_seconds' => 300,
            'notifications_suppressed' => false,
        ]);
        $segment = $operation->segments()->create([
            'position' => 0,
            'operation_phase' => 'problem',
            'escalation_step_from' => 1,
            'escalation_step_to' => null,
            'start_in_seconds' => 0,
            'step_duration_seconds' => 300,
        ]);
        $transportIds = [
            AlertTransport::factory()->create(['transport_type' => self::MODERN, 'transport_name' => 'Modern capture'])->transport_id,
            AlertTransport::factory()->create(['transport_type' => self::LEGACY, 'transport_name' => 'Legacy capture'])->transport_id,
        ];
        $segment->transportSingles()->syncWithPivotValues($transportIds, ['target_type' => 'single']);

        $rule = AlertRule::create([
            'name' => 'Port down rule',
            'severity' => 'critical',
            'extra' => [],
            'disabled' => 0,
            'query' => 'SELECT * FROM devices WHERE device_id = ?',
            'builder' => ['condition' => 'AND', 'rules' => []],
            'proc' => null,
            'invert_map' => 0,
            'alert_operation_id' => $operation->id,
        ]);

        if ($template !== null) {
            AlertTemplateMap::create(['alert_rule_id' => $rule->id, 'alert_templates_id' => $template->id]);
        }

        DB::table('alerts')->insert([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
            'alerted' => AlertState::CLEAR,
            'open' => 1,
            'info' => json_encode(['open_fault_count' => 1]),
        ]);

        $details = ['rule' => [['id' => (int) $device->device_id, 'ifName' => 'eth0', 'msg' => 'link down']], 'contacts' => []];
        $fault = AlertFault::create([
            'rule_id' => $rule->id,
            'device_id' => $device->device_id,
            'entity_key' => (string) $device->device_id,
            'state' => AlertState::ACTIVE,
            'open' => 1,
            'alerted' => 0,
            'details' => $details,
        ]);

        DB::table('alert_log')->insert([
            'rule_id' => $rule->id,
            'device_id' => $device->device_id,
            'fault_id' => $fault->id,
            'state' => AlertState::ACTIVE,
            'details' => gzcompress((string) json_encode($details), 9),
            'time_logged' => date('Y-m-d H:i:s'),
        ]);

        return $device;
    }
}
