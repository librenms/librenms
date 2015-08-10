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
 */

$filter_hostname = mres($_POST['hostname']);
$filter_range = mres($_POST['range']);

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $query = 'message:"'.$searchPhrase.'"';
}
else {
    $query = '*';
}

if (isset($current)) {
    $offset = ($current * $rowCount) - ($rowCount);
    $limit = $rowCount;
}

if ($rowCount != -1) {
    $extra_query = "&limit=$limit&offset=$offset";
}

if (!empty($filter_hostname)) {
    if (!empty($query)) {
        $query .= ' && ';
    }
    $query .= 'source:"'.$filter_hostname.'"';
}

$graylog_url = $config['graylog']['server'] . ':' . $config['graylog']['port'] . '/search/universal/relative?query=' . urlencode($query) . '&range='. $filter_range . $extra_query;

$context = stream_context_create(array(
    'http' => array(
        'header'  => "Authorization: Basic " . base64_encode($config['graylog']['username'].':'.$config['graylog']['password'])
    )
));

$messages = json_decode(file_get_contents($graylog_url, false, $context),true);

foreach ($messages['messages'] as $message) {
    $response[] = array(
                      'timestamp' => $message['message']['timestamp'],
                      'source'    => $message['message']['source'],
                      'message'    => $message['message']['message'],
                      'facility'  => $message['message']['facility'],
                      'level'     => $message['message']['level'],
    );
}

$total = $messages['total_results'];

$output = array('current'=>$current,'rowCount'=>$rowCount,'rows'=>$response,'total'=>$total);
echo _json_encode($output);
