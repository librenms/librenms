<?php

namespace App\Http\Controllers\Api;

use App\Models\Port;
use App\Http\Controllers\Api\ApiController;

class PortController extends ApiController
{
    /**
     * @api {get} /ports Get All Ports
     * @apiName Get_Ports
     * @apiGroup Port
     * @apiVersion  1.0.0
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
     *          "current_page": 1,
     *          "from": 1,
     *          "last_page": 1,
     *          "next_page_url": null,
     *          "path": "http://example.org/api/v1/ports",
     *          "per_page": 15,
     *          "prev_page_url": null,
     *          "to": 10,
     *          "total": 10
     *     }
     *
     */
    public function index()
    {
        return $this->paginateResponse(new Port);
    }

    /**
     * @api {get} /devices/:id Get individual Port
     * @apiName Get_Port
     * @apiGroup Port
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id Id of the Port
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
     */

    public function show(Port $port)
    {
        // TODO: Eager Load address information
        // $port->load('ipv4');
        return $this->objectResponse($port);
    }
}
