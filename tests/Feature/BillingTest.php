<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Bill;
use App\Models\BillData;
use App\Models\BillHistory;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use LibreNMS\Billing;
use LibreNMS\Tests\DBTestCase;

class BillingTest extends DBTestCase
{
    use RefreshDatabase;

    private int $start;

    protected function setUp(): void
    {
        parent::setUp();

        $this->start = (int) strtotime('2026-01-15 12:00:00');
    }

    public function testGetRatesTotalsAndAverages(): void
    {
        $bill = Bill::factory()->create();
        $this->addSample($bill, $this->start + 300, 300, 100, 1000);
        $this->addSample($bill, $this->start + 600, 300, 200, 2000);

        $rates = Billing::getRates($bill->bill_id, $this->dbDate($this->start), $this->dbDate($this->start + 600), 'agg');

        $this->assertEquals(3300, $rates['total_data']);
        $this->assertEquals(300, $rates['total_data_in']);
        $this->assertEquals(3000, $rates['total_data_out']);
        $this->assertEquals(44, $rates['rate_average']);
        $this->assertEquals(4, $rates['rate_average_in']);
        $this->assertEquals(40, $rates['rate_average_out']);
    }

    public function testGetRatesReturnsThe95thPercentileRates(): void
    {
        $bill = Bill::factory()->create();
        foreach (range(1, 100) as $n) {
            // 8n bps in and 16n bps out
            $this->addSample($bill, $this->start + $n * 300, 300, 300 * $n, 600 * $n);
        }

        $rates = Billing::getRates($bill->bill_id, $this->dbDate($this->start), $this->dbDate($this->start + 30000), 'agg');

        $this->assertEquals(752, $rates['rate_95th_in']);
        $this->assertEquals(1504, $rates['rate_95th_out']);
        $this->assertEquals(2256, $rates['rate_95th']);
        $this->assertEquals('agg', $rates['dir_95th']);
    }

    public function testGetRatesUsesTheHigherDirection(): void
    {
        $out = Bill::factory()->create();
        $in = Bill::factory()->create();
        foreach (range(1, 40) as $n) {
            $this->addSample($out, $this->start + $n * 300, 300, 300 * $n, 600 * $n);
            $this->addSample($in, $this->start + $n * 300, 300, 600 * $n, 300 * $n);
        }
        $from = $this->dbDate($this->start);
        $to = $this->dbDate($this->start + 12000);

        $rates = Billing::getRates($out->bill_id, $from, $to, 'max');
        $this->assertEquals('out', $rates['dir_95th']);
        $this->assertEquals($rates['rate_95th_out'], $rates['rate_95th']);
        $this->assertGreaterThan($rates['rate_95th_in'], $rates['rate_95th']);

        $rates = Billing::getRates($in->bill_id, $from, $to, 'max');
        $this->assertEquals('in', $rates['dir_95th']);
        $this->assertEquals($rates['rate_95th_in'], $rates['rate_95th']);
        $this->assertGreaterThan($rates['rate_95th_out'], $rates['rate_95th']);
    }

    public function testGetRatesCombinesSamplesInAFiveMinuteBucket(): void
    {
        $bill = Bill::factory()->create();
        $this->addSample($bill, $this->start + 10, 150, 300, 600);
        $this->addSample($bill, $this->start + 160, 150, 600, 1200);

        $rates = Billing::getRates($bill->bill_id, $this->dbDate($this->start), $this->dbDate($this->start + 299), 'agg');

        $this->assertEquals(24, $rates['rate_95th_in']);
        $this->assertEquals(48, $rates['rate_95th_out']);
        $this->assertEquals(72, $rates['rate_95th']);
    }

    public function testGetRatesWithNoData(): void
    {
        $bill = Bill::factory()->create();

        $rates = Billing::getRates($bill->bill_id, $this->dbDate($this->start), $this->dbDate($this->start + 300), 'max');

        $this->assertEquals(0, $rates['rate_95th']);
        $this->assertEquals('in', $rates['dir_95th']);
        $this->assertNull($rates['total_data']);
        $this->assertEquals(0, $rates['rate_average']);
    }

    public function testBillingPeriodExcludesStartAndIncludesEnd(): void
    {
        $bill = Bill::factory()->create();
        $this->addSample($bill, $this->start, 300, 100, 1000);
        $this->addSample($bill, $this->start + 300, 300, 200, 2000);
        $this->addSample($bill, $this->start + 600, 300, 300, 3000);
        $this->addSample($bill, $this->start + 900, 300, 400, 4000);
        $from = $this->dbDate($this->start);
        $to = $this->dbDate($this->start + 600);

        $period = Billing::getPeriod($bill->bill_id, $from, $to);
        $this->assertEquals(600, $period['period']);
        $this->assertEquals(300, $period['peak_in']);
        $this->assertEquals(3000, $period['peak_out']);

        $this->assertEquals(5500, Billing::getRates($bill->bill_id, $from, $to, 'agg')['total_data']);
    }

    public function testGetPeriodIgnoresOtherBills(): void
    {
        $bill = Bill::factory()->create();
        $other = Bill::factory()->create();
        $this->addSample($bill, $this->start + 300, 300, 100, 1000);
        $this->addSample($other, $this->start + 300, 900, 500, 5000);

        $period = Billing::getPeriod($bill->bill_id, $this->dbDate($this->start), $this->dbDate($this->start + 300));

        $this->assertEquals(300, $period['period']);
        $this->assertEquals(100, $period['peak_in']);
        $this->assertEquals(1000, $period['peak_out']);
    }

    public function testGetPeriodWithNoData(): void
    {
        $bill = Bill::factory()->create();

        $period = Billing::getPeriod($bill->bill_id, $this->dbDate($this->start), $this->dbDate($this->start + 300));

        $this->assertSame(['period' => null, 'peak_in' => null, 'peak_out' => null], $period);
    }

    public function testBitsGraphDataReducesPoints(): void
    {
        $bill = Bill::factory()->create(['bill_type' => 'cdr', 'rate_95th' => 123, 'rate_average' => 45]);
        $this->addRamp($bill, 4);

        $graph = Billing::getBitsGraphData($bill->bill_id, $this->start, $this->start + 1200, 2);

        $this->assertEquals([12, 28], array_values($graph['in_data']));
        $this->assertEquals([24, 56], array_values($graph['out_data']));
        $this->assertEquals([36, 84], array_values($graph['tot_data']));
        $this->assertEquals([$this->start + 600, $this->start + 1200], array_values($graph['ticks']));
        $this->assertEquals($this->start + 300, $graph['first']);
        $this->assertEquals($this->start + 1200, $graph['last']);
        $this->assertEquals(32, $graph['max_in']);
        $this->assertEquals(64, $graph['max_out']);
        $this->assertEquals(20, $graph['ave_in']);
        $this->assertEquals(40, $graph['ave_out']);
        $this->assertEquals(32, $graph['last_in']);
        $this->assertEquals(64, $graph['last_out']);
        $this->assertEquals(123, $graph['rate_95th']);
        $this->assertEquals(45, $graph['rate_average']);
        $this->assertEquals('cdr', $graph['bill_type']);
    }

    public function testBitsGraphDataWritesLeftoverSamples(): void
    {
        $bill = Bill::factory()->create();
        $this->addRamp($bill, 4);

        $graph = Billing::getBitsGraphData($bill->bill_id, $this->start, $this->start + 1200, 3);

        $this->assertEquals([16, 32], array_values($graph['in_data']));
        $this->assertEquals([$this->start + 900, $this->start + 1200], array_values($graph['ticks']));
    }

    public function testBitsGraphDataIncludesBothEnds(): void
    {
        $bill = Bill::factory()->create();
        $this->addRamp($bill, 4);

        $graph = Billing::getBitsGraphData($bill->bill_id, $this->start + 600, $this->start + 900, 1);

        $this->assertEquals([16, 24], array_values($graph['in_data']));
        $this->assertEquals($this->start + 600, $graph['first']);
        $this->assertEquals($this->start + 900, $graph['last']);
    }

    public function testBitsGraphDataAutoReduceFactor(): void
    {
        $bill = Bill::factory()->create();
        $this->addRamp($bill, 4);

        $graph = Billing::getBitsGraphData($bill->bill_id, $this->start, $this->start + 1200, null);

        $this->assertCount(4, $graph['ticks']);
    }

    public function testBitsGraphDataKeepsLeftoverOutboundOnlySample(): void
    {
        $bill = Bill::factory()->create();
        $this->addRamp($bill, 2);
        $this->addSample($bill, $this->start + 900, 300, 0, 300);

        $graph = Billing::getBitsGraphData($bill->bill_id, $this->start, $this->start + 900, 2);

        $this->assertEquals([12, 0], array_values($graph['in_data']));
        $this->assertEquals([24, 8], array_values($graph['out_data']));
    }

    public function testBitsGraphDataSurvivesTrailingZeroPeriod(): void
    {
        $bill = Bill::factory()->create();
        // poll-billing.php writes period 0 for the first sample of a bill
        $this->addSample($bill, $this->start, 0, 100, 100);

        $graph = Billing::getBitsGraphData($bill->bill_id, $this->start - 10, $this->start + 10, 1);

        $this->assertSame([], $graph['ticks']);
        $this->assertEquals($this->start, $graph['first']);
    }

    public function testBitsGraphDataForMissingBill(): void
    {
        $this->expectException(ModelNotFoundException::class);

        Billing::getBitsGraphData(999999, $this->start, $this->start + 300, 1);
    }

    public function testHistoryBitsGraphDataUsesHistoricalRates(): void
    {
        $bill = Bill::factory()->create(['bill_type' => 'quota', 'rate_95th' => 1, 'rate_average' => 2]);
        $this->addRamp($bill, 4);
        $history = $this->addHistory($bill, $this->start, $this->start + 600, ['bill_type' => 'CDR', 'rate_95th' => 500, 'rate_average' => 400]);

        $graph = Billing::getHistoryBitsGraphData($bill->bill_id, $history->bill_hist_id, 1);

        $this->assertNotNull($graph);
        $this->assertEquals($this->start, $graph['from']);
        $this->assertEquals($this->start + 600, $graph['to']);
        $this->assertCount(2, $graph['ticks']);
        $this->assertEquals(500, $graph['rate_95th']);
        $this->assertEquals(400, $graph['rate_average']);
        $this->assertEquals('CDR', $graph['bill_type']);
    }

    public function testHistoryBitsGraphDataForMissingHistory(): void
    {
        $bill = Bill::factory()->create();

        $this->assertNull(Billing::getHistoryBitsGraphData($bill->bill_id, 999999, 1));
    }

    public function testHistoricTransferGraphDataLimitsToNewestTwelve(): void
    {
        $bill = Bill::factory()->create();
        foreach (range(1, 13) as $month) {
            $this->addHistory($bill, (int) mktime(0, 0, 0, $month, 1, 2025), (int) mktime(0, 0, 0, $month, 20, 2025), [
                'bill_type' => $month % 2 ? 'Quota' : 'CDR',
                'bill_allowed' => 1000,
                'bill_overuse' => 50,
                'traf_in' => $month,
                'traf_out' => $month * 10,
                'traf_total' => $month * 11,
            ]);
        }

        $graph = Billing::getHistoricTransferGraphData($bill->bill_id);

        $this->assertCount(12, $graph['in_data']);
        $this->assertEquals('2026-01-01 - 2026-01-20', $graph['ticklabels'][0]);
        $this->assertEquals(13, $graph['in_data'][0]);
        $this->assertEquals(130, $graph['out_data'][0]);
        $this->assertEquals(143, $graph['tot_data'][0]);
        $this->assertEquals(2, $graph['in_data'][11]);
        $this->assertEquals(1000, $graph['allow_data'][0]);
        $this->assertEquals(50, $graph['overuse_data'][0]);
        $this->assertEquals(0, $graph['allow_data'][1]);
        $this->assertEquals(0, $graph['overuse_data'][1]);
    }

    public function testHistoricTransferGraphDataPadsToTwelve(): void
    {
        $bill = Bill::factory()->create();
        $this->addHistory($bill, $this->start, $this->start + 600, ['bill_type' => 'Quota', 'bill_allowed' => 1000, 'traf_in' => 5]);

        $graph = Billing::getHistoricTransferGraphData($bill->bill_id);

        $this->assertCount(12, $graph['in_data']);
        $this->assertCount(12, $graph['ticklabels']);
        $this->assertEquals(5, $graph['in_data'][0]);
        $this->assertEquals(1000, $graph['allow_data'][1]);
        $this->assertEquals(0, $graph['allow_data'][2]);
        $this->assertSame('', $graph['ticklabels'][1]);
    }

    public function testBandwidthGraphDataByDay(): void
    {
        $bill = Bill::factory()->create();
        $dayOne = (int) strtotime('2026-01-15 12:00:00');
        $dayTwo = (int) strtotime('2026-01-16 12:00:00');
        $this->addSample($bill, $dayOne, 300, 100, 200);
        $this->addSample($bill, $dayOne + 3600, 300, 100, 200);
        $this->addSample($bill, $dayTwo, 300, 300, 400);

        $graph = Billing::getBandwidthGraphData($bill->bill_id, null, $dayOne - 3600, $dayTwo + 3600, 'day');

        $this->assertNotNull($graph);
        $this->assertEquals(['2026-01-15', '2026-01-16'], array_slice($graph['ticklabels'], 0, 2));
        $this->assertEquals([200, 300], array_slice($graph['in_data'], 0, 2));
        $this->assertEquals([400, 400], array_slice($graph['out_data'], 0, 2));
        $this->assertEquals([600, 700], array_slice($graph['tot_data'], 0, 2));
        $this->assertEquals(650, $graph['ave_data'][0]);
    }

    public function testBandwidthGraphDataByHour(): void
    {
        $bill = Bill::factory()->create();
        $noon = (int) strtotime('2026-01-15 12:00:00');
        $this->addSample($bill, $noon, 300, 100, 200);
        $this->addSample($bill, $noon + 3600, 300, 300, 400);
        $this->addSample($bill, $noon + 86400, 300, 500, 600);

        $graph = Billing::getBandwidthGraphData($bill->bill_id, null, $noon, $noon + 86400, 'hour');

        $this->assertNotNull($graph);
        $this->assertEquals(['12:00', '13:00'], $graph['ticklabels']);
        $this->assertEquals([600, 300], $graph['in_data']);
        $this->assertEquals([800, 400], $graph['out_data']);
        $this->assertEquals([1400, 700], $graph['tot_data']);
        $this->assertEquals(1050, $graph['ave_data'][0]);
    }

    public function testBandwidthGraphDataUsesHistoryRange(): void
    {
        $bill = Bill::factory()->create();
        $noon = (int) strtotime('2026-01-15 12:00:00');
        $this->addSample($bill, $noon, 300, 100, 200);
        $this->addSample($bill, $noon + 7200, 300, 500, 600);
        $history = $this->addHistory($bill, $noon, $noon + 3600);

        $graph = Billing::getBandwidthGraphData($bill->bill_id, $history->bill_hist_id, null, null, 'hour');

        $this->assertNotNull($graph);
        $this->assertEquals(['12:00'], $graph['ticklabels']);
        $this->assertEquals([100], $graph['in_data']);
    }

    public function testBandwidthGraphDataForMissingHistory(): void
    {
        $bill = Bill::factory()->create();

        $this->assertNull(Billing::getBandwidthGraphData($bill->bill_id, 999999, null, null, 'hour'));
    }

    public function testBandwidthGraphDataRequiresRange(): void
    {
        $bill = Bill::factory()->create();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Must supply from and to');

        Billing::getBandwidthGraphData($bill->bill_id, null, null, null, 'hour');
    }

    public function testBandwidthGraphDataRejectsUnknownType(): void
    {
        $bill = Bill::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        Billing::getBandwidthGraphData($bill->bill_id, null, $this->start, $this->start + 300, '<script>');
    }

    /**
     * Samples every 5 minutes after the start at 8n bps in and 16n bps out
     */
    private function addRamp(Bill $bill, int $samples): void
    {
        foreach (range(1, $samples) as $n) {
            $this->addSample($bill, $this->start + $n * 300, 300, 300 * $n, 600 * $n);
        }
    }

    private function addSample(Bill $bill, int $time, int $period, int $in, int $out): void
    {
        BillData::insert([
            'bill_id' => $bill->bill_id,
            'timestamp' => date('Y-m-d H:i:s', $time),
            'period' => $period,
            'delta' => $in + $out,
            'in_delta' => $in,
            'out_delta' => $out,
        ]);
    }

    /**
     * @param  array<model-property<BillHistory>, int|string>  $attributes
     */
    private function addHistory(Bill $bill, int $from, int $to, array $attributes = []): BillHistory
    {
        return BillHistory::create(array_merge([
            'bill_id' => $bill->bill_id,
            'bill_datefrom' => date('Y-m-d H:i:s', $from),
            'bill_dateto' => date('Y-m-d H:i:s', $to),
            'bill_type' => 'CDR',
            'bill_allowed' => 0,
            'bill_used' => 0,
            'bill_overuse' => 0,
            'bill_percent' => 0,
            'rate_95th_in' => 0,
            'rate_95th_out' => 0,
            'rate_95th' => 0,
            'dir_95th' => 'in',
            'rate_average' => 0,
            'rate_average_in' => 0,
            'rate_average_out' => 0,
            'traf_in' => 0,
            'traf_out' => 0,
            'traf_total' => 0,
            'bill_peak_out' => 0,
            'bill_peak_in' => 0,
        ], $attributes));
    }

    private function dbDate(int $time): string
    {
        return date('YmdHis', $time);
    }
}
