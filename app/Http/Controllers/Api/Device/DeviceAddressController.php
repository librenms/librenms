<?php

namespace App\Http\Controllers\Api\Device;

use App\Models\Device;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class DeviceAddressController extends ApiController
{
    /**
     * @api {get} /api/v1/devices/:id/addresses Get all addresses for a device
     * @apiName Get_Device_Addresses
     * @apiGroup Devices
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id Id of the Device
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          {
     *              "ipv4": [
     *                  {
     *                      "ipv4_address_id": 25,
     *                      "ipv4_address": "127.0.0.1",
     *                      "ipv4_prefixlen": "8",
     *                      "ipv4_network_id": "6",
     *                      "port_id": "51",
     *                      "context_name": "",
     *                      "device_id": "22"
     *                  },
     *              ],
     *              "ipv6": [
     *                  {
     *                      "ipv6_address_id": 1,
     *                      "ipv6_address": "0000:0000:0000:0000:0250:56ff:feb7:d88b",
     *                      "ipv6_compressed": "::250:56ff:feb7:d88b",
     *                      "ipv6_prefixlen": "64",
     *                      "ipv6_origin": "linklayer",
     *                      "ipv6_network_id": "1",
     *                      "port_id": "52",
     *                      "context_name": "",
     *                      "device_id": "22"
     *                  },
     *              ]
     *          }
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
        return $this->objectResponse(['ipv4' => $device->ipv4()->get()->toArray(), 'ipv6' => $device->ipv6()->get()->toArray()]);
    }
}
