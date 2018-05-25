<?php

namespace App\Http\Controllers\Api;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class LogController extends ApiController
{
    /**
     * @api {get} /api/v1/logs/syslog Get all syslogs
     * @apiName Get_syslogs
     * @apiGroup Logs
     * @apiVersion  1.0.0
     *
     * @apiParam {String} [to] Optional The data and time to search to.
     * @apiParam {String} [from] Optional The date and time to search from.
     * @apiParam {Number} [per_page=50] Optional How many items to retrieve
     * @apiParam {Number} [current_page=1] Optional Active page of items
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
     *        ]
     *     }
     *
     */
    public function syslog(Request $request)
    {

        $to = $request->get('to', null);
        $from = $request->get('from', null);

        $query = new \App\Models\Log\Syslog;

        if ($to) {
            $query = $query->where('timestamp', '<', $to);
        }
        if ($from) {
            $query = $query->where('timestamp', '>=', $from);
        }

        return $this->paginateResponse($query);
    }

    /**
     * @api {get} /api/v1/logs/authlog Get all authlogs
     * @apiName Get_authlogs
     * @apiGroup Logs
     * @apiVersion  1.0.0
     *
     * @apiParam {String} [to] Optional The data and time to search to.
     * @apiParam {String} [from] Optional The date and time to search from.
     * @apiParam {Number} [per_page=50] Optional How many items to retrieve
     * @apiParam {Number} [current_page=1] Optional Active page of items
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *              {
     *                  "id": "2",
     *                  "datetime": "2018-04-25 00:16:02",
     *                  "user": "admin",
     *                  "address": "127.0.0.1",
     *                  "result": "Logged In",
     *              },
     *          ],
     *          "current_page": 1,
     *          "from": 1,
     *          "last_page": 1789,
     *          "next_page_url": "http://example.org/api/v1/logs/eventlog?page=2",
     *          "path": "http://example.org/api/v1/logs/eventlog",
     *          "per_page": 50,
     *          "prev_page_url": null,
     *          "to": 50,
     *          "total": 89439
     *     }
     *
     */
    
    public function authlog(Request $request)
    {
        $to = $request->get('to', null);
        $from = $request->get('from', null);

        $query = new \App\Models\Log\Authlog;

        if ($to) {
            $query = $query->where('timestamp', '<', $to);
        }
        if ($from) {
            $query = $query->where('timestamp', '>=', $from);
        }

        return $this->paginateResponse($query);
    }

    /**
     * @api {get} /api/v1/logs/eventlog Get all eventlogs
     * @apiName Get_eventlogs
     * @apiGroup Logs
     * @apiVersion  1.0.0
     *
     * @apiParam {String} [to] Optional The data and time to search to.
     * @apiParam {String} [from] Optional The date and time to search from.
     * @apiParam {Number} [per_page=50] Optional How many items to retrieve
     * @apiParam {Number} [current_page=1] Optional Active page of items
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
     *          "next_page_url": "http://example.org/api/v1/logs/eventlog?page=2",
     *          "path": "http://example.org/api/v1/logs/eventlog",
     *          "per_page": 50,
     *          "prev_page_url": null,
     *          "to": 50,
     *          "total": 89439
     *     }
     *
     */
    public function eventlog(Request $request)
    {

        $to = $request->get('to', null);
        $from = $request->get('from', null);

        $query = new \App\Models\Log\Eventlog;

        if ($to) {
            $query = $query->where('timestamp', '<', $to);
        }
        if ($from) {
            $query = $query->where('timestamp', '>=', $from);
        }

        return $this->paginateResponse($query);
    }

    /**
     * @api {get} /api/v1/logs/syslog Get all alertlogs
     * @apiName Get_alertlogs
     * @apiGroup Logs
     * @apiVersion  1.0.0
     *
     * @apiParam {String} [to] Optional The data and time to search to.
     * @apiParam {String} [from] Optional The date and time to search from.
     * @apiParam {Number} [per_page=50] Optional How many items to retrieve
     * @apiParam {Number} [current_page=1] Optional Active page of items
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
     *          "next_page_url": "http://example.org/api/v1/logs/alertlog?page=2",
     *          "path": "http://example.org/api/v1/logs/alertlog",
     *          "per_page": 50,
     *          "prev_page_url": null,
     *          "to": 50,
     *          "total": 89439
     *     }
     *
     */
    public function alertlog(Request $request)
    {

        $to = $request->get('to', null);
        $from = $request->get('from', null);

        $query = new \App\Models\Log\Alertlog;

        if ($to) {
            $query = $query->where('timestamp', '<', $to);
        }
        if ($from) {
            $query = $query->where('timestamp', '>=', $from);
        }

        return $this->paginateResponse($query);
    }
}
