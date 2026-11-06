<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\User;
use App\Models\UserPref;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Js;
use LibreNMS\Authentication\TwoFactor;
use LibreNMS\Tests\TestCase;
use OTPHP\HOTP;

final class TwoFactorControllerTest extends TestCase
{
    use DatabaseTransactions;

    private const string KEY = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

    // 5 seconds into a 30 second time step
    private const int NOW = 1_700_000_015;

    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::set('twofactor', true);
        LibrenmsConfig::set('twofactor_lock', 0);
        $this->travelTo(Carbon::createFromTimestamp(self::NOW));
    }

    /**
     * @param  array<string, mixed>|false|null  $twofactor
     */
    private function user(array|false|null $twofactor = null): User
    {
        $user = User::factory()->create(['enabled' => 1]);
        if ($twofactor !== null) {
            UserPref::setPref($user, 'twofactor', $twofactor);
        }

        return $user;
    }

    /**
     * @param  int|false  $counter  false for time based
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function settings(int|false $counter = false, array $extra = []): array
    {
        return array_merge(TwoFactor::create($counter !== false), ['key' => self::KEY, 'counter' => $counter], $extra);
    }

    private function counterCode(int $counter): string
    {
        return HOTP::createFromSecret(self::KEY)->at($counter);
    }

    /**
     * The code for the time step $offset steps from now
     */
    private function timeCode(int $offset = 0): string
    {
        return $this->counterCode(intdiv(self::NOW, 30) + $offset);
    }

    /**
     * The QR code script as rendered in the page
     */
    private function qrScript(string $type, string $label, string $counter = ''): string
    {
        $uri = "otpauth://$type/LibreNMS%3A" . rawurlencode($label) . '?' . ($counter === '' ? '' : "counter=$counter&") . 'issuer=LibreNMS&secret=' . self::KEY;

        return '$("#twofactorqr").qrcode(' . Js::from(['text' => $uri])->toHtml() . ');';
    }

    /**
     * @return array<string, mixed>|null
     */
    private function storedSettings(User $user): ?array
    {
        return UserPref::getPref($user, 'twofactor');
    }

    public function testUnverifiedUserIsRedirectedToTwoFactor(): void
    {
        $this->actingAs($this->user($this->settings()))
            ->get('/preferences')
            ->assertRedirect('/2fa');
    }

    public function testUserWithoutTwoFactorIsNotRedirected(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->get(route('2fa.form'))
            ->assertRedirect();

        $this->actingAs($user)
            ->get('/preferences')
            ->assertOk();
    }

    public function testAddShowsTimeBasedQrCode(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->withSession(['twofactoradd' => $this->settings()])
            ->get(route('2fa.form'))
            ->assertOk()
            ->assertSee($this->qrScript('totp', $user->email), false);
    }

    public function testAddShowsCounterBasedQrCodeStartingAtZero(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->withSession(['twofactoradd' => $this->settings(0)])
            ->get(route('2fa.form'))
            ->assertOk()
            ->assertSee($this->qrScript('hotp', $user->email, '0'), false);
    }

    public function testQrCodeLabelFallsBackToUsername(): void
    {
        $user = $this->user();
        $user->email = '';
        $user->save();

        $this->actingAs($user)
            ->withSession(['twofactoradd' => $this->settings()])
            ->get(route('2fa.form'))
            ->assertOk()
            ->assertSee($this->qrScript('totp', $user->username), false);
    }

    public function testPreferencesShowsQrCodeWithServerCounter(): void
    {
        $user = $this->user($this->settings(12));

        $this->actingAs($user)
            ->withSession(['twofactor' => true])
            ->get('/preferences')
            ->assertOk()
            ->assertSee($this->qrScript('hotp', $user->email, '12'), false)
            ->assertDontSee('&amp;secret=', false);
    }

    public function testAddTimeBased(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->post(route('2fa.add'), ['twofactortype' => 'time'])
            ->assertRedirect()
            ->assertSessionHas('twofactoradd', fn ($settings) => $settings['counter'] === false);

        $this->assertNull($this->storedSettings($user));
    }

    public function testAddCounterBasedStartsAtZero(): void
    {
        $this->actingAs($this->user())
            ->post(route('2fa.add'), ['twofactortype' => 'counter'])
            ->assertSessionHas('twofactoradd', fn ($settings) => $settings['counter'] === 0);
    }

    public function testAddRejectsUnknownType(): void
    {
        $this->actingAs($this->user())
            ->post(route('2fa.add'), ['twofactortype' => 'sms'])
            ->assertSessionHasErrors('twofactortype')
            ->assertSessionMissing('twofactoradd');
    }

    public function testAddIsSavedAfterFirstValidCode(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->withSession(['twofactoradd' => $this->settings(0)])
            ->post(route('2fa.verify'), ['twofactor' => $this->counterCode(0)])
            ->assertRedirect()
            ->assertSessionHas('twofactor', true)
            ->assertSessionMissing('twofactoradd');

        $this->assertSame(1, $this->storedSettings($user)['counter']);
    }

    public function testTimeBasedCurrentCode(): void
    {
        $user = $this->user($this->settings());

        $this->actingAs($user)
            ->post(route('2fa.verify'), ['twofactor' => $this->timeCode()])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('twofactor', true);

        $this->assertSame(0, $this->storedSettings($user)['fails']);
    }

    public function testTimeBasedAdjacentCodeWithinLeeway(): void
    {
        $user = $this->user($this->settings());

        $this->actingAs($user)
            ->post(route('2fa.verify'), ['twofactor' => $this->timeCode(-1)])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('twofactor', true);
    }

    public function testTimeBasedOldCodeIsRejected(): void
    {
        $user = $this->user($this->settings());

        // 5 minutes old, the previous implementation accepted codes up to ~75 minutes old
        $this->actingAs($user)
            ->post(route('2fa.verify'), ['twofactor' => $this->timeCode(-10)])
            ->assertRedirect(route('2fa.form'))
            ->assertSessionHasErrors()
            ->assertSessionMissing('twofactor');

        $this->assertSame(1, $this->storedSettings($user)['fails']);
    }

    public function testTimeBasedFutureCodeIsRejected(): void
    {
        $this->actingAs($this->user($this->settings()))
            ->post(route('2fa.verify'), ['twofactor' => $this->timeCode(10)])
            ->assertSessionHasErrors()
            ->assertSessionMissing('twofactor');
    }

    public function testCounterBasedExactCodeAdvancesCounter(): void
    {
        $user = $this->user($this->settings(5));

        $this->actingAs($user)
            ->post(route('2fa.verify'), ['twofactor' => $this->counterCode(5)])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('twofactor', true);

        $this->assertSame(6, $this->storedSettings($user)['counter']);
    }

    public function testCounterBasedLookaheadResyncsCounter(): void
    {
        $user = $this->user($this->settings(5));

        $this->actingAs($user)
            ->post(route('2fa.verify'), ['twofactor' => $this->counterCode(8)])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('twofactor', true);

        $this->assertSame(9, $this->storedSettings($user)['counter']);
    }

    public function testCounterBasedUsedCodeIsRejected(): void
    {
        $user = $this->user($this->settings(5));

        $this->actingAs($user)
            ->post(route('2fa.verify'), ['twofactor' => $this->counterCode(4)])
            ->assertSessionHasErrors()
            ->assertSessionMissing('twofactor');

        $this->assertSame(5, $this->storedSettings($user)['counter']);
    }

    public function testCounterBasedCodeBeyondLookaheadIsRejected(): void
    {
        $this->actingAs($this->user($this->settings(5)))
            ->post(route('2fa.verify'), ['twofactor' => $this->counterCode(10)])
            ->assertSessionHasErrors()
            ->assertSessionMissing('twofactor');
    }

    public function testLockoutAfterThreeFailures(): void
    {
        $user = $this->user($this->settings());

        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($user)->post(route('2fa.verify'), ['twofactor' => '000000']);
        }
        $this->assertSame(3, $this->storedSettings($user)['fails']);

        // a valid code is refused while locked
        $this->actingAs($user)
            ->post(route('2fa.verify'), ['twofactor' => $this->timeCode()])
            ->assertSessionHasErrors()
            ->assertSessionMissing('twofactor');
    }

    public function testLockoutExpires(): void
    {
        LibrenmsConfig::set('twofactor_lock', 60);
        $user = $this->user($this->settings(false, ['fails' => 3, 'last' => time() - 61]));

        $this->actingAs($user)
            ->post(route('2fa.verify'), ['twofactor' => $this->timeCode()])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('twofactor', true);

        $this->assertSame(0, $this->storedSettings($user)['fails']);
    }

    public function testLockoutWithinWaitTime(): void
    {
        LibrenmsConfig::set('twofactor_lock', 60);
        $user = $this->user($this->settings(false, ['fails' => 3, 'last' => time()]));

        $this->actingAs($user)
            ->post(route('2fa.verify'), ['twofactor' => $this->timeCode()])
            ->assertSessionHasErrors()
            ->assertSessionMissing('twofactor');
    }

    public function testRemoveRequiresValidCode(): void
    {
        $user = $this->user($this->settings());

        $this->actingAs($user)
            ->withSession(['twofactor' => true])
            ->post(route('2fa.remove'))
            ->assertSessionHas('twofactorremove', true);

        $this->actingAs($user)
            ->withSession(['twofactorremove' => true])
            ->post(route('2fa.verify'), ['twofactor' => $this->timeCode()])
            ->assertRedirect('preferences');

        $this->assertNull($this->storedSettings($user));
    }
}
