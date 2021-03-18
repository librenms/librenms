<?php
/**
 * Billing.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2021 Mark Kneen
 * @author     Mark Kneen <mark.kneen@gmail.com>
 */

namespace LibreNMS\Billing;


use App\Models\Billing;
use Auth;
use Illuminate\Support\Facades\DB;
use LibreNMS\Common\Functions;
use LibreNMS\Config;
use DateTime;
use DateTimeZone;

class Bills
{
	public function get_bill_list($params) 
	{
		$prev = ! empty($params['period']) && ($params['period'] == 'prev');

		$query = Billing::query();

		if ($prev){
			$query = $query->select('B.bill_name', 'B.bill_notes', 'B.bill_day', 'bill_history.*', 'bill_history.traf_total as total_data', 'bill_history.traf_in as total_data_in', 'bill_history.traf_out as total_data_out')
				->from('bills as B')
				->join(DB::raw('(SELECT bill_id, MAX(bill_hist_id) AS bill_hist_id FROM bill_history WHERE bill_dateto < NOW() AND bill_dateto > subdate(NOW(), 40) GROUP BY bill_id) qLastBills'), 'B.bill_id', '=', 'qLastBills.bill_id')
				->join('bill_history', 'qLastBills.bill_hist_id', '=', 'bill_history.bill_hist_id');
		}else {
			$query = $query->select('B.*',DB::raw('IF(B.bill_type = "CDR", bill_cdr, bill_quota) AS bill_allowed'))
				->from('bills as B');
		}

		// Permissions check
		if (! Auth::user()->hasGlobalRead()) {
			$query = $query->join('bill_perms AS BP','B.bill_id', '=', 'BP.bill_id')
						->where('BP.user_id', '=', Auth::id());
		}

		$searchPhrase = $params['searchPhrase'];
		if (isset($params['searchPhrase']) && ! empty($params['searchPhrase'])) {
			$query = $query->where('B.bill_name', 'LIKE', "%$searchPhrase%");
		}

		if (! empty($params['bill_type'])) {
			if ($prev) {
				$query = $query->where('bill_history.bill_type', '=', $params['bill_type']);
			} else {
				$query = $query->where('bill_type', '=', $params['bill_type']);
			}
		}

		if (! empty($params['state'])) {
			if ($params['state'] === 'under') {
				if ($prev) {
					$query = $query->where(DB::raw('bill_history.bill_type = \'cdr\' AND bill_history.rate_95th <= bill_history.bill_allowed'))
								->orWhere(DB::raw('bill_history.bill_type = \'quota\' AND bill_history.traf_total <= bill_history.bill_allowed'));
				} else {
					$query = $query->where(DB::raw('bill_type = \'cdr\' AND rate_95th <= bill_cdr'))
								->orWhere(DB::raw('bill_type = \'cdr\' AND rate_95th <= bill_cdr'))
								->orWhere(DB::raw('bill_type = \'quota\' AND total_data <= bill_quota'));
				}
			} elseif ($params['state'] === 'over') {
				if ($prev) {
					$query = $query->where(DB::raw('bill_history.bill_type = \'cdr\' AND bill_history.rate_95th > bill_history.bill_allowed'))
								->orWhere(DB::raw('bill_history.bill_type = \'quota\' AND bill_history.traf_total > bill_allowed'));
				} else {
					$query = $query->where(DB::raw('bill_type = \'cdr\' AND rate_95th > bill_cdr'))
								->orWhere(DB::raw('bill_type = \'quota\' AND total_data > bill_quota'));
				}
			}
		}

		if (! isset($sort) || empty($sort)) {
			$query = $query->orderBy('B.bill_name');
		}

		if (isset($params['current'])) {
			$limit_low = (($params['current'] * $params['rowCount']) - ($params['rowCount']));
			$limit_high = $params['rowCount'];
		}

		if (isset($params['rowCount'])) {
			if ($params['rowCount'] != -1) {
				$query = $query->offset($limit_low)
					->limit($limit_high);
			}
		}
		$query2= clone $query;
		$total =  count(
				json_decode(
					$query2->select(DB::raw('COUNT(B.bill_id) as count'))->groupBy('B.bill_id')->get()
					)
				);
				
		
		$query	= $query->get();
		$json 	= $query->toJson();
		
		$output	= ['json' => $json, 'total' => $total];

		return $output;
	}
	
	
/// ----------------------------------------------------------------------------------------------------------------------------
// So this is where im at the limit of my knowledge - I know this is not the way to do this 
// help would be very much appreciated to "do the the right way"
// Basically cheating and coping functions from other files. 
/// ----------------------------------------------------------------------------------------------------------------------------

	private function zeropad($num, $length = 2)
	{
		return str_pad($num, $length, '0', STR_PAD_LEFT);
	}

	private function getDates($dayofmonth, $months = 0)
	{
		$dayofmonth = $this->zeropad($dayofmonth);
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

	private function getPredictedUsage($bill_day, $cur_used)
	{
		$tmp = $this->getDates($bill_day, 0);
		$start = new DateTime($tmp[0], new DateTimeZone(date_default_timezone_get()));
		$end = new DateTime($tmp[1], new DateTimeZone(date_default_timezone_get()));
		$now = new DateTime(date('Y-m-d'), new DateTimeZone(date_default_timezone_get()));
		$total = $end->diff($start)->format('%a');
		$since = $now->diff($start)->format('%a');

		return $cur_used / $since * $total;
	}
	private function format_si($value, $round = 2, $sf = 3)
	{
		return \LibreNMS\Util\Number::formatSi($value, $round, $sf, '');
	}

	private function format_bytes_billing($value)
	{
		return $this->format_number($value, Config::get('billing.base')) . 'B';
	}//end format_bytes_billing()

	private function format_number($value, $base = 1000, $round = 2, $sf = 3)
	{
		return \LibreNMS\Util\Number::formatBase($value, $base, $round, $sf, '');
	}
	private function generate_url($vars, $new_vars = [])
	{
		return \LibreNMS\Util\Url::generate($vars, $new_vars);
	}
	private function get_percentage_colours($percentage, $component_perc_warn = null)
	{
		return \LibreNMS\Util\Colors::percentage($percentage, $component_perc_warn);
	}
	private function print_percentage_bar($width, $height, $percent, $left_text, $left_colour, $left_background, $right_text, $right_colour, $right_background)
	{
		return \LibreNMS\Util\Html::percentageBar($width, $height, $percent, $left_text, $right_text, null, null, [
			'left' => $left_background,
			'left_text' => $left_colour,
			'right' => $right_background,
			'right_text' => $right_colour,
		]);
	}

/// ----------------------------------------------------------------------------------------------------------------------------
	
	
	
	public function expand_bill_list($json, $params)
	{
		$json = json_decode($json);
		
		$prev = ! empty($params['period']) && ($params['period'] == 'prev');
		
		foreach($json as $key => $bill) {
			if ($prev) {
				$datefrom = $bill->bill_datefrom;
				$dateto = $bill->bill_dateto;
				$percent = $bill->bill_percent;
				$overuse = $bill->bill_overuse;
			} else {
				$day_data = getDates($bill->bill_day);
				$datefrom = $day_data['0'];
				$dateto = $day_data['1'];
			}
			
			$rate_95th = $this->format_si($bill->rate_95th) . 'bps';
			$dir_95th = $bill->dir_95th;
			$total_data = $this->format_bytes_billing($bill->total_data);
			$rate_average = $bill->rate_average;
			$url = $this->generate_url(['page' => 'bill', 'bill_id' => $bill->bill_id]);
			$used95th = $this->format_si($bill->rate_95th) . 'bps';
			$notes = $bill->bill_notes;

			if (strtolower($bill->bill_type) == 'cdr') {
				$type = 'CDR';
				$allowed = $this->format_si($bill->bill_allowed) . 'bps';
				$in = $this->format_si($bill->rate_95th_in) . 'bps';
				$out = $this->format_si($bill->rate_95th_out) . 'bps';
				if (! $prev) {
					$percent = round((($bill->rate_95th / $bill->bill_allowed) * 100), 2);
					$overuse = ($bill->rate_95th - $bill->bill_allowed);
				}

				$overuse_formatted = $this->format_si($overuse) . 'bps';
				$used = $rate_95th;
				$tmp_used = $bill->rate_95th;
				$rate_95th = "<b>$rate_95th</b>";
			} elseif (strtolower($bill->bill_type) == 'quota') {
				$type = 'Quota';
				$allowed = format_bytes_billing($bill->bill_allowed);
				if (! empty($prev)) {
					$in = format_bytes_billing($bill->traf_in);
					$out = format_bytes_billing($bill->traf_out);
				} else {
					$in = format_bytes_billing($bill->total_data_in);
					$out = format_bytes_billing($bill->total_data_out);
				}
				if (! $prev) {
					$percent = round((($bill->total_data / ($bill->bill_allowed)) * 100), 2);
					$overuse = ($bill->total_data - $bill->bill_allowed);
				}

				$overuse_formatted = format_bytes_billing($overuse);
				$used = $total_data;
				$tmp_used = $bill->total_data;
				$total_data = "<b>$total_data</b>";
			}

			$background = $this->get_percentage_colours($percent);
			$right_background = $background['right'];
			$left_background = $background['left'];
			$overuse_formatted = (($overuse <= 0) ? '-' : "<span style='color: #${background['left']}; font-weight: bold;'>$overuse_formatted</span>");

			$bill_name = "<a href='$url'><span style='font-weight: bold;' class='interface'>$bill->bill_name}</span></a><br />" .
							strftime('%F', strtotime($datefrom)) . ' to ' . strftime('%F', strtotime($dateto));
			$bar = $this->print_percentage_bar(250, 20, $percent, null, 'ffffff', $background['left'], $percent . '%', 'ffffff', $background['right']);
			$actions = '';

			if (! $prev && Auth::user()->hasGlobalAdmin()) {
				$actions .= "<a href='" . $this->generate_url(['page' => 'bill', 'bill_id' => $bill->bill_id, 'view' => 'edit']) .
					"'><i class='fa fa-pencil fa-lg icon-theme' title='Edit' aria-hidden='true'></i> Edit</a> ";
			}
			if (strtolower($bill->bill_type) == 'cdr') {
				$predicted = $this->format_si($this->getPredictedUsage($bill->bill_day, $tmp_used)) . 'bps';
			} elseif (strtolower($bill->bill_type) == 'quota') {
				$predicted = $this->format_bytes_billing($this->getPredictedUsage($bill->bill_day, $tmp_used));
			}

			$response[] = [
				'bill_name'     => $bill_name,
				'notes'         => $notes,
				'bill_type'     => $type,
				'bill_allowed'    => $allowed,
				'total_data_in' => $in,
				'total_data_out'=> $out,
				'total_data'    => $total_data,
				'rate_95th'     => $rate_95th,
				'used'          => $used,
				'overusage'     => $overuse_formatted,
				'predicted'     => $predicted,
				'graph'         => $bar,
				'actions'       => $actions,
			];
		}
		return json_encode($response);		
	}
}
