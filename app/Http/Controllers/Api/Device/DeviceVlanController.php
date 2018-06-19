<?php

namespace App\Http\Controllers\Api\Device;

use App\Models\Device;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class DeviceVlanController extends ApiController
{
    /**
     * @api {get} /api/v1/devices/:id/vlans Get device vlans
     * @apiName Get_Device_vlans
     * @apiGroup Device Vlans
     * @apiVersion  1.0.0
     *
     * @apiUse DeviceParam
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *              {
     *                  "vlan_id": "31",
     *                  "device_id": "10",
     *                  "vlan_vlan": "1",
     *                  "vlan_domain": "1",
     *                  "vlan_name": "default",
     *                  "vlan_type": "ethernet",
     *                  "vlan_mtu": null
     *              },
     *          ]
     *     }
     *
     */
    public function index(Device $device)
    {
        return $this->objectResponse($device->vlans()->get());
    }
}
