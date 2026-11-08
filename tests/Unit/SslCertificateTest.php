<?php

namespace LibreNMS\Tests\Unit;

use App\Facades\LibrenmsConfig;
use App\Models\SslCertificate;
use Illuminate\Support\Carbon;
use LibreNMS\Tests\TestCase;

class SslCertificateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::set('ssl_certificates.days_until_expiry_warning', 15);
        LibrenmsConfig::set('ssl_certificates.days_until_expiry_danger', 0);
    }

    private function makeCertificate(Carbon $originalValidTo, Carbon $newValidTo, int $originalDays, int $newDays): SslCertificate
    {
        $certificate = new SslCertificate;
        $certificate->forceFill([
            'host' => 'example.com',
            'port' => 443,
            'valid_to' => $originalValidTo,
            'days_until_expiry' => $originalDays,
        ]);
        $certificate->syncOriginal();

        $certificate->valid_to = $newValidTo;
        $certificate->days_until_expiry = $newDays;

        return $certificate;
    }

    public function testDailyDecrementIsNotTracked(): void
    {
        $cases = [
            [112, 111],
            [31, 30],
            [30, 29],
        ];

        foreach ($cases as [$originalDays, $newDays]) {
            $validTo = Carbon::now()->addDays($newDays);
            $certificate = $this->makeCertificate($validTo, $validTo, $originalDays, $newDays);

            $this->assertSame('', $certificate->getTrackedChanges(), "days_until_expiry {$originalDays} → {$newDays}");
        }
    }

    public function testChangeWithinWarningWindowIsNotTracked(): void
    {
        $validTo = Carbon::now()->addDays(14);
        $certificate = $this->makeCertificate($validTo, $validTo, 15, 14);

        $this->assertSame('', $certificate->getTrackedChanges());
    }

    public function testEnteringWarningWindowIsTracked(): void
    {
        $validTo = Carbon::now()->addDays(15);
        $certificate = $this->makeCertificate($validTo, $validTo, 16, 15);

        $this->assertSame('days_until_expiry (16 → 15)', $certificate->getTrackedChanges());
    }

    public function testExpiryIsTracked(): void
    {
        $validTo = Carbon::now();
        $certificate = $this->makeCertificate($validTo, $validTo, 1, 0);

        $this->assertSame('days_until_expiry (1 → 0)', $certificate->getTrackedChanges());

        $certificate = $this->makeCertificate($validTo, $validTo, 0, -1);

        $this->assertSame('', $certificate->getTrackedChanges());
    }

    public function testRenewalTracksValidToButNotDays(): void
    {
        $originalValidTo = Carbon::now()->subDays(3);
        $newValidTo = Carbon::now()->addDays(360);

        $certificate = $this->makeCertificate($originalValidTo, $newValidTo, -3, 360);

        $changes = $certificate->getTrackedChanges();

        $this->assertStringContainsString('valid_to', $changes);
        $this->assertStringNotContainsString('days_until_expiry', $changes);
    }

    public function testIsWithinWarningWindow(): void
    {
        $daysUntilExpiryToExpectations = [
            [16, false],
            [15, true],
            [1, true],
            [0, true],
            [-1, true],
        ];

        foreach ($daysUntilExpiryToExpectations as [$days, $expected]) {
            $certificate = new SslCertificate;
            $certificate->days_until_expiry = $days;

            $this->assertSame($expected, $certificate->isWithinWarningWindow(), "isWithinWarningWindow() with days_until_expiry {$days}");
        }

        $certificate = new SslCertificate;
        $certificate->days_until_expiry = null;

        $this->assertFalse($certificate->isWithinWarningWindow());
    }
}
