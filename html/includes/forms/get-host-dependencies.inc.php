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
 
if (is_admin() === false) {
    $status = array('status' => 1, 'message' => 'You need to be admin');
} else {
    if (isset($_POST['viewtype'])) {
        if ($_POST['viewtype'] == 'fulllist') {
            $count_query = "SELECT count(device_id) from devices";

            $deps_query = "SELECT  a.device_id as id, a.hostname as hostname, GROUP_CONCAT(b.hostname ORDER BY b.device_id) parents, a.parent_id as parentid FROM devices a LEFT JOIN devices b ON FIND_IN_SET(b.device_id, a.parent_id) > 0 GROUP BY a.device_id";

            if (isset($_POST['format'])) {
                if (isset($_POST['searchPhrase']) && !empty($_POST['searchPhrase'])) {
                    #This is a bit ugly
                    $deps_query = "SELECT * FROM (".$deps_query;
                    $deps_query .= " ) as t WHERE t.hostname LIKE ? OR t.parent LIKE ? ";
                    $deps_query .= " ORDER BY t.hostname";
                } else {
                    $deps_query .= " ORDER BY a.hostname";
                }

                if (is_numeric($_POST['rowCount']) && is_numeric($_POST['current'])) {
                    $rows = $_POST['rowCount'];
                    $current = $_POST['current'];
                    $deps_query .= " LIMIT ".$rows * ($current - 1).", ".$rows;
                }
            } else {
                $deps_query .= " ORDER BY a.hostname";
            }

            if (isset($_POST['format']) && !empty($_POST['searchPhrase'])) {
                $searchphrase = '%'.$_POST['searchPhrase'].'%';
                $device_deps = dbFetchRows($deps_query, array($searchphrase, $searchphrase));
            } else {
                $device_deps = dbFetchRows($deps_query);
            }

            if (isset($_POST['searchPhrase']) && !empty($_POST['searchPhrase'])) {
                $rec_count = count($device_deps);
            } else {
                $rec_count = dbFetchCell($count_query);
            }

            if (isset($_POST['format'])) {
                $res_arr = array();
                foreach ($device_deps as $myrow) {
                    if ($myrow['parents'] == null || $myrow['parents'] == '') {
                        $parent = 'None';
                    } else {
                        $parent = $myrow['parents'];
                    }
                    
                    array_push($res_arr, array( "deviceid" => $myrow['id'], "hostname" => $myrow['hostname'], "parent" => $parent, "parentid" => $myrow['parentid'] ));
                }
                $status = array('current' => $_POST['current'], 'rowCount' => $_POST['rowCount'], 'rows' => $res_arr, 'total' => $rec_count);
            } else {
                $status = array('status' => 0, 'deps' => $device_deps);
            }
        } else {
            $device_deps = dbFetchRows('SELECT `device_id`,`hostname` from `devices` WHERE `parent_id` = ? ORDER BY `hostname` ASC', array($_POST['parent_id']));
            if ($_POST['viewtype'] == 'fromparent') {
                if ($_POST['parent_ids'] == 0) {
                    $device_deps = dbFetchRows('SELECT `device_id`,`hostname` from `devices` WHERE `parent_id` = 0 OR `parent_id` is null ORDER BY `hostname` ASC');
                } else {
                    $device_deps = dbFetchRows(
                        'SELECT `device_id`,`hostname` from `devices` WHERE `parent_id` = ? ORDER BY `hostname` ASC',
                        array($_POST['parent_ids'])
                    );
                }

                $status = array('status' => 0, 'deps' => $device_deps);
            }
        }
    } else {
        if (!is_numeric($_POST['device_id'])) {
            $status = array('status' => 1, 'message' => 'Wrong device id!');
        } else {
            $device_deps = dbFetchRows(
                'SELECT `device_id`,`hostname`,`parent_id` from `devices` WHERE `device_id` <>  ? ORDER BY `hostname` ASC',
                array($_POST['device_id'])
            );
            $status = array('status' => 0, 'deps' => $device_deps);
        }
    }
}
 
header('Content-Type: application/json');
echo _json_encode($status);
