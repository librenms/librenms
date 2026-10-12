<?php

namespace LibreNMS\Tests\Feature;

use App\Jobs\DispatchPollingWork;
use App\Jobs\PollDevice;
use App\Models\User;
use App\Providers\HorizonServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Horizon\JobPayload;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\ModuleList;
use PHPUnit\Framework\Attributes\DataProvider;

class HorizonTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard(): void
    {
        config(['queue.default' => 'redis']);
        $user = User::factory()->admin()->create(['enabled' => true]);

        $this->actingAs($user)->get('/horizon')->assertOk();
    }

    public function test_dashboard_unavailable_without_redis(): void
    {
        config(['queue.default' => 'database']);
        $user = User::factory()->admin()->create(['enabled' => true]);

        $this->actingAs($user)->get('/horizon')->assertForbidden();
    }

    public function test_non_admin_cannot_view_dashboard(): void
    {
        $user = User::factory()->read()->create(['enabled' => true]);

        $this->actingAs($user)->get('/horizon')->assertForbidden();
    }

    public function test_non_admin_cannot_view_dashboard_in_local_environment(): void
    {
        $this->app['env'] = 'local';
        $user = User::factory()->read()->create(['enabled' => true]);

        $this->actingAs($user)->get('/horizon')->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/horizon')->assertRedirect();
    }

    public function test_performance_page_shows_queue_graphs_when_using_redis(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);

        config(['queue.default' => 'redis']);
        $this->actingAs($user)->get(route('poller.performance'))
            ->assertOk()
            ->assertSee('global_horizon_throughput')
            ->assertSee('global_horizon_runtime');

        config(['queue.default' => 'database']);
        $this->actingAs($user)->get(route('poller.performance'))
            ->assertOk()
            ->assertDontSee('global_horizon_throughput');
    }

    public function test_dispatcher_is_silenced(): void
    {
        $payload = (new JobPayload('{}'))->prepare(new DispatchPollingWork(poll: false, discover: false));

        $this->assertTrue($payload->isSilenced());
    }

    public function test_poll_device_is_tagged_and_not_silenced(): void
    {
        $payload = (new JobPayload('{}'))->prepare(new PollDevice(42, ModuleList::fromUserOverrides([])));

        $this->assertFalse($payload->isSilenced());
        $this->assertSame(['poll', 'device:42'], $payload->tags());
    }

    public function test_config_is_not_loaded_before_the_app_has_booted(): void
    {
        $app = require base_path('bootstrap/app.php');
        $loadedEarly = null;
        // runs before booted callbacks registered by providers, database settings are not available before this
        $app->booted(function ($app) use (&$loadedEarly): void {
            $loadedEarly = $app->resolved('librenms-config');
        });

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        $this->assertFalse($loadedEarly, 'LibreNMS config was loaded during boot, it would be cached without database settings');
    }

    /**
     * @param  int[]  $groups
     * @param  string[]  $expected
     */
    #[DataProvider('queueNameProvider')]
    public function test_queue_names_match_dispatcher(string $prefix, array $groups, array $expected): void
    {
        $this->assertSame($expected, HorizonServiceProvider::queueNames($prefix, $groups));
    }

    /**
     * @return array<string, array{string, int[], string[]}>
     */
    public static function queueNameProvider(): array
    {
        return [
            'default group' => ['poll', [0], ['poll']],
            'single group' => ['discovery', [3], ['discovery-3']],
            'multiple groups' => ['poll', [0, 1, 2], ['poll', 'poll-1', 'poll-2']],
            'duplicates' => ['poll', [1, 1], ['poll-1']],
        ];
    }
}
