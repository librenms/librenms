<?php
/**
 * Bills.php
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

namespace LibreNMS\Reporting;

use App\Models\Billing;
use Auth;
use Illuminate\Support\Facades\DB;
use LibreNMS\Config;
use DateTime;
use DateTimeZone;
use LibreNMS\Common\Functions;

class Bills
{
	public function get_list($params) 
	{
		$prev = ! empty($params['period']) && ($params['period'] == 'prev');
		$query = Billing::query();
		
		$className = 'LibreNMS\\Common\\Functions';
		$Functions = new $className;
		
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
			$query = $query->orderBy('B.bill_id');
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

	
	public function expand_list($json, $params)
	{
		$className = 'LibreNMS\\Common\\Functions';
		$Functions = new Functions;

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
			
			$rate_95th = $Functions->format_si($bill->rate_95th) . 'bps';
			$dir_95th = $bill->dir_95th;
			$total_data = $Functions->format_bytes_billing($bill->total_data);
			$rate_average = $bill->rate_average;
			$url = $Functions->generate_url(['page' => 'bill', 'bill_id' => $bill->bill_id]);
			$used95th = $Functions->format_si($bill->rate_95th) . 'bps';
			$notes = $bill->bill_notes;

			if (strtolower($bill->bill_type) == 'cdr') {
				$type = 'CDR';
				$allowed = $Functions->format_si($bill->bill_allowed) . 'bps';
				$in = $Functions->format_si($bill->rate_95th_in) . 'bps';
				$out = $Functions->format_si($bill->rate_95th_out) . 'bps';
				if (! $prev) {
					$percent = round((($bill->rate_95th / $bill->bill_allowed) * 100), 2);
					$overuse = ($bill->rate_95th - $bill->bill_allowed);
				}

				$overuse_formatted = $Functions->format_si($overuse) . 'bps';
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
				$tmp_used = $bill->total_data;
				$total_data = "<b>$total_data</b>";
			}

			$background = $Functions->get_percentage_colours($percent);
			$right_background = $background['right'];
			$left_background = $background['left'];
			$overuse_formatted = (($overuse <= 0) ? '-' : "<span style='color: #${background['left']}; font-weight: bold;'>$overuse_formatted</span>");

			$bill_name = "<a href='$url'><span style='font-weight: bold;' class='interface'>$bill->bill_name</span></a><br />" .
							strftime('%F', strtotime($datefrom)) . ' to ' . strftime('%F', strtotime($dateto));
			$bar = $Functions->print_percentage_bar(120, 20, $percent, $percent . '%', 'black', $background['left'], null, 'black', $background['right']);

			if (strtolower($bill->bill_type) == 'cdr') {
				$predicted = $Functions->format_si($Functions->getPredictedUsage($bill->bill_day, $tmp_used)) . 'bps';
			} elseif (strtolower($bill->bill_type) == 'quota') {
				$predicted = $Functions->format_bytes_billing($Functions->getPredictedUsage($bill->bill_day, $tmp_used));
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
				'overusage'     => $overuse_formatted,
				'predicted'     => $predicted,
				'graph'         => $bar,
			];
		}
		return json_encode($response);		
	}
}
