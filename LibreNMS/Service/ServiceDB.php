<?php

/**
 * ServiceDB.php
 *
 * Service monitoring database operations.
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     LibreNMS
 * @link        http://librenms.org
 * @copyright   2019, KanREN Inc.
 * @author      Heath Barnhart <hbarnhart@kanren.net>
 */

namespace Service;

use Log;

class ServiceDB
{
    /**
     * Creates an entry in the services table
     * @param array $device Array containing device attributes
     * @param string $type Service type
     * @param string $desc Service description
     * @param string $ip Service target IP address
     * @param string $param Nagios plugin parameters
     * @param int $ignore Service ignore state
     * @param int $disabled Service disabled state
     * @returns PDO|null
    **/
    public static function addService($device, $type, $desc, $ip = 'localhost', $param = "", $ignore = 0, $disabled = 0)
    {

        if (!is_array($device)) {
            $device = device_by_id_cache($device);
        }

        if (empty($ip)) {
            $ip = $device['hostname'];
        }

        $insert = array('device_id' => $device['device_id'], 'service_ip' => $ip, 'service_type' => $type, 'service_changed' => array('UNIX_TIMESTAMP(NOW())'), 'service_desc' => $desc, 'service_param' => $param, 'service_ignore' => $ignore, 'service_status' => 3, 'service_message' => 'Service not yet checked', 'service_ds' => '{}', 'service_disabled' => $disabled);
        return dbInsert($insert, 'services');
    }

    /**
     * Converts Nagios return codes into LibreNMS severity levels
     * @param int $old_status Previous return code from Nagios Plugin
     * @param int $new_status New return code
     * @return mixed Returns the text values of the return code and a LMNS status number
    **/
    static function getSeverity($old_status, $new_status)
    {
        $status_text = array(0 => 'OK', 1 => 'Warning', 2 => 'Critical', 3 => 'Unknown');
        $oldText = isset($status_text[$old_status]) ? $status_text[$old_status] : 'Unknown';
        $newText = isset($status_text[$new_status]) ? $status_text[$new_status] : 'Unknown';

        switch ($new_status) {
            case 3:
                $severity = 3;
                break;
            case 2:
                $severity = 5;
                break;
            case 1:
                $severity = 4;
                break;
            case 0:
                $severity = 0;
        }

        return array ($oldText, $newText, $severity);
    }

    /**
     * Finds rows from services for matching device and/or service
     * @param array $device Device attributes array
     * @param int $deviceId Device Id number
     * @param int @serviceId Service Id number
     * @return mixed Array of service attributes for specificed device id, service id pair
    **/
    public static function findService($device = null, $service = null)
    {
        $sql_query = "SELECT `service_id`,`device_id`,`service_ip`,`service_type`,`service_desc`,`service_param`,`service_ignore`,`service_status`,`service_changed`,`service_message`,`service_disabled`,`service_ds` FROM `services` WHERE";
        $sql_param = array();
        $add = 0;

        d_echo("SQL Query: ".$sql_query);
        if (!is_null($service)) {
            // Add a service filter to the SQL query.
            $sql_query .= " `service_id` = ? AND";
            $sql_param[] = $service;
            $add++;
        }
        if (!is_null($device)) {
            // Add a device filter to the SQL query.
            $sql_query .= " `device_id` = ? AND";
            $sql_param[] = $device;
            $add++;
        }

        if ($add == 0) {
            // No filters, remove " WHERE" -6
            $sql_query = substr($sql_query, 0, strlen($sql_query)-6);
        } else {
            // We have filters, remove " AND" -4
            $sql_query = substr($sql_query, 0, strlen($sql_query)-4);
        }
        d_echo("SQL Query: ".$sql_query);

        // $service is not null, get only what we want.
        $services = dbFetchRows($sql_query, $sql_param);
        d_echo("Service Array: ".print_r($services, true)."\n");

        return $services;
    }

    /**
     * Edit a service's properties
     * @param array $update Service attributes to update
     * @param int $service Service ID
     * @return dbUpdate|null Updates the database and returns success
    **/
    public static function editService($update = array(), $serviceId = null)
    {
        if (!is_numeric($serviceId)) {
            return false;
        }

        return dbUpdate($update, 'services', '`service_id`=?', array($service));
    }

    /**
     * Delete a service entry
     * @param int $serviceId
     * @return dbDelete|null Updates the database and returns success
    **/
    public static function deleteService($service = null)
    {
        if (!is_numeric($service)) {
            return false;
        }

        return dbDelete('services', '`service_id` =  ?', array($service));
    }

    /**
     * List all available services from nagios plugins directory
     *
     * @return array
     */
    function listServices()
    {
        global $config;
        $services = array();
        foreach (scandir($config['nagios_plugins']) as $file) {
            if (substr($file, 0, 6) === 'check_') {
                $services[] = substr($file, 6);
            }
        }
        return $services;
    }
}
