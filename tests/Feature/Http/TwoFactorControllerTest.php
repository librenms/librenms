<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\User;
use App\Models\UserPref;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Js;
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

    private function user(array|false|null $twofactor = null): User
    {
        $user = User::factory()->create(['enabled' => 1]);
        if ($twofactor !== null) {
            UserPref::setPref($user, 'twofactor', $twofactor);
        }

        return $user;
    }

    private function timeSettings(array $extra = []): array
    {
        return array_merge(['key' => self::KEY, 'fails' => 0, 'last' => 0, 'counter' => false], $extra);
    }

    private function counterSettings(int $counter, array $extra = []): array
    {
        return array_merge(['key' => self::KEY, 'fails' => 0, 'last' => 0, 'counter' => $counter], $extra);
    }

    /**
     * The code for the time step $offset steps from now
     */
    private function timeCode(int $offset = 0): string
    {
        return HOTP::createFromSecret(self::KEY)->at(intdiv(self::NOW, 30) + $offset);
    }

    private function counterCode(int $counter): string
    {
        return HOTP::createFromSecret(self::KEY)->at($counter);
    }

    /**
     * The QR code script argument as rendered in the page
     */
    private function qrScript(string $uri): string
    {
        return '$("#twofactorqr").qrcode(' . Js::from(['text' => $uri])->toHtml() . ');';
    }

    private function storedSettings(User $user): array
    {
        return UserPref::getPref($user, 'twofactor');
    }

    public function testUnverifiedUserIsRedirectedToTwoFactor(): void
    {
        $this->actingAs($this->user($this->timeSettings()))
            ->get('/preferences')
            ->assertRedirect('/2fa');
    }

    public function testUserWithoutTwoFactorIsNotRedirected(): void
    {
        $this->actingAs($this->user())
            ->get(route('2fa.form'))
            ->assertRedirect();

        $this->actingAs($this->user())
            ->get('/preferences')
            ->assertOk();
    }

    public function testAddShowsTimeBasedQrCode(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->withSession(['twofactoradd' => $this->timeSettings()])
            ->get(route('2fa.form'))
            ->assertOk()
            ->assertSee($this->qrScript('otpauth://totp/LibreNMS%3A' . rawurlencode($user->email) . '?issuer=LibreNMS&secret=' . self::KEY), false);
    }

    public function testAddShowsCounterBasedQrCodeStartingAtZero(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->withSession(['twofactoradd' => $this->counterSettings(0)])
            ->get(route('2fa.form'))
            ->assertOk()
            ->assertSee($this->qrScript('otpauth://hotp/LibreNMS%3A' . rawurlencode($user->email) . '?counter=0&issuer=LibreNMS&secret=' . self::KEY), false);
    }

    public function testQrCodeLabelFallsBackToUsername(): void
    {
        $user = $this->user();
        $user->email = '';
        $user->save();

        $this->actingAs($user)
            ->withSession(['twofactoradd' => $this->timeSettings()])
            ->get(route('2fa.form'))
            ->assertOk()
            ->assertSee($this->qrScript('otpauth://totp/LibreNMS%3A' . rawurlencode($user->username) . '?issuer=LibreNMS&secret=' . self::KEY), false);
    }

    public function testPreferencesShowsQrCodeWithServerCounter(): void
    {
        $user = $this->user($this->counterSettings(12));

        $this->actingAs($user)
            ->withSession(['twofactor' => true])
            ->get('/preferences')
            ->assertOk()
            ->assertSee($this->qrScript('otpauth://hotp/LibreNMS%3A' . rawurlencode($user->email) . '?counter=12&issuer=LibreNMS&secret=' . self::KEY), false)
            ->assertDontSee('&amp;secret=', false);
    }

    public function testAddTimeBased(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->post(route('2fa.add'), ['twofactortype' => 'time'])
            ->assertRedirect()
            ->assertSessionHas('twofactoradd', fn ($settings) => $settings['counter'] === false);

        $this->assertNull(UserPref::getPref($user, 'twofactor'));
    }

    public function testAddCounterBasedStartsAtZero(): void
    {
        $this->actingAs($this->user())
            ->post(route('2fa.add'), ['twofactortype' => 'counter'])
            ->assertSessionHas('twofactoradd', fn ($settings) => $settings['counter'] === 0);
    }

    public function testAddIsSavedAfterFirstValidCode(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->withSession(['twofactoradd' => $this->counterSettings(0)])
            ->post(route('2fa.verify'), ['twofactor' => $this->counterCode(0)])
            ->assertRedirect()
            ->assertSessionHas('twofactor', true)
            ->assertSessionMissing('twofactoradd');

        $this->assertSame(1, $this->storedSettings($user)['counter']);
    }

    public function testTimeBasedCurrentCode(): void
    {
        $user = $this->user($this->timeSettings());

        $this->actingAs($user)
            ->post(route('2fa.verify'), ['twofactor' => $this->timeCode()])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('twofactor', true);

        $this->assertSame(0, $this->storedSettings($user)['fails']);
    }

    public function testTimeBasedAdjacentCodeWithinLeeway(): void
    {
        $user = $this->user($this->timeSettings());

        $this->actingAs($user)
            ->post(route('2fa.verify'), ['twofactor' => $this->timeCode(-1)])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('twofactor', true);
    }

    public function testTimeBasedOldCodeIsRejected(): void
    {
        $user = $this->user($this->timeSettings());

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
        $this->actingAs($this->user($this->timeSettings()))
            ->post(route('2fa.verify'), ['twofactor' => $this->timeCode(10)])
            ->assertSessionHasErrors()
            ->assertSessionMissing('twofactor');
    }

    public function testCounterBasedExactCodeAdvancesCounter(): void
    {
        $user = $this->user($this->counterSettings(5));

        $this->actingAs($user)
            ->post(route('2fa.verify'), ['twofactor' => $this->counterCode(5)])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('twofactor', true);

        $this->assertSame(6, $this->storedSettings($user)['counter']);
    }

    public function testCounterBasedLookaheadResyncsCounter(): void
    {
        $user = $this->user($this->counterSettings(5));

        $this->actingAs($user)
            ->post(route('2fa.verify'), ['twofactor' => $this->counterCode(8)])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('twofactor', true);

        $this->assertSame(9, $this->storedSettings($user)['counter']);
    }

    public function testCounterBasedUsedCodeIsRejected(): void
    {
        $user = $this->user($this->counterSettings(5));

        $this->actingAs($user)
            ->post(route('2fa.verify'), ['twofactor' => $this->counterCode(4)])
            ->assertSessionHasErrors()
            ->assertSessionMissing('twofactor');

        $this->assertSame(5, $this->storedSettings($user)['counter']);
    }

    public function testCounterBasedCodeBeyondLookaheadIsRejected(): void
    {
        $this->actingAs($this->user($this->counterSettings(5)))
            ->post(route('2fa.verify'), ['twofactor' => $this->counterCode(10)])
            ->assertSessionHasErrors()
            ->assertSessionMissing('twofactor');
    }

    public function testLockoutAfterThreeFailures(): void
    {
        $user = $this->user($this->timeSettings());

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
        $user = $this->user($this->timeSettings(['fails' => 3, 'last' => time() - 61]));

        $this->actingAs($user)
            ->post(route('2fa.verify'), ['twofactor' => $this->timeCode()])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('twofactor', true);

        $this->assertSame(0, $this->storedSettings($user)['fails']);
    }

    public function testLockoutWithinWaitTime(): void
    {
        LibrenmsConfig::set('twofactor_lock', 60);
        $user = $this->user($this->timeSettings(['fails' => 3, 'last' => time()]));

        $this->actingAs($user)
            ->post(route('2fa.verify'), ['twofactor' => $this->timeCode()])
            ->assertSessionHasErrors()
            ->assertSessionMissing('twofactor');
    }

    public function testRemoveRequiresValidCode(): void
    {
        $user = $this->user($this->timeSettings());

        $this->actingAs($user)
            ->withSession(['twofactor' => true])
            ->post(route('2fa.remove'))
            ->assertSessionHas('twofactorremove', true);

        $this->actingAs($user)
            ->withSession(['twofactorremove' => true])
            ->post(route('2fa.verify'), ['twofactor' => $this->timeCode()])
            ->assertRedirect('preferences');

        $this->assertNull(UserPref::getPref($user, 'twofactor'));
    }
}
