<?php

namespace App\Http\Controllers\Api\Device;

use App\Models\Device;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class DeviceWirelessController extends ApiController
{
    /**
     * @api {get} /devices/:id/wireless Get Wireless Sensors
     * @apiName Get_Device_Wireless
     * @apiGroup Device Get all wirelesss sensors for a device
     *
     * @apiParam {Number} id Id of the Device
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          [
     *              {
     *                  "sensor_class": "clients",
     *                  "sensor_descr": "Client Count"
     *              },
     *              {
     *                  "sensor_class": "frequency",
     *                  "sensor_descr": "Frequency"
     *              },
     *              {
     *                  "sensor_class": "ssr",
     *                  "sensor_descr": "Cambium Signal Strength Ratio"
     *              }
     *          ]
     *     }
     *
     */
    public function index(Device $device)
    {
        return $this->objectResponse($device->wirelessSensors()->select('sensor_class','sensor_descr')->get());
    }

    /**
     * @api {get} /devices/:id/wireless Get Wireless Sensors
     * @apiName Get_Device_Wireless
     * @apiGroup Device Get all wirelesss sensors for a device
     *
     * @apiParam {Number} id Id of the Device
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          [
     *              {
     *                  "sensor_class": "clients",
     *                  "sensor_descr": "Client Count"
     *              },
     *              {
     *                  "sensor_class": "frequency",
     *                  "sensor_descr": "Frequency"
     *              },
     *              {
     *                  "sensor_class": "ssr",
     *                  "sensor_descr": "Cambium Signal Strength Ratio"
     *              }
     *          ]
     *     }
     *
     */
    public function show(Device $device, $type)
    {
        return $this->objectResponse($device->wirelessSensors()->where('sensor_class', $type)->get());
    }
}
