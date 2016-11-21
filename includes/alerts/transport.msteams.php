/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$url = $opts['url'];
$color = ($obj['state'] == 0 ? '#00FF00' : '#FF0000');
$data = array(
    'title' => ($obj['name'] ? $obj['name'] . ' on ' . $obj['hostname'] : $obj['title']) ,
    'themeColor' => $color ,
    'text' => strip_tags($obj['msg'])
);
$curl = curl_init();
set_curl_proxy($curl);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    'Content-type' => 'application/json',
    'Expect:'
));
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
$ret = curl_exec($curl);
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ($code != 200) {
    var_dump("Microsoft Teams returned Error, retry later");
    return false;
}

return true;
