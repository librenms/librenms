<?php

namespace App\Http\Controllers\Api\Device;

use App\Models\Device;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class DeviceWirelessController extends ApiController
{
    /**
     * @api {get} /api/v1/devices/:id/wireless Get Wireless Sensors
     * @apiName Get_Device_Wireless
     * @apiGroup Device Wireless
     * @apiVersion  1.0.0
     *
     * @apiUse DeviceParam
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
     * @api {get} /api/v1/devices/:id/wireless/:class Get all wireless sensors for a Wireless Class
     * @apiName Get_All_Wireless_Sensors_For_Class
     * @apiGroup Device Wireless
     * @apiVersion  1.0.0
     *
     * @apiUse DeviceParam
     * @apiParam {String} class The class name of the sensor (gathered from the Get_Device_Wireless method)
     *
     * @apiExample {curl} Example usage:
     *     curl -H 'X-Auth-Token: YOURAPITOKENHERE' -H 'Content-Type: application/json' -i http://example.org/api/v1/devices/1/wireless/clients
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
     * @apiUse NotFoundError
     */
    public function show(Device $device, $class)
    {
        return $this->objectResponse($device->wirelessSensors()->where('sensor_class', $class)->get());
    }
}
