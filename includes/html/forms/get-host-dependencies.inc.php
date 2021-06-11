<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2017 Aldemir Akpinar <https://github.com/aldemira/>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (! Auth::user()->hasGlobalAdmin()) {
    $status = ['status' => 1, 'message' => 'You need to be admin'];
} else {
    if (isset($_POST['viewtype'])) {
        if ($_POST['viewtype'] == 'fulllist') {
            $deps_query = 'SELECT a.device_id as id, a.hostname as hostname, a.sysName as sysName, GROUP_CONCAT(b.hostname) as parent, GROUP_CONCAT(b.device_id) as parentid FROM devices as a LEFT JOIN device_relationships a1 ON a.device_id=a1.child_device_id LEFT JOIN devices b ON b.device_id = a1.parent_device_id GROUP BY a.device_id, a.hostname, a.sysName';

            if (isset($_POST['searchPhrase']) && ! empty($_POST['searchPhrase'])) {
                $deps_query .= ' HAVING parent LIKE ? OR hostname LIKE ? OR sysName LIKE ? ';
                $count_query = 'SELECT COUNT(*) FROM (' . $deps_query . ') AS rowcount';
            } else {
                $count_query = 'SELECT COUNT(device_id) AS rowcount FROM devices';
            }

            // if format is set we're trying to pull the Bootgrid table data
            if (isset($_POST['format'])) {
                $order_by = '';
                if (isset($_POST['sort']) && is_array($_REQUEST['sort'])) {
                    foreach ($_REQUEST['sort'] as $key => $value) {
                        $order_by .= " $key $value";
                    }
                } else {
                    $order_by = ' a.hostname';
                }

                $deps_query .= ' ORDER BY ' . $order_by;

                if (is_numeric($_POST['rowCount']) && is_numeric($_POST['current'])) {
                    $rows = $_POST['rowCount'];
                    $current = $_POST['current'];
                    if ($rows > 0) {
                        $deps_query .= ' LIMIT ' . $rows * ($current - 1) . ', ' . $rows;
                    }
                }
            } else {
                $deps_query .= ' ORDER BY a.hostname';
            }

            if (isset($_POST['format']) && ! empty($_POST['searchPhrase'])) {
                $searchphrase = '%' . $_POST['searchPhrase'] . '%';
                $search_arr = [$searchphrase, $searchphrase, $searchphrase];
                $device_deps = dbFetchRows($deps_query, $search_arr);
                $rec_count = dbFetchCell($count_query, $search_arr);
            } else {
                $device_deps = dbFetchRows($deps_query);
                $rec_count = dbFetchCell($count_query);
            }

            if (isset($_POST['format'])) {
                $res_arr = [];
                foreach ($device_deps as $myrow) {
                    if ($myrow['parent'] == null || $myrow['parent'] == '') {
                        $parent = 'None';
                    } else {
                        $parent = $myrow['parent'];
                    }

                    $hostname = format_hostname($myrow);
                    $sysname = ($hostname == $myrow['sysName']) ? $myrow['hostname'] : $myrow['sysName'];
                    array_push($res_arr, ['deviceid' => $myrow['id'], 'hostname' => $hostname, 'sysname' => $sysname, 'parent' => $parent, 'parentid' => $myrow['parentid']]);
                }
                $status = ['current' => $_POST['current'], 'rowCount' => $_POST['rowCount'], 'rows' => $res_arr, 'total' => $rec_count];
            } else {
                $status = ['status' => 0, 'deps' => $device_deps];
            }
        } else {
            // Get childs from parent id(s)
            if ($_POST['viewtype'] == 'fromparent') {
                if ($_POST['parent_ids'] == 0) {
                    $device_deps = dbFetchRows('SELECT `device_id`,`hostname` from `devices` as a LEFT JOIN `device_relationships` as b ON b.`child_device_id` =  a.`device_id` WHERE b.`child_device_id` is null ORDER BY `hostname`');
                } else {
                    $parents = implode(',', $_POST['parent_ids']);
                    $device_deps = dbFetchRows('SELECT  a.device_id as device_id, a.hostname as hostname, GROUP_CONCAT(b.hostname) as parent, GROUP_CONCAT(b.device_id) as parentid FROM devices as a LEFT JOIN device_relationships a1 ON a.device_id=a1.child_device_id LEFT JOIN devices b ON b.device_id=a1.parent_device_id GROUP BY a.device_id, a.hostname HAVING parentid = ?', [$parents]);
                }

                $status = ['status' => 0, 'deps' => $device_deps];
            }
        }
    } else {
        // Find devices by child.
        if (! is_numeric($_POST['device_id'])) {
            $status = ['status' => 1, 'message' => 'Wrong device id!'];
        } else {
            $deps_query = 'SELECT `device_id`, `hostname` FROM `devices` AS a INNER JOIN `device_relationships` AS b ON a.`device_id` = b.`parent_device_id` WHERE ';
            // device_id == 0 is the case where we have no parents.
            if ($_POST['device_id'] == 0) {
                $device_deps = dbFetchRows($deps_query . ' b.`parent_device_id` is null OR b.`parent_device_id` = 0 ');
            } else {
                $device_deps = dbFetchRows($deps_query . ' b.`child_device_id` = ?', [$_POST['device_id']]);
            }
            $status = ['status' => 0, 'deps' => $device_deps];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
