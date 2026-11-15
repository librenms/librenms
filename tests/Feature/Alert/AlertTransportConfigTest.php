<?php

namespace LibreNMS\Tests\Feature\Alert;

use App\Models\AlertTransport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Tests\TestCase;

final class AlertTransportConfigTest extends TestCase
{
    use RefreshDatabase;

    public function testRowsRoundTripAndCanBeCleared(): void
    {
        Gate::before(fn (?User $user) => true);
        $result = $this->saveForm(['alertmanager-labels-rows' => [['name' => 'team', 'value' => 'network']]]);
        self::assertSame('ok', $result['status'], json_encode($result));
        $transport = AlertTransport::firstOrFail();
        self::assertSame(['team' => 'network'], $transport->transport_config['alertmanager-labels']);

        $result = $this->saveForm(['transport_id' => $transport->transport_id, 'alertmanager-labels-rows' => '']);
        self::assertSame('ok', $result['status'], json_encode($result));
        self::assertSame([], $transport->fresh()->transport_config['alertmanager-labels']);
    }

    public function testInvalidRowsDoNotChangeExistingTransport(): void
    {
        Gate::before(fn (?User $user) => true);
        $this->saveForm([]);
        $transport = AlertTransport::firstOrFail();
        $result = $this->saveForm([
            'transport_id' => $transport->transport_id,
            'name' => 'Changed name',
            'alertmanager-labels-rows' => [['name' => 'team', 'value' => 'a'], ['name' => 'team', 'value' => 'b']],
        ]);
        self::assertSame('error', $result['status']);
        self::assertSame('Test transport', $transport->fresh()->transport_name);
    }

    public function testInvalidCreateDoesNotLeaveAnEmptyTransport(): void
    {
        Gate::before(fn (?User $user) => true);
        $result = $this->saveForm(['alertmanager-labels-rows' => [['name' => 'bad-name', 'value' => 'a']]]);
        self::assertSame('error', $result['status']);
        self::assertSame(0, AlertTransport::count());
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function saveForm(array $overrides): array
    {
        $vars = array_merge([
            'name' => 'Test transport',
            'transport-type' => 'alertmanager',
            'alertmanager-url' => 'https://am.example.net',
        ], $overrides);
        ob_start();
        try {
            include base_path('includes/html/forms/alert-transports.inc.php');

            return json_decode(ob_get_contents(), true, flags: JSON_THROW_ON_ERROR);
        } finally {
            ob_end_clean();
        }
    }
}
