<?php

namespace App\Http\Controllers\Api\Device;

use Librenms\Config;
use App\Models\Device;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class DeviceHealthController extends ApiController
{
    /**
     * @api {get} /devices/:id/health Get Health Sensors
     * @apiName Get_Device_Health
     * @apiGroup Device Get all health sensors for a device
     * 
     * @apiParam {Number} id Id of the Device
     * 
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": 
     *          [
     *              {
     *                  "name": "state",
     *                  "descr": "GPS Status"
     *              },
     *              {
     *                  "name": "temperature",
     *                  "descr": "Cambium Temperature"
     *              }
     *          ]
     *     }
     * 
     */
    public function show($id)
    {
        // TODO: Make model method that gathers processors, mem, storage, and states in a single method
        $device = Device::find($id);
        return $this->object_response($device->sensors()->select('sensor_class','sensor_descr')->get());
    }

}
