<?php

namespace LibreNMS;

use App\Facades\LibrenmsConfig;
use App\Models\Bill;
use App\Models\BillCounter;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LibreNMS\Interfaces\Models\BillableSource;
use LibreNMS\Util\Number;

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

    public static function getDates($dayofmonth, $months = 0): array
    {
        $dayofmonth = Str::padLeft($dayofmonth, 2, '0');
        $year = date('Y');
        $month = date('m');

        if (date('d') > $dayofmonth) {
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
        $tmp = self::getDates($bill_day, 0);
        $start = new DateTime($tmp[0], new DateTimeZone(date_default_timezone_get()));
        $end = new DateTime($tmp[1], new DateTimeZone(date_default_timezone_get()));
        $now = new DateTime(date('Y-m-d'), new DateTimeZone(date_default_timezone_get()));
        $total = $end->diff($start)->format('%a');
        $since = $now->diff($start)->format('%a');

        // Prevent DivisionByZeroError when short previous months cause date_sub() to overflow the start date, making $since equal to 0.
        if ($since == 0) {
            $since = 1;
        }

        return $cur_used / $since * $total;
    }

    private static function get95thagg($bill_id, $datefrom, $dateto): float
    {
        $sum_data = dbFetchRows('SELECT (SUM(delta) / SUM(period) * 8) as rate, FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(`timestamp`) / 300) * 300) AS bucket_start FROM bill_data WHERE bill_id = ? AND timestamp > ? AND timestamp <= ? GROUP BY bill_id, bucket_start ORDER BY rate ASC', [$bill_id, $datefrom, $dateto]);
        $measurement_95th = max(0, (int) round(count($sum_data) / 100 * 95) - 2);

        return round($sum_data[$measurement_95th]['rate'] ?? 0, 2);
    }

    private static function get95thIn($bill_id, $datefrom, $dateto): float
    {
        $sum_data = dbFetchRows('SELECT (SUM(in_delta) / SUM(period) * 8) as rate, FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(`timestamp`) / 300) * 300) AS bucket_start FROM bill_data WHERE bill_id = ? AND timestamp > ? AND timestamp <= ? GROUP BY bill_id, bucket_start ORDER BY rate ASC', [$bill_id, $datefrom, $dateto]);
        $measurement_95th = max(0, (int) round(count($sum_data) / 100 * 95) - 2);

        return round($sum_data[$measurement_95th]['rate'] ?? 0, 2);
    }

    private static function get95thout($bill_id, $datefrom, $dateto): float
    {
        $sum_data = dbFetchRows('SELECT (SUM(out_delta) / SUM(period) * 8) as rate, FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(`timestamp`) / 300) * 300) AS bucket_start FROM bill_data WHERE bill_id = ? AND timestamp > ? AND timestamp <= ? GROUP BY bill_id, bucket_start ORDER BY rate ASC', [$bill_id, $datefrom, $dateto]);
        $measurement_95th = max(0, (int) round(count($sum_data) / 100 * 95) - 2);

        return round($sum_data[$measurement_95th]['rate'] ?? 0, 2);
    }

    /**
     * Account the traffic of every source on the bill since the previous run
     */
    public static function pollBill(Bill $bill): void
    {
        $now = Carbon::now();
        $in_delta = 0;
        $out_delta = 0;

        foreach (self::activeSources($bill) as $source) {
            /** @var \App\Models\Device $device loaded by activeSources() */
            $device = $source->getRelation('device');
            Log::info('  ' . $source::billingTypeName() . ' ' . $source->getBillingLabel() . ' on ' . $device->displayName());
            [$in, $out] = self::updateCounter($bill, $source, $now);
            $in_delta += $in;
            $out_delta += $out;
        }

        if (! self::hasSources($bill)) {
            return; // don't insert zero value entries for bills without sources
        }

        $previous = $bill->data()->latest('timestamp')->first();
        $period = $previous ? $now->getTimestamp() - Carbon::parse($previous->timestamp)->getTimestamp() : 0;

        if ($period < 0) {
            Log::debug("BILLING: negative period! id:{$bill->bill_id} period:$period in_delta:$in_delta out_delta:$out_delta");

            return;
        }

        $bill->data()->create([
            'timestamp' => $now,
            'period' => $period,
            'delta' => $in_delta + $out_delta,
            'in_delta' => $in_delta,
            'out_delta' => $out_delta,
        ]);
    }

    /**
     * Store the current counter sample of a source and return the traffic since the previous sample
     *
     * @param  Model&BillableSource  $source  loaded through a Bill relation, so the pivot is present
     * @return array{0: int, 1: int} inbound and outbound octets
     */
    private static function updateCounter(Bill $bill, Model&BillableSource $source, Carbon $now): array
    {
        $counters = $source->fetchBillingCounters();

        if ($counters === null) {
            Log::error('    Could not read the counters, skipping');

            return [0, 0];
        }

        [$in, $out] = $counters;
        /** @var BillCounter $last */
        $last = $source->getRelation('pivot');
        $in_delta = 0;
        $out_delta = 0;

        if ($last->in_counter !== null) {
            $period = max(1, $now->getTimestamp() - $last->timestamp->getTimestamp());
            $in_delta = self::counterDelta($in, $last->in_counter, $last->in_delta, $period, $source->getBillingSpeed());
            $out_delta = self::counterDelta($out, $last->out_counter, $last->out_delta, $period, $source->getBillingSpeed());
        }

        Log::debug("    in: $in (+$in_delta)  out: $out (+$out_delta)");

        $source->bills()->updateExistingPivot($bill->bill_id, [
            'timestamp' => $now,
            'in_counter' => $in,
            'out_counter' => $out,
            'in_delta' => $in_delta,
            'out_delta' => $out_delta,
        ]);

        return [$in_delta, $out_delta];
    }

    /**
     * Octets since the previous sample. A counter wrap or a jump the link speed can't carry
     * repeats the previous delta, as the port based billing always did.
     */
    private static function counterDelta(int $current, int $last, int $last_delta, int $period, ?int $speed): int
    {
        if ($current < $last) {
            return $last_delta;
        }

        $delta = $current - $last;

        if ($speed !== null && $delta * 8 / $period > $speed) {
            return $last_delta;
        }

        return $delta;
    }

    /**
     * Sources of the bill that carry traffic right now: active, on an up device handled by this poller
     *
     * @return Collection<int, Model&BillableSource>
     */
    private static function activeSources(Bill $bill): Collection
    {
        $device = fn (Builder $query) => self::pollerDevices($query)->where('status', 1);

        /** @var Collection<int, Model&BillableSource> $sources */
        $sources = new Collection;
        foreach (Bill::SOURCE_TYPES as $class) {
            $sources = $sources->concat($bill->sources($class)->with('device')
                ->where(fn (Builder $query) => $class::filterBillingActive($query))
                ->whereHas('device', $device)
                ->get());
        }

        return $sources;
    }

    private static function hasSources(Bill $bill): bool
    {
        foreach (Bill::SOURCE_TYPES as $class) {
            if ($bill->sources($class)->whereHas('device', self::pollerDevices(...))->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * With distributed billing every poller only accounts the devices of its own poller groups
     */
    private static function pollerDevices(Builder $query): Builder
    {
        if (LibrenmsConfig::get('distributed_poller') && LibrenmsConfig::get('distributed_billing')) {
            $query->whereIn('poller_group', explode(',', (string) LibrenmsConfig::get('distributed_poller_group')));
        }

        return $query;
    }

    public static function getRates($bill_id, $datefrom, $dateto, $dir_95th): array
    {
        $data = [];

        $sum_data = self::getSum($bill_id, $datefrom, $dateto);
        $mtot = $sum_data['total'];
        $mtot_in = $sum_data['inbound'];
        $mtot_out = $sum_data['outbound'];
        $ptot = $sum_data['period'];

        $data['rate_95th_in'] = self::get95thIn($bill_id, $datefrom, $dateto);
        $data['rate_95th_out'] = self::get95thout($bill_id, $datefrom, $dateto);

        if ($dir_95th == 'agg') {
            $data['rate_95th'] = self::get95thagg($bill_id, $datefrom, $dateto);
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
        $sum = dbFetchRow('SELECT SUM(period) as period, SUM(delta) as total, SUM(in_delta) as inbound, SUM(out_delta) as outbound FROM bill_data WHERE bill_id = ? AND timestamp > ? AND timestamp <= ?', [$bill_id, $datefrom, $dateto]);

        return $sum;
    }

    public static function getPeriod($bill_id, $datefrom, $dateto): array
    {
        $ptot = dbFetchRow('SELECT SUM(period) as `period`, MAX(in_delta) as `peak_in`, MAX(out_delta) as `peak_out`  FROM bill_data WHERE bill_id = ? AND timestamp > ? AND timestamp <= ?', [$bill_id, $datefrom, $dateto]);

        return $ptot;
    }

    public static function getHistoryBitsGraphData($bill_id, $bill_hist_id, $reducefactor): ?array
    {
        $histrow = dbFetchRow('SELECT UNIX_TIMESTAMP(bill_datefrom) as `from`, UNIX_TIMESTAMP(bill_dateto) AS `to`, rate_95th, rate_average, bill_type FROM bill_history WHERE bill_id = ? AND bill_hist_id = ?', [$bill_id, $bill_hist_id]);

        if (is_null($histrow)) {
            return null;
        }

        $graph_data = self::getBitsGraphData($bill_id, $histrow['from'], $histrow['to'], $reducefactor);

        // Overwrite the rate data with the historical version
        $graph_data['rate_95th'] = $histrow['rate_95th'];
        $graph_data['rate_average'] = $histrow['rate_average'];
        $graph_data['bill_type'] = $histrow['bill_type'];

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

        $bill_data = dbFetchRow('SELECT * from `bills` WHERE `bill_id`= ? LIMIT 1', [$bill_id]);

        foreach (dbFetchRows('SELECT *, UNIX_TIMESTAMP(timestamp) AS formatted_date FROM bill_data WHERE bill_id = ? AND `timestamp` >= FROM_UNIXTIME( ? ) AND `timestamp` <= FROM_UNIXTIME( ? ) ORDER BY timestamp ASC', [$bill_id, $from, $to]) as $row) {
            $timestamp = $row['formatted_date'];
            if (! $first) {
                $first = $timestamp;
            }

            $period = $row['period'];
            $in_delta = $row['in_delta'] * 8;
            $out_delta = $row['out_delta'] * 8;
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

        if (! empty($iter_in)) {  // Write last element
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

            'rate_95th' => $bill_data['rate_95th'],
            'rate_average' => $bill_data['rate_average'],
            'bill_type' => $bill_data['bill_type'],
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

        foreach (dbFetchRows('SELECT * FROM `bill_history` WHERE `bill_id` = ? ORDER BY `bill_datefrom` DESC LIMIT 12', [$bill_id]) as $data) {
            $datefrom = date('Y-m-d', strtotime((string) $data['bill_datefrom']));
            $dateto = date('Y-m-d', strtotime((string) $data['bill_dateto']));
            $datelabel = $datefrom . ' - ' . $dateto;

            array_push($ticklabels, $datelabel);
            array_push($in_data, $data['traf_in']);
            array_push($out_data, $data['traf_out']);
            array_push($tot_data, $data['traf_total']);
            array_push($allow_data, $allowed_val = ($data['bill_type'] == 'Quota' ? $data['bill_allowed'] : 0));
            array_push($overuse_data, $data['bill_type'] == 'Quota' ? $data['bill_overuse'] : 0);
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
            $histrow = dbFetchRow('SELECT UNIX_TIMESTAMP(bill_datefrom) as `from`, UNIX_TIMESTAMP(bill_dateto) AS `to`, rate_95th, rate_average FROM bill_history WHERE bill_id = ? AND bill_hist_id = ?', [$bill_id, $bill_hist_id]);

            if (is_null($histrow)) {
                return null;
            }
            $from = $histrow['from'];
            $to = $histrow['to'];
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
            foreach (dbFetchRows('SELECT UNIX_TIMESTAMP(MIN(timestamp)) as timestamp, SUM(delta) as traf_total, SUM(in_delta) as traf_in, SUM(out_delta) as traf_out FROM bill_data WHERE `bill_id` = ? AND `timestamp` >= FROM_UNIXTIME(?) AND `timestamp` <= FROM_UNIXTIME(?) GROUP BY DATE(timestamp) ORDER BY DATE(timestamp) ASC', [$bill_id, $from, $to]) as $data) {
                array_push($ticklabels, date('Y-m-d', $data['timestamp']));
                array_push($in_data, $data['traf_in'] ?? 0);
                array_push($out_data, $data['traf_out'] ?? 0);
                array_push($tot_data, $data['traf_total'] ?? 0);
                $average += $data['traf_total'];
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
            foreach (dbFetchRows('SELECT DISTINCT HOUR(timestamp) as hour, SUM(delta) as traf_total, SUM(in_delta) as traf_in, SUM(out_delta) as traf_out FROM bill_data WHERE `bill_id` = ? AND `timestamp` >= FROM_UNIXTIME(?) AND `timestamp` <= FROM_UNIXTIME(?) GROUP BY HOUR(timestamp) ORDER BY HOUR(timestamp) ASC', [$bill_id, $from, $to]) as $data) {
                array_push($ticklabels, sprintf('%02d', $data['hour']) . ':00');
                array_push($in_data, $data['traf_in'] ?? 0);
                array_push($out_data, $data['traf_out'] ?? 0);
                array_push($tot_data, $data['traf_total'] ?? 0);
                $average += $data['traf_total'];
            }

            $ave_count = count($tot_data);
        } else {
            exit("Unknown graph type $imgtype");
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
