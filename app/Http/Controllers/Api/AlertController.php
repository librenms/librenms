<?php

namespace App\Http\Controllers\Api;

use App\Models\Alert;
use App\Http\Controllers\Api\ApiController;

class AlertController extends ApiController
{
    /**
     *
     * @api {get} /api/v1/alerts Get alerts
     * @apiName Get_alerts
     * @apiGroup Alerts
     * @apiVersion  1.0.0
     *
     * @apiUse Pagination
     *
     * @apiSuccessExample {type} Success-Response:
     *      HTTP/1.1 200 OK
     *      {
     *          "data": [
     *              {
     *                  "id": 13,
     *                  "device_id": "1",
     *                  "rule_id": "1",
     *                  "state": "0",
     *                  "alerted": "0",
     *                  "open": "1",
     *                  "note": "",
     *                  "timestamp": "2018-05-29 10:25:35"
     *              },
     *          ],
     *          "current_page": 1,
     *          "from": 1,
     *          "last_page": 4,
     *          "next_page_url": "http://example.org/api/v1/alerts?page=2",
     *          "path": "http://example.org/api/v1/alerts",
     *          "per_page": 50,
     *          "prev_page_url": null,
     *          "to": 50,
     *          "total": 175
     *      }
     *
     *
     */
    public function index()
    {
        return $this->paginateResponse(new Alert);
    }

    /**
     *
     * @api {get} /api/v1/alerts/:id Get individual alert
     * @apiName Get_alert
     * @apiDescription Get details of an alert
     * @apiGroup Alerts
     * @apiVersion  1.0.0
     *
     *
     * @apiParam {Number} id The id of the alert
     *
     * @apiSuccessExample {type} Success-Response:
     *      HTTP/1.1 200 OK
     *      {
     *          "data": {
     *              "id": 13,
     *              "device_id": "1",
     *              "rule_id": "1",
     *              "state": "0",
     *              "alerted": "0",
     *              "open": "0",
     *              "note": "",
     *              "timestamp": "2018-05-29 10:31:02",
     *              "device": {
     *                  "device_id": 1,
     *                  "hostname": "localhost",
     *                  "sysName": "hostname.example.org",
     *                  "ip": null,
     *                  "community": "public",
     *                  "authlevel": "noAuthNoPriv",
     *                  "authname": "",
     *                  "authpass": "",
     *                  "authalgo": "MD5",
     *                  "cryptopass": "",
     *                  "cryptoalgo": "AES",
     *                  "snmpver": "v2c",
     *                  "port": "161",
     *                  "transport": "udp",
     *                  "timeout": null,
     *                  "retries": null,
     *                  "snmp_disable": "0",
     *                  "bgpLocalAs": null,
     *                  "sysObjectID": ".1.3.6.1.4.1.8072.3.2.10",
     *                  "sysDescr": "Linux redirect.wavedirect.net 3.10.0-693.17.1.el7.x86_64 #1 SMP Thu Jan 25 20:13:58 UTC 2018 x86_64",
     *                  "sysContact": "Root <root@localhost> (configure /etc/snmp/snmp.local.conf)",
     *                  "version": "3.10.0-693.17.1.el7.x86_64",
     *                  "hardware": "Generic x86 64-bit",
     *                  "features": null,
     *                  "location": "Unknown (edit /etc/snmp/snmpd.conf)",
     *                  "os": "linux",
     *                  "status": "1",
     *                  "status_reason": "",
     *                  "ignore": "0",
     *                  "disabled": "0",
     *                  "uptime": "4760185",
     *                  "agent_uptime": "0",
     *                  "last_polled": "2018-05-29 10:30:36",
     *                  "last_poll_attempted": null,
     *                  "last_polled_timetaken": "27.87",
     *                  "last_discovered_timetaken": "34.86",
     *                  "last_discovered": "2018-05-29 06:35:26",
     *                  "last_ping": "2018-05-29 10:30:36",
     *                  "last_ping_timetaken": "0.46",
     *                  "purpose": null,
     *                  "type": "server",
     *                  "serial": null,
     *                  "icon": "http://example.org/images/os/linux.svg",
     *                  "poller_group": "0",
     *                  "override_sysLocation": "0",
     *                  "notes": null,
     *                  "port_association_mode": "1"
     *              }
     *          }
     *      }
     */
    public function show(Alert $alert)
    {
        return $this->objectResponse($alert->load('device'));
    }

    /**
     *
     * @api {put} /api/v1/alerts/:id Acknowledge an alert
     * @apiName Acknowledge_alert
     * @apiDescription Acknowledge an alert by ID
     * @apiGroup Alerts
     * @apiVersion  1.0.0
     *
     *
     * @apiParam  {Number} id The id of the alert.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          {
     *              "message": "Alert #234 has been acknowledged"
     *          }
     *     }
     *
     * @apiUse NotFoundError
     *
     */
    public function update(Alert $alert)
    {
        $alert->acknowledge();
        return $this->messageResponse("Alert #$alert->id has been acknowledged");
    }

    /**
     *
     * @api {delete} /api/v1/alerts/:id Unmute an alert
     * @apiName Unute_alert
     * @apiDescription Unmute an alert by ID
     * @apiGroup Alerts
     * @apiVersion  1.0.0
     *
     *
     * @apiParam  {Number} id The id of the alert.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          {
     *              "message": "Alert #234 has been unmuted"
     *          }
     *     }
     *
     * @apiUse NotFoundError
     *
     */
    public function destroy(Alert $alert)
    {
        $alert->unmute();
        return $this->messageResponse("Alert #$alert->id has been unmuted");
    }
}
