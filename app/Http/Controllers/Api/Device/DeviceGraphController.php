<?php

namespace App\Http\Controllers\Api\Device;

use App\Models\Device;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class DeviceGraphController extends ApiController
{
    /**
     * @api {get} /api/v1/devices/:id/graphs Get Generic graphs
     * @apiName Get_Device_Graphs
     * @apiDescription Get a list of available graphs for a device, this does not include ports.
     * @apiGroup Devices
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id ID or Hostname of the Device
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          [
     *              {
     *                  "graph": "uptime",
     *                  "descr": "System Uptime"
     *              },
     *              {
     *                  "graph": "netstat_snmp",
     *                  "descr": "SNMP Statistics"
     *              },
     *              {
     *                  "graph": "netstat_snmp_pkt",
     *                  "descr": "SNMP Packet Type Statistics"
     *              },
     *              {
     *                  "graph": "netstat_ip",
     *                  "descr": "IP Statistics"
     *              },
     *              {
     *                  "graph": "netstat_ip_frag",
     *                  "descr": "IP Fragmentation Statistics"
     *              },
     *              {
     *                  "graph": "netstat_tcp",
     *                  "descr": "TCP Statistics"
     *              },
     *              {
     *                  "graph": "netstat_udp",
     *                  "descr": "UDP Statistics"
     *              },
     *              {
     *                  "graph": "netstat_icmp",
     *                  "descr": "ICMP Statistics"
     *              },
     *              {
     *                  "graph": "netstat_icmp_info",
     *                  "descr": "ICMP Informational Statistics"
     *              }
     *          ]
     *     }
     *
     * @apiErrorExample {json} Error-Response:
     *      HTTP/1.1 404 Not-Found
     *      {
     *          "status": "Item not Found"
     *      }
     */
    public function index(Device $device)
    {
        return $this->objectResponse($device->deviceGraphs()->select('graph')->distinct()->get());
    }
}
