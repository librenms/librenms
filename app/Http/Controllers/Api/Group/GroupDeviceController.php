<?php

namespace App\Http\Controllers\Api\Group;

use App\Models\DeviceGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class GroupDeviceController extends ApiController
{
    /**
     *
     * @api {get} /api/v1/groups/device Get Device Groups
     * @apiName Get_Device_groups
     * @apiGroup Group
     * @apiVersion  1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          [
     *              {
     *                  "id": 1,
     *                  "name": "Test",
     *                  "desc": "teast",
     *                  "pattern": "devices.status = '1'",
     *                  "params": null,
     *                  "patternSql": "devices.status = '1'"
     *              }
     *          ]
     *     }
     *
     */
    public function index()
    {
        return $this->objectResponse(DeviceGroup::all());
    }

    /**
     *
     * @api {get} /api/v1/groups/device Get Devices in Device Group
     * @apiName Get_Devices_In_Group
     * @apiGroup Group
     * @apiVersion  1.0.0
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *              {
     *                  "device_id": 3,
     *                  "hostname": "10.1.1.1",
     *                  "sysName": "SysName",
     *                  "ip": null,
     *                  "community": "public",
     *                  "authlevel": null,
     *                  "authname": null,
     *                  "authpass": null,
     *                  "authalgo": null,
     *                  "cryptopass": null,
     *                  "cryptoalgo": null,
     *                  "snmpver": "v2c",
     *                  "port": "161",
     *                  "transport": "udp",
     *                  "timeout": null,
     *                  "retries": null,
     *                  "snmp_disable": "0",
     *                  "bgpLocalAs": "1",
     *                  "sysObjectID": ".1.3.6.1.4.1.161.19.250.256",
     *                  "sysDescr": "CANOPY 15.0.0.1 AP-DES",
     *                  "sysContact": "user@example.org",
     *                  "version": "CANOPY 15.0.0.1 AP-DES",
     *                  "hardware": "PMP 450 AP",
     *                  "features": null,
     *                  "location": "NOC",
     *                  "os": "pmp",
     *                  "status": "1",
     *                  "status_reason": "",
     *                  "ignore": "0",
     *                  "disabled": "0",
     *                  "uptime": "3640435",
     *                  "agent_uptime": "0",
     *                  "last_polled": "2018-05-23 14:35:47",
     *                  "last_poll_attempted": null,
     *                  "last_polled_timetaken": "39.28",
     *                  "last_discovered_timetaken": "13.12",
     *                  "last_discovered": "2018-05-23 12:34:25",
     *                  "last_ping": "2018-05-23 14:35:47",
     *                  "last_ping_timetaken": "12.20",
     *                  "purpose": "",
     *                  "type": "wireless",
     *                  "serial": null,
     *                  "icon": "http://example.org/images/os/cambium.svg",
     *                  "poller_group": "0",
     *                  "override_sysLocation": "0",
     *                  "notes": null,
     *                  "port_association_mode": "1",
     *                  "pivot": {
     *                      "device_group_id": "2",
     *                      "device_id": "3"
     *                  }
     *              }
     *          ],
     *          "current_page": 1,
     *          "from": 2,
     *          "last_page": 10,
     *          "next_page_url": "http://example.org/api/v1/groups/devices/2?page=3",
     *          "path": "http://example.org/api/v1/groups/devices/2",
     *          "per_page": "1",
     *          "prev_page_url": "http://example.org/api/v1/groups/devices/2?page=1",
     *          "to": 2,
     *          "total": 10
     *      }
     *
     * @apiErrorExample {json} Error-Response:
     *      HTTP/1.1 404 Not-Found
     *      {
     *          "status": "Item not Found"
     *      }
     */
    public function show($device_group_id)
    {
        $group = DeviceGroup::findOrFail($device_group_id);
        return $this->paginateResponse($group->devices());
    }
}
