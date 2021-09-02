<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2018 LibreNMS
 * @author     LibreNMS Contributors
*/

use LibreNMS\Config;

$filter_hostname = $vars['hostname'];
$filter_range = $vars['range'];

if (isset($searchPhrase) && ! empty($searchPhrase)) {
    $query = 'message:"' . $searchPhrase . '"';
} else {
    $query = '*';
}

if (isset($current)) {
    $offset = ($current * $rowCount) - ($rowCount);
    $limit = $rowCount;
}

if ($rowCount != -1) {
    $extra_query = "&limit=$limit&offset=$offset";
}

if (! empty($filter_hostname)) {
    if (! empty($query)) {
        $query .= ' && ';
    }
    $ip = gethostbyname($filter_hostname);
    $device = device_by_name($filter_hostname);
    $query .= 'source:"' . $filter_hostname . '" || source:"' . $ip . '"';
    if (isset($device['ip']) && $ip != $device['ip']) {
        $query .= ' || source:"' . $device['ip'] . '"';
    }
}

if (Config::has('graylog.base_uri')) {
    $graylog_base = Config::get('graylog.base_uri');
} elseif (version_compare(Config::get('graylog.version'), '2.1', '>=')) {
    $graylog_base = '/api/search/universal/relative';
} else {
    $graylog_base = '/search/universal/relative';
}

$graylog_url = Config::get('graylog.server') . ':' . Config::get('graylog.port') . $graylog_base . '?query=' . urlencode($query) . '&range=' . $filter_range . $extra_query;

$context = stream_context_create([
    'http' => [
        'header' => 'Authorization: Basic ' . base64_encode(Config::get('graylog.username') . ':' . Config::get('graylog.password')) . "\r\n" .
                     'Accept: application/json',
    ],
]);

$messages = json_decode(file_get_contents($graylog_url, false, $context), true);

foreach ($messages['messages'] as $message) {
    if (Config::has('graylog.timezone')) {
        $userTimezone = new DateTimeZone(Config::get('graylog.timezone'));
        $graylogTime = new DateTime($message['message']['timestamp']);
        $offset = $userTimezone->getOffset($graylogTime);

        $timeInterval = DateInterval::createFromDateString((string) $offset . 'seconds');
        $graylogTime->add($timeInterval);
        $displayTime = $graylogTime->format('Y-m-d H:i:s');
    } else {
        $displayTime = $message['message']['timestamp'];
    }

    $response[] = [
        'timestamp' => graylog_severity_label($message['message']['level']) . $displayTime,
        'source'    => '<a href="' . \LibreNMS\Util\Url::generate(['page' => 'device', 'device' => $message['message']['source']]) . '">' . $message['message']['source'] . '</a>',
        'message'    => $message['message']['message'],
        'facility'  => $message['message']['facility'],
        'level'     => $message['message']['level'],
    ];
}

if (empty($messages['total_results'])) {
    $total = 0;
} else {
    $total = $messages['total_results'];
}

$output = ['current'=>$current, 'rowCount'=>$rowCount, 'rows'=>$response, 'total'=>$total];
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
