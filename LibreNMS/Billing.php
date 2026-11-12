<?php

namespace LibreNMS;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Bill;
use App\Models\BillData;
use App\Models\BillHistory;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LibreNMS\Util\Number;
use SnmpQuery;
use UnexpectedValueException;

class Billing
{
    public static function formatBytes($value): string
    {
        return Number::formatBase($value, LibrenmsConfig::get('billing.base'));
    }

    public static function formatBytesShort($value): string
    {
        return Number::formatBase($value, LibrenmsConfig::get('billing.base'), 2, 0, '');
    }

    public static function getDates($dayofmonth, $months = 0, ?DateTimeInterface $now = null): array
    {
        $now ??= now();
        $dayofmonth = Str::padLeft($dayofmonth, 2, '0');
        $year = $now->format('Y');
        $month = $now->format('m');

        if ($now->format('d') > $dayofmonth) {
            // Billing day is past, so it is next month
            $date_end = date_create($year . '-' . $month . '-' . $dayofmonth);
            $date_start = date_create($year . '-' . $month . '-' . $dayofmonth);
            date_add($date_end, date_interval_create_from_date_string('1 month'));
        } else {
            // Billing day will happen this month, therefore started last month
            $date_end = date_create($year . '-' . $month . '-' . $dayofmonth);
            $date_start = date_create($year . '-' . $month . '-' . $dayofmonth);
            date_sub($date_start, date_interval_create_from_date_string('1 month'));
        }

        if ($months > 0) {
            date_sub($date_start, date_interval_create_from_date_string($months . ' month'));
            date_sub($date_end, date_interval_create_from_date_string($months . ' month'));
        }

        // date_sub($date_start, date_interval_create_from_date_string('1 month'));
        date_sub($date_end, date_interval_create_from_date_string('1 day'));

        $date_from = date_format($date_start, 'Ymd') . '000000';
        $date_to = date_format($date_end, 'Ymd') . '235959';

        date_sub($date_start, date_interval_create_from_date_string('1 month'));
        date_sub($date_end, date_interval_create_from_date_string('1 month'));

        $last_from = date_format($date_start, 'Ymd') . '000000';
        $last_to = date_format($date_end, 'Ymd') . '235959';

        $return = [];
        $return['0'] = $date_from;
        $return['1'] = $date_to;
        $return['2'] = $last_from;
        $return['3'] = $last_to;

        return $return;
    }

    public static function getPredictedUsage($bill_day, $cur_used): float|int
    {
        $now = now();
        $tmp = self::getDates($bill_day, 0, $now);
        $start = new DateTime($tmp[0], new DateTimeZone(date_default_timezone_get()));
        $end = new DateTime($tmp[1], new DateTimeZone(date_default_timezone_get()));
        $today = new DateTime($now->format('Y-m-d'), new DateTimeZone(date_default_timezone_get()));
        $total = $end->diff($start)->format('%a');
        $since = $today->diff($start)->format('%a');

        // Prevent DivisionByZeroError when short previous months cause date_sub() to overflow the start date, making $since equal to 0.
        if ($since == 0) {
            $since = 1;
        }

        return $cur_used / $since * $total;
    }

    /**
     * Blend the current 95th-percentile rate with a trend from the previous
     * three billing cycles, weighted by complete days elapsed in this cycle.
     */
    public static function getPredictedCdrUsage(Bill $bill): float
    {
        if (! $bill->isCdr()) {
            throw new InvalidArgumentException('A CDR bill is required.');
        }

        $now = now();
        [$from, $to] = self::getDates($bill->bill_day, 0, $now);
        $currentStart = new DateTime($from);
        $currentEnd = new DateTime($to)->modify('+1 second');
        $oldestStart = new DateTime(self::getDates($bill->bill_day, 3, $now)[0]);
        $history = $bill->history()
            ->whereRaw('LOWER(bill_type) = ?', ['cdr'])
            ->where('bill_dateto', '>=', $oldestStart->format('Y-m-d H:i:s'))
            ->where('bill_dateto', '<', min($currentStart, $now)->format('Y-m-d H:i:s'))
            ->orderByDesc('bill_dateto')
            ->orderByDesc('bill_hist_id')
            ->get(['bill_dateto', 'rate_95th']);

        $points = [];
        $seenCycles = [];
        foreach ($history as $row) {
            if (! isset($seenCycles[$row->bill_dateto])) {
                $points[] = (float) $row->rate_95th;
                $seenCycles[$row->bill_dateto] = true;
            }
        }

        // Fall back to current use if there are no prior months to predict from
        $count = count($points);
        if ($count === 0) {
            return max(0.0, $bill->used());
        }

        // Calculate the slope for the average trend
        // Historical points have trend indices -1, -2, and so on.
        $meanIndex = -($count + 1) / 2;
        $meanUsage = array_sum($points) / $count;
        $numerator = 0.0;
        $denominator = 0.0;
        foreach ($points as $idx => $usage) {
            $idx = -$idx - 1;
            $numerator += ($idx - $meanIndex) * ($usage - $meanUsage);
            $denominator += ($idx - $meanIndex) ** 2;
        }
        $slope = $count > 1 ? $numerator / $denominator : 0.0;

        // Calculate a predicted usage from the prior months
        $predicted = max(0.0, $meanUsage - $slope * $meanIndex);

        // Calcuate a weight to apply between the current and predicted usage values
        // Prefer the predicted value more at the start of the month, and the current usage more at the end
        $totalDays = (int) $currentStart->diff($currentEnd)->format('%r%a');
        if ($totalDays <= 0) {
            throw new UnexpectedValueException('The billing cycle must contain at least one day.');
        }
        $today = new DateTime($now->format('Y-m-d'));
        $elapsedDays = (int) $currentStart->diff($today)->format('%r%a');
        $currentWeight = min(1.0, max(0.0, $elapsedDays / $totalDays));

        return $predicted * (1 - $currentWeight) + max(0.0, $bill->used()) * $currentWeight;
    }

    public static function calculateBitrate(int|float $measurement, int|float $last_measurement, int|float $period): float
    {
        if ($period <= 0) {
            return 0.0;
        }

        return round(($measurement - $last_measurement) * 8 / $period, 2);
    }

    public static function getValue($device_id, $id, $inout): ?int
    {
        $device = DeviceCache::get($device_id);
        $value = SnmpQuery::device($device)->get('IF-MIB::ifHC' . $inout . 'Octets.' . $id)->value();

        if (! is_numeric($value)) {
            $value = SnmpQuery::device($device)->get('IF-MIB::if' . $inout . 'Octets.' . $id)->value();
        }

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * @return array{rate_agg: float, rate_in: float, rate_out: float}
     */
    private static function getPercentileRates($bill_id, $datefrom, $dateto, int $percentile = 95): array
    {
        $buckets = BillData::query()
            ->selectRaw('SUM(delta) / SUM(period) * 8 as rate_agg, SUM(in_delta) / SUM(period) * 8 as rate_in, SUM(out_delta) / SUM(period) * 8 as rate_out')
            ->selectRaw('FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(`timestamp`) / 300) * 300) AS bucket_start')
            ->where('bill_id', $bill_id)
            ->inBillingPeriod($datefrom, $dateto)
            ->groupBy('bucket_start')
            ->toBase()
            ->get();

        $measurement = max(0, (int) round($buckets->count() / 100 * $percentile) - 2);
        $rate_at = fn (string $rate): float => round($buckets->pluck($rate)->map(fn ($value) => (float) $value)->sort()->values()[$measurement] ?? 0, 2);

        return [
            'rate_agg' => $rate_at('rate_agg'),
            'rate_in' => $rate_at('rate_in'),
            'rate_out' => $rate_at('rate_out'),
        ];
    }

    public static function getRates($bill_id, $datefrom, $dateto, $dir_95th): array
    {
        $data = [];

        $sum_data = self::getSum($bill_id, $datefrom, $dateto);
        $mtot = $sum_data->total;
        $mtot_in = $sum_data->inbound;
        $mtot_out = $sum_data->outbound;
        $ptot = $sum_data->period;

        $rates_95th = self::getPercentileRates($bill_id, $datefrom, $dateto);
        $data['rate_95th_in'] = $rates_95th['rate_in'];
        $data['rate_95th_out'] = $rates_95th['rate_out'];

        if ($dir_95th == 'agg') {
            $data['rate_95th'] = $rates_95th['rate_agg'];
            $data['dir_95th'] = 'agg';
        } else {
            if ($data['rate_95th_out'] > $data['rate_95th_in']) {
                $data['rate_95th'] = $data['rate_95th_out'];
                $data['dir_95th'] = 'out';
            } else {
                $data['rate_95th'] = $data['rate_95th_in'];
                $data['dir_95th'] = 'in';
            }
        }

        $data['total_data'] = $mtot;
        $data['total_data_in'] = $mtot_in;
        $data['total_data_out'] = $mtot_out;
        $data['rate_average'] = ! empty($ptot) ? ($mtot / $ptot * 8) : 0;
        $data['rate_average_in'] = ! empty($ptot) ? ($mtot_in / $ptot * 8) : 0;
        $data['rate_average_out'] = ! empty($ptot) ? ($mtot_out / $ptot * 8) : 0;

        return $data;
    }

    private static function getSum($bill_id, $datefrom, $dateto)
    {
        return BillData::query()
            ->selectRaw('SUM(period) as period, SUM(delta) as total, SUM(in_delta) as inbound, SUM(out_delta) as outbound')
            ->where('bill_id', $bill_id)
            ->inBillingPeriod($datefrom, $dateto)
            ->toBase()
            ->first();
    }

    public static function getPeriod($bill_id, $datefrom, $dateto): array
    {
        return (array) BillData::query()
            ->selectRaw('SUM(period) as period, MAX(in_delta) as peak_in, MAX(out_delta) as peak_out')
            ->where('bill_id', $bill_id)
            ->inBillingPeriod($datefrom, $dateto)
            ->toBase()
            ->first();
    }

    public static function getHistoryBitsGraphData($bill_id, $bill_hist_id, $reducefactor): ?array
    {
        $histrow = BillHistory::query()
            ->selectRaw('UNIX_TIMESTAMP(bill_datefrom) as `from`, UNIX_TIMESTAMP(bill_dateto) as `to`, rate_95th, rate_average, bill_type')
            ->where('bill_id', $bill_id)
            ->where('bill_hist_id', $bill_hist_id)
            ->toBase()
            ->first();

        if (is_null($histrow)) {
            return null;
        }

        $graph_data = self::getBitsGraphData($bill_id, $histrow->from, $histrow->to, $reducefactor);

        // Overwrite the rate data with the historical version
        $graph_data['rate_95th'] = $histrow->rate_95th;
        $graph_data['rate_average'] = $histrow->rate_average;
        $graph_data['bill_type'] = $histrow->bill_type;

        return $graph_data;
    }

    public static function getBitsGraphData($bill_id, $from, $to, $reducefactor): array
    {
        $i = '0';
        $iter = 0;
        $first = null;
        $last = null;
        $iter_in = 0;
        $iter_out = 0;
        $iter_period = 0;
        $max_in = 0;
        $max_out = 0;
        $tot_in = 0;
        $tot_out = 0;
        $tot_period = 0;
        $in_delta = null;
        $out_delta = null;
        $period = null;
        $in_data = [];
        $out_data = [];
        $tot_data = [];
        $ticks = [];

        if (! isset($reducefactor) || ! is_numeric($reducefactor) || $reducefactor < 1) {
            // Auto calculate reduce factor
            $expectedpoints = ceil(($to - $from) / 300);
            $desiredpoints = 400;
            $reducefactor = max(1, floor($expectedpoints / $desiredpoints));
        }

        $bill = Bill::findOrFail($bill_id);

        $billdata = BillData::query()
            ->select('*')
            ->selectRaw('UNIX_TIMESTAMP(`timestamp`) AS formatted_date')
            ->where('bill_id', $bill_id)
            ->betweenUnixTimes($from, $to)
            ->orderBy('timestamp')
            ->toBase()
            ->get();

        foreach ($billdata as $row) {
            $timestamp = $row->formatted_date;
            if (! $first) {
                $first = $timestamp;
            }

            $period = $row->period;
            $in_delta = $row->in_delta * 8;
            $out_delta = $row->out_delta * 8;
            $last = $timestamp;

            $iter_in += $in_delta;
            $iter_out += $out_delta;
            $iter_period += $period;

            if ($period > 0) {
                $max_in = max($max_in, $in_delta / $period);
                $max_out = max($max_out, $out_delta / $period);
                $tot_in += $in_delta;
                $tot_out += $out_delta;
                $tot_period += $period;

                if (++$iter >= $reducefactor) {
                    $out_data[$i] = round($iter_out / $iter_period, 2);
                    $in_data[$i] = round($iter_in / $iter_period, 2);
                    $tot_data[$i] = ($out_data[$i] + $in_data[$i]);
                    $ticks[$i] = $timestamp;
                    $i++;
                    $iter = 0;
                    $iter_out = 0;
                    $iter_in = 0;
                    $iter_period = 0;
                }
            }
        }//end foreach

        if ($iter_period > 0) {  // Write last element
            $out_data[$i] = round($iter_out / $iter_period, 2);
            $in_data[$i] = round($iter_in / $iter_period, 2);
            $tot_data[$i] = ($out_data[$i] + $in_data[$i]);
            $ticks[$i] = $timestamp ?? time();
            $i++;
        }
        $result = [
            'from' => $from,
            'to' => $to,
            'first' => $first,
            'last' => $last,

            'in_data' => $in_data,
            'out_data' => $out_data,
            'tot_data' => $tot_data,
            'ticks' => $ticks,

            'rate_95th' => $bill->rate_95th,
            'rate_average' => $bill->rate_average,
            'bill_type' => $bill->bill_type,
        ];

        if ($period) {
            $result['max_in'] = $max_in;
            $result['max_out'] = $max_out;
            $result['ave_in'] = $tot_in / $tot_period;
            $result['ave_out'] = $tot_out / $tot_period;
            $result['last_in'] = $in_delta / $period;
            $result['last_out'] = $out_delta / $period;
        }

        return $result;
    }

    public static function getHistoricTransferGraphData($bill_id): array
    {
        $i = '0';

        $in_data = [];
        $out_data = [];
        $tot_data = [];
        $allow_data = [];
        $ave_data = [];
        $overuse_data = [];
        $ticklabels = [];
        $allowed_val = null;

        $billhist = BillHistory::query()
            ->where('bill_id', $bill_id)
            ->orderByDesc('bill_datefrom')
            ->limit(12)
            ->toBase()
            ->get();

        foreach ($billhist as $data) {
            $datefrom = date('Y-m-d', strtotime((string) $data->bill_datefrom));
            $dateto = date('Y-m-d', strtotime((string) $data->bill_dateto));
            $datelabel = $datefrom . ' - ' . $dateto;

            array_push($ticklabels, $datelabel);
            array_push($in_data, $data->traf_in);
            array_push($out_data, $data->traf_out);
            array_push($tot_data, $data->traf_total);
            array_push($allow_data, $allowed_val = ($data->bill_type == 'Quota' ? $data->bill_allowed : 0));
            array_push($overuse_data, $data->bill_type == 'Quota' ? $data->bill_overuse : 0);
            $i++;
        }//end foreach

        if ($i < 12) {
            $y = (12 - $i);
            for ($x = 0; $x < $y; $x++) {
                $allowed = (($x == '0') ? $allowed_val : '0');
                array_push($in_data, '0');
                array_push($out_data, '0');
                array_push($tot_data, '0');
                array_push($allow_data, $allowed);
                array_push($overuse_data, '0');
                array_push($ticklabels, '');
            }
        }

        $graph_name = 'Historical bandwidth over the last 12 billing periods';

        return [
            'graph_name' => $graph_name,
            'in_data' => $in_data,
            'out_data' => $out_data,
            'tot_data' => $tot_data,
            'allow_data' => $allow_data,
            'ave_data' => $ave_data,
            'overuse_data' => $overuse_data,
            'ticklabels' => $ticklabels,
        ];
    }

    public static function getBandwidthGraphData($bill_id, $bill_hist_id, $from, $to, $imgtype): ?array
    {
        if (is_numeric($bill_hist_id)) {
            $histrow = BillHistory::query()
                ->selectRaw('UNIX_TIMESTAMP(bill_datefrom) as `from`, UNIX_TIMESTAMP(bill_dateto) as `to`, rate_95th, rate_average')
                ->where('bill_id', $bill_id)
                ->where('bill_hist_id', $bill_hist_id)
                ->toBase()
                ->first();

            if (is_null($histrow)) {
                return null;
            }
            $from = $histrow->from;
            $to = $histrow->to;
        } else {
            if (! is_numeric($from) || ! is_numeric($to)) {
                throw new \Exception('Must supply from and to if bill_hist_id is not supplied');
            }
        }

        $in_data = [];
        $out_data = [];
        $tot_data = [];
        $allow_data = [];
        $ave_data = [];
        $overuse_data = [];
        $ticklabels = [];

        $data = [];
        $average = 0;
        if ($imgtype == 'day') {
            $billdata = BillData::query()
                ->selectRaw('UNIX_TIMESTAMP(MIN(`timestamp`)) as formatted_timestamp, SUM(delta) as traf_total, SUM(in_delta) as traf_in, SUM(out_delta) as traf_out')
                ->where('bill_id', $bill_id)
                ->betweenUnixTimes($from, $to)
                ->groupByRaw('DATE(`timestamp`)')
                ->orderByRaw('DATE(`timestamp`) ASC')
                ->toBase()
                ->get();

            foreach ($billdata as $data) {
                array_push($ticklabels, date('Y-m-d', $data->formatted_timestamp));
                array_push($in_data, $data->traf_in ?? 0);
                array_push($out_data, $data->traf_out ?? 0);
                array_push($tot_data, $data->traf_total ?? 0);
                $average += $data->traf_total;
            }

            $ave_count = count($tot_data);

            // Add empty items for the days not yet passed
            $days = (date('j', $to - $from) - $ave_count - 1);
            for ($x = 0; $x < $days; $x++) {
                array_push($ticklabels, '');
                array_push($in_data, 0);
                array_push($out_data, 0);
                array_push($tot_data, 0);
            }
        } elseif ($imgtype == 'hour') {
            $billdata = BillData::query()
                ->selectRaw('HOUR(`timestamp`) as hour, SUM(delta) as traf_total, SUM(in_delta) as traf_in, SUM(out_delta) as traf_out')
                ->where('bill_id', $bill_id)
                ->betweenUnixTimes($from, $to)
                ->groupByRaw('HOUR(`timestamp`)')
                ->orderByRaw('HOUR(`timestamp`) ASC')
                ->toBase()
                ->get();

            foreach ($billdata as $data) {
                array_push($ticklabels, sprintf('%02d', $data->hour) . ':00');
                array_push($in_data, $data->traf_in ?? 0);
                array_push($out_data, $data->traf_out ?? 0);
                array_push($tot_data, $data->traf_total ?? 0);
                $average += $data->traf_total;
            }

            $ave_count = count($tot_data);
        } else {
            throw new \InvalidArgumentException('Unknown graph type, expected day or hour');
        }//end if

        $average = $ave_count ? ($average / $ave_count) : 0;
        $tot_data_size = count($tot_data);
        for ($x = 0; $x <= $tot_data_size; $x++) {
            array_push($ave_data, $average);
        }

        $graph_name = date('M j g:ia', $from) . ' - ' . date('M j g:ia', $to);

        return [
            'graph_name' => $graph_name,
            'in_data' => $in_data,
            'out_data' => $out_data,
            'tot_data' => $tot_data,
            'allow_data' => $allow_data,
            'ave_data' => $ave_data,
            'overuse_data' => $overuse_data,
            'ticklabels' => $ticklabels,
        ];
    }
}
