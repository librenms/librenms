<?php

namespace App\Http\Controllers\Api\Device;

use App\Models\Device;
use App\Models\Port;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class DevicePortAddressController extends ApiController
{
    /**
     * @api {get} /devices/:id/ports/:port_id/addresses Get device port addresses
     * @apiName Get_Device_Port_Addresses
     * @apiGroup Device Ports
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id ID or Hostname of the Device
     * @apiParam {Number} port_id ID of the port
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": 
     *          {
     *              "ipv4": [
     *                  {
     *                         "ipv4_address_id": 28,
     *                         "ipv4_address": "10.51.100.52",
     *                         "ipv4_prefixlen": "24",
     *                         "ipv4_network_id": "10",
     *                         "port_id": "23",
     *                         "context_name": ""
     *                  }
     *              ],
     *              "ipv6": []
     *          }
     *     }
     *
     * @apiErrorExample {json} Error-Response:
     *      HTTP/1.1 404 Not-Found
     *      {
     *          "status": "Item not Found"
     *      }
     */
    public function index(Device $device, Port $port)
    {
        return $this->objectResponse(['ipv4' => $port->ipv4()->get(), 'ipv6' => $port->ipv6()->get()]);
    }
}
