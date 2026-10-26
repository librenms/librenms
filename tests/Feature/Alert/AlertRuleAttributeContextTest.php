<?php

namespace LibreNMS\Tests\Feature\Alert;

use App\Models\Alert;
use App\Models\AlertFault;
use App\Models\AlertLog;
use App\Models\AlertRule;
use App\Models\AlertTemplate;
use App\Models\AlertTransport;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use LibreNMS\Alert\RunAlerts;
use LibreNMS\Alert\Template;
use LibreNMS\Enum\AlertState;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class AlertRuleAttributeContextTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{int, bool}> */
    public static function notificationModes(): array
    {
        return [
            'grouped' => [AlertState::ACTIVE, false],
            'per entity' => [AlertState::ACTIVE, true],
            'ack grouped' => [AlertState::ACKNOWLEDGED, false],
            'ack per entity' => [AlertState::ACKNOWLEDGED, true],
            'recovery grouped' => [AlertState::RECOVERED, false],
            'recovery per entity' => [AlertState::RECOVERED, true],
        ];
    }

    #[DataProvider('notificationModes')]
    public function testMetadataReachesPreviewAndEveryDeliveryMode(int $state, bool $perEntity): void
    {
        $device = Device::factory()->create();
        $rule = AlertRule::factory()->create([
            'severity' => 'critical', 'notify_per_entity' => $perEntity, 'max_entities' => 10,
            'custom_attributes' => ['team' => 'network', 'severity' => 'custom', 'alertmanager_endsAt' => '20m'],
        ]);
        AlertTemplate::factory()->create([
            'name' => 'Default Alert Template', 'template' => '{{ $alert->attributes["team"] ?? "missing" }}',
            'title' => '', 'title_rec' => '',
        ]);
        $alert = Alert::factory()->create(['device_id' => $device->device_id, 'rule_id' => $rule->id, 'state' => $state, 'info' => []]);
        foreach ([1, 2] as $index) {
            $details = ['rule' => [['port_id' => $index]], 'contacts' => []];
            $fault = AlertFault::create([
                'device_id' => $device->device_id, 'rule_id' => $rule->id, 'entity_key' => 'port:' . $index,
                'state' => $state, 'alerted' => 1, 'open' => 1, 'details' => $details,
            ]);
            AlertLog::factory()->create(['device_id' => $device->device_id, 'rule_id' => $rule->id, 'fault_id' => $fault->id, 'state' => AlertState::ACTIVE, 'details' => $details]);
            if ($state !== AlertState::ACTIVE) {
                AlertLog::factory()->create(['device_id' => $device->device_id, 'rule_id' => $rule->id, 'fault_id' => $fault->id, 'state' => $state, 'details' => $details]);
            }
        }
        $runner = new class extends RunAlerts
        {
            /** @var array<int, array<string, mixed>> */
            public array $captured = [];

            /**
             * @param  array<string, mixed>  $obj
             * @param  array<int, array<string, mixed>>|null  $transportOverride
             */
            public function extTransports($obj, ?array $transportOverride = null): void
            {
                $this->captured[] = $obj;
            }
        };
        $alerts = $runner->loadAlerts('alerts.id = ' . $alert->id);
        $preview = $runner->describeAlert($alerts[0]);
        self::assertIsArray($preview);
        self::assertSame('network', $preview['attributes']['team'] ?? null);
        ob_start();
        try {
            $runner->issueAlert($alerts[0]);
        } finally {
            ob_end_clean();
        }
        self::assertCount($perEntity && $state !== AlertState::ACKNOWLEDGED ? 2 : 1, $runner->captured);
        foreach ($runner->captured as $data) {
            self::assertSame('network', $data['attributes']['team']);
            self::assertSame('critical', $data['severity']);
            self::assertSame('network', (new Template)->bladeBody($data));
        }
        Http::fake();
        $transport = AlertTransport::factory()->api('')->make();
        $config = $transport->transport_config;
        $config['api-headers'] = 'X-Team={{ $attributes.team }}';
        $transport->transport_config = $config;
        $transport->instance()->deliverAlert($runner->captured[0]);
        Http::assertSent(fn ($request) => $request->hasHeader('X-Team', 'network'));
    }
}
