<?php

namespace LibreNMS;

use DateTime;
use DateTimeZone;
use LibreNMS\Util\Number;

class Billing
{
    public static function formatBytes($value): string
    {
        return Number::formatBase($value, Config::get('billing.base'));
    }

    public static function formatBytesShort($value): string
    {
        return Number::formatBase($value, Config::get('billing.base'), 2, 3, '');
    }

    public static function getDates($dayofmonth, $months = 0): array
    {
        $dayofmonth = zeropad($dayofmonth);
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

        return $cur_used / $since * $total;
    }

    public static function getValue($host, $port, $id, $inout): int
    {
        $oid = 'IF-MIB::ifHC' . $inout . 'Octets.' . $id;
        $device = dbFetchRow('SELECT * from `devices` WHERE `hostname` = ? LIMIT 1', [$host]);
        $value = snmp_get($device, $oid, '-Oqv');

        if (! is_numeric($value)) {
            $oid = 'IF-MIB::if' . $inout . 'Octets.' . $id;
            $value = snmp_get($device, $oid, '-Oqv');
        }

        return (int) $value;
    }

    public static function getLastPortCounter($port_id, $bill_id): array
    {
        $return = [];
        $row = dbFetchRow('SELECT timestamp, in_counter, in_delta, out_counter, out_delta FROM bill_port_counters WHERE `port_id` = ? AND `bill_id` = ?', [$port_id, $bill_id]);
        if (! is_null($row)) {
            $return['timestamp'] = $row['timestamp'];
            $return['in_counter'] = $row['in_counter'];
            $return['in_delta'] = $row['in_delta'];
            $return['out_counter'] = $row['out_counter'];
            $return['out_delta'] = $row['out_delta'];
            $return['state'] = 'ok';
        } else {
            $return['state'] = 'failed';
        }

        return $return;
    }

    public static function getLastMeasurement($bill_id): array
    {
        $return = [];
        $row = dbFetchRow('SELECT timestamp,delta,in_delta,out_delta FROM bill_data WHERE bill_id = ? ORDER BY timestamp DESC LIMIT 1', [$bill_id]);
        if (! is_null($row)) {
            $return['delta'] = $row['delta'];
            $return['in_delta'] = $row['in_delta'];
            $return['out_delta'] = $row['out_delta'];
            $return['timestamp'] = $row['timestamp'];
            $return['state'] = 'ok';
        } else {
            $return['state'] = 'failed';
        }

        return $return;
    }

    private static function get95thagg($bill_id, $datefrom, $dateto): float
    {
        $mq_sql = 'SELECT count(delta) FROM bill_data WHERE bill_id = ?';
        $mq_sql .= ' AND timestamp > ? AND timestamp <= ?';
        $measurements = dbFetchCell($mq_sql, [$bill_id, $datefrom, $dateto]);
        $measurement_95th = (round($measurements / 100 * 95) - 1);

        $q_95_sql = 'SELECT (delta / period * 8) AS rate FROM bill_data  WHERE bill_id = ?';
        $q_95_sql .= ' AND timestamp > ? AND timestamp <= ? ORDER BY rate ASC';
        $a_95th = dbFetchColumn($q_95_sql, [$bill_id, $datefrom, $dateto]);
        $m_95th = $a_95th[$measurement_95th];

        return round($m_95th, 2);
    }

    private static function get95thIn($bill_id, $datefrom, $dateto): float
    {
        $mq_sql = 'SELECT count(delta) FROM bill_data WHERE bill_id = ?';
        $mq_sql .= ' AND timestamp > ? AND timestamp <= ?';
        $measurements = dbFetchCell($mq_sql, [$bill_id, $datefrom, $dateto]);
        $measurement_95th = (round($measurements / 100 * 95) - 1);

        $q_95_sql = 'SELECT (in_delta / period * 8) AS rate FROM bill_data  WHERE bill_id = ?';
        $q_95_sql .= ' AND timestamp > ? AND timestamp <= ? ORDER BY rate ASC';
        $a_95th = dbFetchColumn($q_95_sql, [$bill_id, $datefrom, $dateto]);
        $m_95th = $a_95th[$measurement_95th];

        return round($m_95th, 2);
    }

    private static function get95thout($bill_id, $datefrom, $dateto): float
    {
        $mq_sql = 'SELECT count(delta) FROM bill_data WHERE bill_id = ?';
        $mq_sql .= ' AND timestamp > ? AND timestamp <= ?';
        $measurements = dbFetchCell($mq_sql, [$bill_id, $datefrom, $dateto]);
        $measurement_95th = (round($measurements / 100 * 95) - 1);

        $q_95_sql = 'SELECT (out_delta / period * 8) AS rate FROM bill_data  WHERE bill_id = ?';
        $q_95_sql .= ' AND timestamp > ? AND timestamp <= ? ORDER BY rate ASC';
        $a_95th = dbFetchColumn($q_95_sql, [$bill_id, $datefrom, $dateto]);
        $m_95th = $a_95th[$measurement_95th];

        return round($m_95th, 2);
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
            $datefrom = date('Y-m-d', strtotime($data['bill_datefrom']));
            $dateto = date('Y-m-d', strtotime($data['bill_dateto']));
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
            foreach (dbFetchRows('SELECT DISTINCT UNIX_TIMESTAMP(timestamp) as timestamp, SUM(delta) as traf_total, SUM(in_delta) as traf_in, SUM(out_delta) as traf_out FROM bill_data WHERE `bill_id` = ? AND `timestamp` >= FROM_UNIXTIME(?) AND `timestamp` <= FROM_UNIXTIME(?) GROUP BY DATE(timestamp) ORDER BY timestamp ASC', [$bill_id, $from, $to]) as $data) {
                array_push($ticklabels, date('Y-m-d', $data['timestamp']));
                array_push($in_data, isset($data['traf_in']) ? $data['traf_in'] : 0);
                array_push($out_data, isset($data['traf_out']) ? $data['traf_out'] : 0);
                array_push($tot_data, isset($data['traf_total']) ? $data['traf_total'] : 0);
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
                array_push($in_data, isset($data['traf_in']) ? $data['traf_in'] : 0);
                array_push($out_data, isset($data['traf_out']) ? $data['traf_out'] : 0);
                array_push($tot_data, isset($data['traf_total']) ? $data['traf_total'] : 0);
                $average += $data['traf_total'];
            }

            $ave_count = count($tot_data);
        } else {
            exit("Unknown graph type $imgtype");
        }//end if

        $average = ($average / $ave_count);
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
