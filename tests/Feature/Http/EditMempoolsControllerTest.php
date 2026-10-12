<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Mempool;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;

final class EditMempoolsControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    private function user(): User
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        return $user;
    }

    public function testAdminCanViewMempoolsPage(): void
    {
        $device = Device::factory()->create();
        Mempool::factory()->for($device)->create([
            'mempool_descr' => 'Physical <b>Memory</b>',
            'mempool_perc' => 42,
            'mempool_perc_warn' => 85,
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.edit.mempools', $device))
            ->assertOk()
            ->assertSee('Physical &lt;b&gt;Memory&lt;/b&gt;', false)
            ->assertDontSee('Physical <b>Memory</b>', false)
            ->assertSee('42%')
            ->assertSee('value="85"', false);
    }

    public function testNullWarnThresholdRendersEmpty(): void
    {
        $device = Device::factory()->create();
        Mempool::factory()->for($device)->create(['mempool_perc_warn' => null]);

        $this->actingAs($this->admin())
            ->get(route('device.edit.mempools', $device))
            ->assertOk()
            ->assertSee('value=""', false);
    }

    public function testUserCannotViewMempoolsPage(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->user())
            ->get(route('device.edit.mempools', $device))
            ->assertForbidden();
    }

    public function testAdminCanUpdateWarnThreshold(): void
    {
        $device = Device::factory()->create();
        $mempool = Mempool::factory()->for($device)->create(['mempool_perc_warn' => 75]);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.mempools.update', [$device, $mempool]), ['mempool_perc_warn' => '90'])
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $this->assertSame(90, $mempool->fresh()->mempool_perc_warn);
    }

    public function testEmptyWarnThresholdClearsIt(): void
    {
        $device = Device::factory()->create();
        $mempool = Mempool::factory()->for($device)->create(['mempool_perc_warn' => 75]);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.mempools.update', [$device, $mempool]), ['mempool_perc_warn' => ''])
            ->assertOk();

        $this->assertNull($mempool->fresh()->mempool_perc_warn);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function invalidThresholds(): array
    {
        return [
            'non-numeric' => ['abc'],
            'negative' => [-1],
            'over 100' => [101],
        ];
    }

    #[DataProvider('invalidThresholds')]
    public function testInvalidWarnThresholdIsRejected(mixed $value): void
    {
        $device = Device::factory()->create();
        $mempool = Mempool::factory()->for($device)->create(['mempool_perc_warn' => 75]);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.mempools.update', [$device, $mempool]), ['mempool_perc_warn' => $value])
            ->assertUnprocessable();

        $this->assertSame(75, $mempool->fresh()->mempool_perc_warn);
    }

    public function testMempoolMustBelongToDevice(): void
    {
        $device = Device::factory()->create();
        $otherMempool = Mempool::factory()->create(['mempool_perc_warn' => 75]);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.mempools.update', [$device, $otherMempool]), ['mempool_perc_warn' => 90])
            ->assertNotFound();

        $this->assertSame(75, $otherMempool->fresh()->mempool_perc_warn);
    }

    public function testUserCannotUpdateWarnThreshold(): void
    {
        $device = Device::factory()->create();
        $mempool = Mempool::factory()->for($device)->create(['mempool_perc_warn' => 75]);

        $this->actingAs($this->user())
            ->postJson(route('device.edit.mempools.update', [$device, $mempool]), ['mempool_perc_warn' => 90])
            ->assertForbidden();

        $this->assertSame(75, $mempool->fresh()->mempool_perc_warn);
    }
}
