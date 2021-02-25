<?php
	// SQL Query
	$wheres = [];
	$param = [];
	$select = 'SELECT bills.bill_name, bills.bill_notes, bill_history.*, bill_history.traf_total as total_data, bill_history.traf_in as total_data_in, bill_history.traf_out as total_data_out ';
	$query = 'FROM `bills`
		INNER JOIN (SELECT bill_id, MAX(bill_hist_id) AS bill_hist_id FROM bill_history WHERE bill_dateto < NOW() AND bill_dateto > subdate(NOW(), 40) GROUP BY bill_id) qLastBills ON bills.bill_id = qLastBills.bill_id
		INNER JOIN bill_history ON qLastBills.bill_hist_id = bill_history.bill_hist_id';

	$orderby = 'ORDER BY bills.bill_name';

	$sql = "$select $query $orderby";
	
	foreach (dbFetchRows($sql, $param) as $index => $bill) {
		$datefrom = $bill['bill_datefrom'];
		$dateto = $bill['bill_dateto'];

		$rate_95th = format_si($bill['rate_95th']) . 'bps';
		$dir_95th = $bill['dir_95th'];
		$total_data = format_bytes_billing($bill['total_data']);
		$rate_average = $bill['rate_average'];
		$url = generate_url(['page' => 'bill', 'bill_id' => $bill['bill_id']]);
		$used95th = format_si($bill['rate_95th']) . 'bps';
		$notes = $bill['bill_notes'];

		$percent = $bill['bill_percent'];
		$overuse = $bill['bill_overuse'];

		if (strtolower($bill['bill_type']) == 'cdr') {
			$type = 'CDR';
			$allowed = format_si($bill['bill_allowed']) . 'bps';
			$in = format_si($bill['rate_95th_in']) . 'bps';
			$out = format_si($bill['rate_95th_out']) . 'bps';
			if (! $prev) {
				$percent = round((($bill['rate_95th'] / $bill['bill_allowed']) * 100), 2);
				$overuse = ($bill['rate_95th'] - $bill['bill_allowed']);
			}

			$overuse_formatted = format_si($overuse) . 'bps';
			$used = $rate_95th;
			$tmp_used = $bill['rate_95th'];
			$rate_95th = "<b>$rate_95th</b>";
		} elseif (strtolower($bill['bill_type']) == 'quota') {
			$type = 'Quota';
			$allowed = format_bytes_billing($bill['bill_allowed']);
			if (! empty($prev)) {
				$in = format_bytes_billing($bill['traf_in']);
				$out = format_bytes_billing($bill['traf_out']);
			} else {
				$in = format_bytes_billing($bill['total_data_in']);
				$out = format_bytes_billing($bill['total_data_out']);
			}
			$percent = round((($bill['total_data'] / ($bill['bill_allowed'])) * 100), 2);
			$overuse = ($bill['total_data'] - $bill['bill_allowed']);

			$overuse_formatted = format_bytes_billing($overuse);
			$used = $total_data;
			$tmp_used = $bill['total_data'];
			$total_data = "<b>$total_data</b>";
		}

		$background = get_percentage_colours($percent);
		$right_background = $background['right'];
		$left_background = $background['left'];
		$overuse_formatted = (($overuse <= 0) ? '-' : "<span style='color: #${background['left']}; font-weight: bold;'>$overuse_formatted</span>");
		$bill_name = "<a href='$url'><span style='font-weight: bold;' class='interface'>${bill['bill_name']}</span></a><br />" .
		strftime('%F', strtotime($datefrom)) . ' to ' . strftime('%F', strtotime($dateto));
		$bar = print_percentage_bar('250', 20, $percent, null, 'ffffff', $background['left'], $percent . '%', 'ffffff', $background['right']);

		if (strtolower($bill['bill_type']) == 'cdr') {
			$predicted = format_si(getPredictedUsage($bill['bill_day'], $tmp_used)) . 'bps';
		} elseif (strtolower($bill['bill_type']) == 'quota') {
			$predicted = format_bytes_billing(getPredictedUsage($bill['bill_day'], $tmp_used));
		}


		$response[] = [
			'bill_id'	=> $bill['bill_id'],
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
			'percent'	=> $percent,
			'background'	=> $background,
		];
	}
$json = _json_encode($response);
