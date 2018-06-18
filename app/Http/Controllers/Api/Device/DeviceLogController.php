<?php

namespace App\Http\Controllers\Api\Device;

use App\Models\Device;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class DeviceLogController extends ApiController
{
    /**
     * @api {get} /api/v1/devices/:id/logs/syslog Get device syslogs
     * @apiName Get_device_syslogs
     * @apiGroup Device Logs
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id The id or Hostname of the device.
     * @apiParam {String} [to] The data and time to search to.
     * @apiParam {String} [from] The date and time to search from.
     * @apiUse Pagination
     *
     * @apiExample {curl} Example usage:
     *     curl -H 'X-Auth-Token: YOURAPITOKENHERE' -H 'Content-Type: application/json' -i http://example.org/api/v1/devices/1/logs/syslog?from=2018-05-28 12:00
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *            {
     *                "seq": "2",
     *                "device_id": "2",
     *                "timestamp": "2018-04-25 00:16:02",
     *                "level": "10",
     *                "tag": "...",
     *                "facility": "...",
     *                "program": "...",
     *                "msg": "..."
     *            }
     *        ],
     *          "current_page": 1,
     *          "from": 1,
     *          "last_page": 1789,
     *          "next_page_url": "http://example.org/api/v1/devices/:id/logs/syslog?page=2",
     *          "path": "http://example.org/api/v1/devices/:id/logs/syslog",
     *          "per_page": 50,
     *          "prev_page_url": null,
     *          "to": 50,
     *          "total": 89439
     *     }
     *
     */
    public function syslog(Request $request, Device $device)
    {

        $to = $request->get('to', null);
        $from = $request->get('from', null);

        $query = $device->syslogs();

        if ($to) {
            $query = $query->where('timestamp', '<', $to);
        }
        if ($from) {
            $query = $query->where('timestamp', '>=', $from);
        }

        return $this->paginateResponse($query);
    }

    /**
     * @api {get} /api/v1/devices/:id/logs/eventlog Get device eventlogs
     * @apiName Get_device_eventlogs
     * @apiGroup Device Logs
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id The id or Hostname of the device.
     * @apiParam {String} [to] The data and time to search to.
     * @apiParam {String} [from] The date and time to search from.
     * @apiParam {Number} [per_page=50] How many items to retrieve
     * @apiParam {Number} [current_page=1] Active page of items
     *
     * @apiExample {curl} Example usage:
     *     curl -H 'X-Auth-Token: YOURAPITOKENHERE' -H 'Content-Type: application/json' -i http://example.org/api/v1/devices/1/logs/eventlog?from=2018-05-28 12:00&to=2018-05-30 12:00
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *              {
     *                  "event_id": 21662,
     *                  "host": "10",
     *                  "device_id": "10",
     *                  "datetime": "2018-04-25 00:16:02",
     *                  "message": "Could not issue critical alert for rule 'Devices up/down' to transport 'mail' Error: You must provide at least one recipient email address.",
     *                  "type": "error",
     *                  "reference": "NULL",
     *                  "username": "",
     *                  "severity": "5"
     *              },
     *          ],
     *          "current_page": 1,
     *          "from": 1,
     *          "last_page": 1789,
     *          "next_page_url": "http://example.org/api/v1/devices/:id/logs/eventlog?page=2",
     *          "path": "http://example.org/api/v1/devices/:id/logs/eventlog",
     *          "per_page": 50,
     *          "prev_page_url": null,
     *          "to": 50,
     *          "total": 89439
     *     }
     *
     */
    public function eventlog(Request $request, Device $device)
    {

        $to = $request->get('to', null);
        $from = $request->get('from', null);

        $query = $device->eventlogs();

        if ($to) {
            $query = $query->where('datetime', '<', $to);
        }
        if ($from) {
            $query = $query->where('datetime', '>=', $from);
        }

        return $this->paginateResponse($query);
    }

    /**
     * @api {get} /api/v1/devices/:id/logs/alertlog Get device alertlogs
     * @apiName Get_device_alertlogs
     * @apiGroup Device Logs
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id The id or Hostname of the device.
     * @apiParam {String} [to] The data and time to search to.
     * @apiParam {String} [from] The date and time to search from.
     * @apiParam {Number} [per_page=50] How many items to retrieve.
     * @apiParam {Number} [current_page=1] Active page of items.
     *
     * @apiExample {curl} Example usage:
     *     curl -H 'X-Auth-Token: YOURAPITOKENHERE' -H 'Content-Type: application/json' -i http://example.org/api/v1/devices/1/logs/alertlog?from=2018-05-28 12:00&to=2018-05-30
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *             {
     *                 "id": 21662,
     *                 "device_id": "10",
     *                 "rule_id": "12",
     *                 "time_logged": "2018-04-25 00:16:02",
     *                 "state": "0"
     *                 "message": "Could not issue critical alert for rule 'Devices up/down' to transport 'mail' Error: You must provide at least one recipient email address.",
     *              },
     *          ],
     *          "current_page": 1,
     *          "from": 1,
     *          "last_page": 1789,
     *          "next_page_url": "http://example.org/api/v1/devices/:id/logs/alertlog?page=2",
     *          "path": "http://example.org/api/v1/devices/:id/logs/alertlog",
     *          "per_page": 50,
     *          "prev_page_url": null,
     *          "to": 50,
     *          "total": 89439
     *     }
     *
     */
    public function alertlog(Request $request, Device $device)
    {

        $to = $request->get('to', null);
        $from = $request->get('from', null);

        $query = $device->alertlogs();

        if ($to) {
            $query = $query->where('time_logged', '<', $to);
        }
        if ($from) {
            $query = $query->where('time_logged', '>=', $from);
        }

        return $this->paginateResponse($query);
    }
}
