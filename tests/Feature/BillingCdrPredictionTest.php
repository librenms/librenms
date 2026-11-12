<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Bill;
use App\Models\BillHistory;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use LibreNMS\Billing;
use LibreNMS\Tests\TestCase;

class BillingCdrPredictionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        [$from] = Billing::getDates(1);
        Carbon::setTestNow(Carbon::parse($from)->addHours(12));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function testForecastUsesTheLastThreeCompleteCycles(): void
    {
        $bill = $this->cdrBill();
        $this->history($bill, 4, 10000);
        $this->history($bill, 3, 100);
        $this->history($bill, 2, 180);
        $this->history($bill, 1, 250);
        $this->history($bill, 0, 10000);
        $this->history($this->cdrBill(), 1, 10000);

        $this->assertEqualsWithDelta(326.6666667, Billing::getPredictedCdrUsage($bill), 0.0001);
    }

    public function testForecastTreatsMissingCyclesAsConsecutiveAndIgnoresQuotaHistory(): void
    {
        $bill = $this->cdrBill();
        $this->history($bill, 3, 100);
        $this->history($bill, 2, 10000, 'Quota');
        $this->history($bill, 1, 200);

        $this->assertEqualsWithDelta(300, Billing::getPredictedCdrUsage($bill), 0.0001);
    }

    public function testForecastTreatsDifferentEndTimestampsAsSeparateRecords(): void
    {
        $bill = $this->cdrBill();
        $this->history($bill, 3, 100);
        $this->history($bill, 2, 200);
        $earlier = $this->history($bill, 1, 999);
        $earlier->bill_dateto = Carbon::parse($earlier->bill_dateto)->subDay();
        $earlier->save();
        $this->history($bill, 1, 300);
        $this->history($bill, 0, 10000);

        $this->assertEqualsWithDelta(749.5, Billing::getPredictedCdrUsage($bill), 0.0001);
    }

    public function testForecastUsesNewestRecordForEachEndTimestamp(): void
    {
        $bill = $this->cdrBill();
        $this->history($bill, 3, 100);
        $this->history($bill, 2, 10000);
        $this->history($bill, 2, 200);
        $this->history($bill, 1, 10000);
        $this->history($bill, 1, 300);

        $this->assertSame(400.0, Billing::getPredictedCdrUsage($bill));
    }

    public function testForecastPreservesZeroRateForDuplicateEndTimestamp(): void
    {
        $bill = $this->cdrBill();
        $this->history($bill, 1, 10000);
        $this->history($bill, 1, 0);

        $this->assertSame(0.0, Billing::getPredictedCdrUsage($bill));
    }

    public function testForecastTreatsOldestAvailableRecordsAsConsecutive(): void
    {
        $bill = $this->cdrBill();
        $this->history($bill, 3, 100);
        $this->history($bill, 2, 200);

        $this->assertSame(300.0, Billing::getPredictedCdrUsage($bill));
    }

    public function testHistoryMustEndBeforeTheCurrentTimeWhenTheBillingStartIsInTheFuture(): void
    {
        $bill = $this->cdrBill();
        $future = $this->history($bill, 1, 1000);
        $currentStart = Carbon::parse(Billing::getDates($bill->bill_day)[0]);
        $clock = $currentStart->copy()->subDays(2);
        $future->bill_dateto = $clock->copy()->addDay();
        $future->save();
        $past = $this->history($bill, 2, 200);
        $past->bill_dateto = $clock->copy()->subDay();
        $past->save();
        Carbon::setTestNow($clock);

        $this->assertSame(200.0, Billing::getPredictedCdrUsage($bill));
    }

    public function testHistoryMustEndBeforeTheCurrentBillingStart(): void
    {
        $bill = $this->cdrBill();
        $overlap = $this->history($bill, 1, 1000);
        $currentStart = Carbon::parse(Billing::getDates($bill->bill_day)[0]);
        $overlap->bill_dateto = $currentStart;
        $overlap->save();
        $this->history($bill, 2, 200);
        Carbon::setTestNow($currentStart->copy()->addDays(10));
        [$from, $to] = Billing::getDates($bill->bill_day);
        $days = (int) Carbon::parse($from)->diffInDays(Carbon::parse($to)->addSecond());

        $this->assertEqualsWithDelta(200 * (1 - 10 / $days) + 75 * 10 / $days, Billing::getPredictedCdrUsage($bill), 0.0001);
    }

    public function testSingleCycleAndZeroRateAreValidHistory(): void
    {
        $bill = $this->cdrBill();
        $this->history($bill, 2, 120);

        $this->assertSame(120.0, Billing::getPredictedCdrUsage($bill));

        $this->history($bill, 1, 0);

        $this->assertSame(0.0, Billing::getPredictedCdrUsage($bill));

        [$from, $to] = Billing::getDates($bill->bill_day);
        $start = Carbon::parse($from);
        $days = (int) $start->diffInDays(Carbon::parse($to)->addSecond());
        Carbon::setTestNow($start->copy()->addDays(10));
        $this->assertEqualsWithDelta(75 * 10 / $days, Billing::getPredictedCdrUsage($bill), 0.0001);
    }

    public function testTwoAdjacentCyclesExtrapolateTheirTrend(): void
    {
        $bill = $this->cdrBill();
        $this->history($bill, 2, 100, 'cdr');
        $this->history($bill, 1, 150);

        $this->assertSame(200.0, Billing::getPredictedCdrUsage($bill));
    }

    public function testForecastDoesNotGoBelowZero(): void
    {
        $bill = $this->cdrBill();
        $this->history($bill, 3, 300);
        $this->history($bill, 2, 150);
        $this->history($bill, 1, 0);

        $this->assertSame(0.0, Billing::getPredictedCdrUsage($bill));
    }

    public function testForecastWeightsCurrentRateByElapsedBillingDays(): void
    {
        $bill = $this->cdrBill();
        $this->history($bill, 1, 300);
        [$from, $to] = Billing::getDates($bill->bill_day);
        $start = Carbon::parse($from);
        $days = (int) $start->diffInDays(Carbon::parse($to)->addSecond());
        $halfway = intdiv($days, 2);

        $this->assertSame(300.0, Billing::getPredictedCdrUsage($bill));

        Carbon::setTestNow($start->copy()->addDays($halfway)->addHours(12));
        $this->assertEqualsWithDelta(300 * (1 - $halfway / $days) + 75 * $halfway / $days, Billing::getPredictedCdrUsage($bill), 0.0001);

        Carbon::setTestNow($start->copy()->addDays($days - 1)->endOfDay());
        $this->assertEqualsWithDelta(300 / $days + 75 * ($days - 1) / $days, Billing::getPredictedCdrUsage($bill), 0.0001);

        Carbon::setTestNow($start->copy()->addDays($days));
        $this->assertSame(75.0, Billing::getPredictedCdrUsage($bill));
    }

    public function testWeightFollowsBillingCycleRatherThanCalendarMonth(): void
    {
        $bill = Bill::factory()->create(['bill_type' => 'cdr', 'bill_day' => 15, 'rate_95th' => 100]);
        $this->history($bill, 1, 300);
        [$from, $to] = Billing::getDates($bill->bill_day);
        $start = Carbon::parse($from);
        $days = (int) $start->diffInDays(Carbon::parse($to)->addSecond());
        $elapsed = 10;

        Carbon::setTestNow($start->copy()->addDays($elapsed));
        $this->assertEqualsWithDelta(300 * (1 - $elapsed / $days) + 100 * $elapsed / $days, Billing::getPredictedCdrUsage($bill), 0.0001);
    }

    public function testForecastFallsBackToCurrentRateWithoutCdrHistory(): void
    {
        $bill = $this->cdrBill();
        $this->assertSame(75.0, Billing::getPredictedCdrUsage($bill));

        $this->history($bill, 1, 900, 'Quota');
        $this->history($bill, 4, 10000);

        $this->assertSame(75.0, Billing::getPredictedCdrUsage($bill));

        [$from] = Billing::getDates($bill->bill_day);
        Carbon::setTestNow(Carbon::parse($from)->addDays(10));
        $this->assertSame(75.0, Billing::getPredictedCdrUsage($bill));
    }

    public function testForecastRejectsQuotaBills(): void
    {
        $bill = Bill::factory()->create(['bill_type' => 'quota']);

        $this->expectException(InvalidArgumentException::class);
        Billing::getPredictedCdrUsage($bill);
    }

    private function cdrBill(): Bill
    {
        return Bill::factory()->create([
            'bill_type' => 'cdr',
            'bill_day' => 1,
            'rate_95th' => 75,
        ]);
    }

    private function history(Bill $bill, int $offset, int $rate, string $type = 'CDR'): BillHistory
    {
        [$from, $to] = Billing::getDates($bill->bill_day, $offset);

        return BillHistory::create([
            'bill_id' => $bill->bill_id,
            'bill_datefrom' => new \DateTime($from)->format('Y-m-d H:i:s'),
            'bill_dateto' => new \DateTime($to)->format('Y-m-d H:i:s'),
            'bill_type' => $type,
            'bill_allowed' => 0,
            'bill_used' => $rate,
            'bill_overuse' => 0,
            'bill_percent' => 0,
            'rate_95th_in' => $rate,
            'rate_95th_out' => 0,
            'rate_95th' => $rate,
            'dir_95th' => 'in',
            'rate_average' => 0,
            'rate_average_in' => 0,
            'rate_average_out' => 0,
            'traf_in' => 0,
            'traf_out' => 0,
            'traf_total' => 0,
            'bill_peak_in' => 0,
            'bill_peak_out' => 0,
        ]);
    }
}
