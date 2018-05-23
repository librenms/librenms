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
     *              },
     *              {
     *                  "sensor_class": "frequency",
     *              },
     *              {
     *                  "sensor_class": "ssr",
     *              }
     *          ]
     *     }
     *
     */
    public function index(Device $device)
    {
        return $this->objectResponse($device->wirelessSensors()->select('sensor_class')->distinct()->get());
    }

    /**
     * @api {get} /devices/:id/wireless/:sensor_class Get all wireless sensors for a Wireless Class
     * @apiName Get_All_Wireless_Sensors_For_Class
     * @apiGroup Device Get all sensors for a wireless sensor
     *
     * @apiParam {Number} id Id of the Device
     * @apiParam {String} sensor_class The class name of the sensor (gathered from the Get_Device_Wireless method)
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          [
     *              {
     *                  "sensor_id": "9",
     *                  "sensor_deleted": "0",
     *                  "sensor_class": "clients",
     *                  "device_id": "13",
     *                  "sensor_index": "0",
     *                  "sensor_type": "pmp",
     *                  "sensor_descr": "Client Count",
     *                  "sensor_divisor": "1",
     *                  "sensor_multiplier": "1",
     *                  "sensor_aggregator": "sum",
     *                  "sensor_current": "25",
     *                  "sensor_prev": "25",
     *                  "sensor_limit": null,
     *                  "sensor_limit_warn": null,
     *                  "sensor_limit_low": null,
     *                  "sensor_limit_low_warn": null,
     *                  "sensor_alert": "1",
     *                  "sensor_custom": "No",
     *                  "entPhysicalIndex": null,
     *                  "entPhysicalIndex_measured": null,
     *                  "lastupdate": "2018-05-23 09:45:47",
     *                  "sensor_oids": "[\".1.3.6.1.4.1.161.19.3.1.7.1.0\"]",
     *                  "access_point_id": null
     *              }
     *          ]
     *     }
     *
     */
    public function show(Device $device, $class)
    {
        return $this->objectResponse($device->wirelessSensors()->where('sensor_class', $class)->get());
    }
}
