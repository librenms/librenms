<?php

namespace App\Http\Controllers\Api\Device;

use App\Models\Device;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class DeviceHealthController extends ApiController
{
    /**
     * @api {get} /api/v1/devices/:id/health Get Health Sensors
     * @apiName Get_Device_Health
     * @apiGroup Device Health
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id Id of the Device
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          [
     *              {
     *                  "sensor_class": "state",
     *              },
     *          ]
     *     }
     *
     */
    public function index(Device $device)
    {
        return $this->objectResponse($device->sensors()->deviceSensors());
    }

    /**
     * @api {get} /api/v1/devices/:id/health/:class Get all sensors for a Health Class
     * @apiName Get_All_Sensors_For_Class
     * @apiGroup Device Health
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id ID or Hostname of the Device
     * @apiParam {String} class The class name of the sensor (gathered from the Get_Device_Health method)
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          [
     *              {
     *                  "sensor_id": "1698",
     *                  "sensor_deleted": "0",
     *                  "sensor_class": "state",
     *                  "device_id": "251",
     *                  "poller_type": "snmp",
     *                  "sensor_oid": ".1.3.6.1.4.1.161.19.3.4.4.1.1.5.1",
     *                  "sensor_index": "powerStatus.1",
     *                  "sensor_type": "CMM3-MIB::powerStatus",
     *                  "sensor_descr": "Power Status 1",
     *                  "sensor_divisor": "1",
     *                  "sensor_multiplier": "1",
     *                  "sensor_current": "1",
     *                  "sensor_limit": null,
     *                  "sensor_limit_warn": null,
     *                  "sensor_limit_low": null,
     *                  "sensor_limit_low_warn": null,
     *                  "sensor_alert": "1",
     *                  "sensor_custom": "No",
     *                  "entPhysicalIndex": null,
     *                  "entPhysicalIndex_measured": null,
     *                  "lastupdate": "2018-05-17 15:40:24",
     *                  "sensor_prev": null,
     *                  "user_func": null
     *              },
     *          ]
     *     }
     *
     * @apiErrorExample {json} Error-Response:
     *      HTTP/1.1 404 Not-Found
     *      {
     *          "status": "Item not Found"
     *      }
     */
    public function show(Device $device, $class)
    {
        return $this->objectResponse($device->sensors()->where('sensor_class', $class)->get());
    }
}
