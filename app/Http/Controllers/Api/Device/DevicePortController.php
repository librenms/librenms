<?php

namespace App\Http\Controllers\Api\Device;

use App\Models\Device;
use App\Models\Port;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class DevicePortController extends ApiController
{
    /**
     * @api {get} /api/v1/devices/:id/ports Get device ports
     * @apiName Get_Device_Ports
     * @apiGroup Device Ports
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id ID or Hostname of the Device
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *              {
     *                  "port_id": 3,
     *                  "device_id": "3",
     *                  "port_descr_type": null,
     *                  "port_descr_descr": null,
     *                  "port_descr_circuit": null,
     *                  "port_descr_speed": null,
     *                  "port_descr_notes": null,
     *                  "ifDescr": "...",
     *                  "ifName": "...",
     *                  "portName": null,
     *                  "ifIndex": "1",
     *                  "ifSpeed": null,
     *                  "ifConnectorPresent": "0",
     *                  "ifPromiscuousMode": "0",
     *                  "ifHighSpeed": "0",
     *                  "ifOperStatus": "up",
     *                  "ifOperStatus_prev": "up",
     *                  "ifAdminStatus": "up",
     *                  "ifAdminStatus_prev": "up",
     *                  "ifDuplex": null,
     *                  "ifMtu": "0",
     *                  "ifType": "ethernetCsmacd",
     *                  "ifAlias": "None",
     *                  "ifPhysAddress": "0a003ea0acdf",
     *                  "ifHardType": null,
     *                  "ifLastChange": "20436085",
     *                  "ifVlan": "",
     *                  "ifTrunk": null,
     *                  "ifVrf": "0",
     *                  "counter_in": null,
     *                  "counter_out": null,
     *                  "ignore": "0",
     *                  "disabled": "0",
     *                  "detailed": "0",
     *                  "deleted": "0",
     *                  "pagpOperationMode": null,
     *                  "pagpPortState": null,
     *                  "pagpPartnerDeviceId": null,
     *                  "pagpPartnerLearnMethod": null,
     *                  "pagpPartnerIfIndex": null,
     *                  "pagpPartnerGroupIfIndex": null,
     *                  "pagpPartnerDeviceName": null,
     *                  "pagpEthcOperationMode": null,
     *                  "pagpDeviceId": null,
     *                  "pagpGroupIfIndex": null,
     *                  "ifInUcastPkts": "809856394",
     *                  "ifInUcastPkts_prev": "809633119",
     *                  "ifInUcastPkts_delta": "223275",
     *                  "ifInUcastPkts_rate": "747",
     *                  "ifOutUcastPkts": "2927841490",
     *                  "ifOutUcastPkts_prev": "2927698689",
     *                  "ifOutUcastPkts_delta": "142801",
     *                  "ifOutUcastPkts_rate": "478",
     *                  "ifInErrors": "13",
     *                  "ifInErrors_prev": "13",
     *                  "ifInErrors_delta": "0",
     *                  "ifInErrors_rate": "0",
     *                  "ifOutErrors": "1",
     *                  "ifOutErrors_prev": "1",
     *                  "ifOutErrors_delta": "0",
     *                  "ifOutErrors_rate": "0",
     *                  "ifInOctets": "952753304",
     *                  "ifInOctets_prev": "694272411",
     *                  "ifInOctets_delta": "258480893",
     *                  "ifInOctets_rate": "864485",
     *                  "ifOutOctets": "2417361137",
     *                  "ifOutOctets_prev": "2369159572",
     *                  "ifOutOctets_delta": "48201565",
     *                  "ifOutOctets_rate": "161209",
     *                  "poll_time": "1526670934",
     *                  "poll_prev": "1526670635",
     *                  "poll_period": "299"
     *              }
     *          ],
     *     }
     *
     */
    public function index(Device $device)
    {
        return $this->objectResponse($device->ports()->get());
    }

    /**
     * @api {get} /api/v1/devices/:id/ports/:port_id Get individual Port for a device
     * @apiName Get_Port
     * @apiGroup Device Ports
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id ID or Hostname of the device
     * @apiParam {Number} port_id ID of the Port
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          {
     *              "port_id": 3,
     *              "device_id": "3",
     *              "port_descr_type": null,
     *              "port_descr_descr": null,
     *              "port_descr_circuit": null,
     *              "port_descr_speed": null,
     *              "port_descr_notes": null,
     *              "ifDescr": "...",
     *              "ifName": "...",
     *              "portName": null,
     *              "ifIndex": "1",
     *              "ifSpeed": null,
     *              "ifConnectorPresent": "0",
     *              "ifPromiscuousMode": "0",
     *              "ifHighSpeed": "0",
     *              "ifOperStatus": "up",
     *              "ifOperStatus_prev": "up",
     *              "ifAdminStatus": "up",
     *              "ifAdminStatus_prev": "up",
     *              "ifDuplex": null,
     *              "ifMtu": "0",
     *              "ifType": "ethernetCsmacd",
     *              "ifAlias": "None",
     *              "ifPhysAddress": "0a003ea0acdf",
     *              "ifHardType": null,
     *              "ifLastChange": "20436085",
     *              "ifVlan": "",
     *              "ifTrunk": null,
     *              "ifVrf": "0",
     *              "counter_in": null,
     *              "counter_out": null,
     *              "ignore": "0",
     *              "disabled": "0",
     *              "detailed": "0",
     *              "deleted": "0",
     *              "pagpOperationMode": null,
     *              "pagpPortState": null,
     *              "pagpPartnerDeviceId": null,
     *              "pagpPartnerLearnMethod": null,
     *              "pagpPartnerIfIndex": null,
     *              "pagpPartnerGroupIfIndex": null,
     *              "pagpPartnerDeviceName": null,
     *              "pagpEthcOperationMode": null,
     *              "pagpDeviceId": null,
     *              "pagpGroupIfIndex": null,
     *              "ifInUcastPkts": "809856394",
     *              "ifInUcastPkts_prev": "809633119",
     *              "ifInUcastPkts_delta": "223275",
     *              "ifInUcastPkts_rate": "747",
     *              "ifOutUcastPkts": "2927841490",
     *              "ifOutUcastPkts_prev": "2927698689",
     *              "ifOutUcastPkts_delta": "142801",
     *              "ifOutUcastPkts_rate": "478",
     *              "ifInErrors": "13",
     *              "ifInErrors_prev": "13",
     *              "ifInErrors_delta": "0",
     *              "ifInErrors_rate": "0",
     *              "ifOutErrors": "1",
     *              "ifOutErrors_prev": "1",
     *              "ifOutErrors_delta": "0",
     *              "ifOutErrors_rate": "0",
     *              "ifInOctets": "952753304",
     *              "ifInOctets_prev": "694272411",
     *              "ifInOctets_delta": "258480893",
     *              "ifInOctets_rate": "864485",
     *              "ifOutOctets": "2417361137",
     *              "ifOutOctets_prev": "2369159572",
     *              "ifOutOctets_delta": "48201565",
     *              "ifOutOctets_rate": "161209",
     *              "poll_time": "1526670934",
     *              "poll_prev": "1526670635",
     *              "poll_period": "299"
     *          }
     *     }
     *
     * @apiErrorExample {json} Error-Response:
     *      HTTP/1.1 404 Not-Found
     *      {
     *          "status": "Item not Found"
     *      }
     */
    public function show(Device $device, Port $port)
    {
        return $this->objectResponse($port);
    }

    /**
     * @api {get} /api/v1/devices/:id/ports/stack Get ports stack for Device
     * @apiName Get_Ports_Stack
     * @apiDescription Get a list of port mappings for a device. This is useful for showing physical ports that are in a virtual port-channel.
     * @apiGroup Device Ports
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id ID or Hostname of the device
     * @apiParam {Boolean} [valid_mappings=true] Optional Filter the result by only showing valid mappings ("0" values not shown).
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          [
     *              {
     *                "device_id": "3742",
     *                "port_id_high": "1001000",
     *                "port_id_low": "51001",
     *                "ifStackStatus": "active"
     *              },
     *              {
     *                "device_id": "3742",
     *                "port_id_high": "1001000",
     *                "port_id_low": "52001",
     *                "ifStackStatus": "active"
     *              }
     *          ]
     *      }
     *
     * @apiErrorExample {json} Error-Response:
     *      HTTP/1.1 404 Not-Found
     *      {
     *          "status": "Item not Found"
     *      }
     */
    public function stack(Device $device)
    {
        $input = $this->booleanInput('valid_mappings');
        if ($input) {
            return $this->objectResponse($device->portStack()->isActive()->validMappings()->orderBy('port_id_high', 'asc')->get());
        }
        return $this->objectResponse($device->portStack()->isActive()->orderBy('port_id_high', 'asc')->get());
    }
}
