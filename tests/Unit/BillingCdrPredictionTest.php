<?php

namespace LibreNMS\Tests\Unit;

use App\Models\Bill;
use App\Models\BillHistory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use LibreNMS\Billing;
use LibreNMS\Tests\TestCase;

class BillingCdrPredictionTest extends TestCase
{
    public function testPreloadedHistoryUsesTheBillWindowAndLatestDuplicate(): void
    {
        $this->travelTo(Carbon::parse('2024-06-02 12:00:00'));
        $bill = new Bill(['bill_type' => 'cdr', 'bill_day' => 1, 'rate_95th' => 75]);
        $history = new Collection([
            new BillHistory(['bill_dateto' => '2024-06-01 00:00:00', 'rate_95th' => 9999]),
            new BillHistory(['bill_dateto' => '2024-05-31 23:59:59', 'rate_95th' => 250]),
            new BillHistory(['bill_dateto' => '2024-05-31 23:59:59', 'rate_95th' => 200]),
            new BillHistory(['bill_dateto' => '2024-04-30 23:59:59', 'rate_95th' => 100]),
            new BillHistory(['bill_dateto' => '2024-02-29 23:59:59', 'rate_95th' => 9999]),
        ]);

        $this->assertEqualsWithDelta(400 * 29 / 30 + 75 / 30, Billing::getPredictedCdrUsage($bill, $history), 0.0001);
        $this->assertSame(75.0, Billing::getPredictedCdrUsage($bill, new Collection));
    }

    public function testPreloadedHistoryExcludesFutureRecordsWhenCycleStartsInTheFuture(): void
    {
        $this->travelTo(Carbon::parse('2024-03-01 12:00:00'));
        $bill = new Bill(['bill_type' => 'cdr', 'bill_day' => 31, 'rate_95th' => 75]);
        $history = new Collection([
            new BillHistory(['bill_dateto' => '2024-03-01 18:00:00', 'rate_95th' => 1000]),
            new BillHistory(['bill_dateto' => '2024-02-29 12:00:00', 'rate_95th' => 200]),
        ]);

        $this->assertSame(200.0, Billing::getPredictedCdrUsage($bill, $history));
    }
}
