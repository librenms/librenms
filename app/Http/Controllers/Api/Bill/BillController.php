<?php

namespace App\Http\Controllers\Api\Bill;

use App\Models\Bill\Bill;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

// TODO: Implement History Ref and customer input overrides

class BillController extends ApiController
{
    /**
     *
     * @api {get} /api/v1/bills Get Bills
     * @apiName Get_all_bills
     * @apiDescription Retrieve the list of bills currently in the system.
     * @apiGroup Bills
     * @apiVersion  1.0.0
     *
     * @apiParam {Boolean} [previous=false] Optional Indicates you would like the data for the last complete period rather than the current period
     * @apiParam {String} [ref] Optional The billing reference
     * @apiParam {String} [custid] Optional The customer reference
     * @apiUse Pagination
     *
     * @apiExample {curl} Example usage wihout parameters:
     *     curl -H 'X-Auth-Token: YOURAPITOKENHERE' -H 'Content-Type: application/json' -i http://example.org/api/v1/bill
     *
     * @apiExample {curl} Example usage with parameters:
     *     curl -H 'X-Auth-Token: YOURAPITOKENHERE' -H 'Content-Type: application/json' -i http://example.org/api/v1/bill?customer=12&pervious=true
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *              {
     *                  "bill_id": 1,
     *                  "bill_name": "test",
     *                  "bill_type": "cdr",
     *                  "bill_cdr": "0",
     *                  "bill_day": "1",
     *                  "bill_quota": "0",
     *                  "rate_95th_in": "0",
     *                  "rate_95th_out": "0",
     *                  "rate_95th": "0",
     *                  "dir_95th": "in",
     *                  "total_data": "0",
     *                  "total_data_in": "0",
     *                  "total_data_out": "0",
     *                  "rate_average_in": "0",
     *                  "rate_average_out": "0",
     *                  "rate_average": "0",
     *                  "bill_last_calc": "2018-05-24 08:30:05",
     *                  "bill_custid": "",
     *                  "bill_ref": "",
     *                  "bill_notes": "",
     *                  "bill_autoadded": "0",
     *                  "used": "274.800kbps",
     *                  "overuse": "174.800k",
     *                  "percent": 549.59000000000003,
     *                  "allowed": "100.000kbps",
     *                  "ports": [
     *                      {
     *                          "port_id": 27,
     *                          "device_id": "14",
     *                          "port_descr_type": null,
     *                          "port_descr_descr": null,
     *                          "port_descr_circuit": null,
     *                          "port_descr_speed": null,
     *                          "port_descr_notes": null,
     *                          "ifDescr": "Motorola 10/100 FEC",
     *                          "ifName": "Motorola 10/100 FEC",
     *                          "portName": null,
     *                          "ifIndex": "1",
     *                          "ifSpeed": null,
     *                          "ifConnectorPresent": "0",
     *                          "ifPromiscuousMode": "0",
     *                          "ifHighSpeed": "0",
     *                          "ifOperStatus": "up",
     *                          "ifOperStatus_prev": "up",
     *                          "ifAdminStatus": "up",
     *                          "ifAdminStatus_prev": "up",
     *                          "ifDuplex": null,
     *                          "ifMtu": "0",
     *                          "ifType": "ethernetCsmacd",
     *                          "ifAlias": "None",
     *                          "ifPhysAddress": "0a003e20d1f0",
     *                          "ifHardType": null,
     *                          "ifLastChange": "16260149",
     *                          "ifVlan": "",
     *                          "ifTrunk": null,
     *                          "ifVrf": "0",
     *                          "counter_in": null,
     *                          "counter_out": null,
     *                          "ignore": "0",
     *                          "disabled": "0",
     *                          "detailed": "0",
     *                          "deleted": "0",
     *                          "pagpOperationMode": null,
     *                          "pagpPortState": null,
     *                          "pagpPartnerDeviceId": null,
     *                          "pagpPartnerLearnMethod": null,
     *                          "pagpPartnerIfIndex": null,
     *                          "pagpPartnerGroupIfIndex": null,
     *                          "pagpPartnerDeviceName": null,
     *                          "pagpEthcOperationMode": null,
     *                          "pagpDeviceId": null,
     *                          "pagpGroupIfIndex": null,
     *                          "ifInUcastPkts": "865421060",
     *                          "ifInUcastPkts_prev": "865416958",
     *                          "ifInUcastPkts_delta": "4102",
     *                          "ifInUcastPkts_rate": "14",
     *                          "ifOutUcastPkts": "383234617",
     *                          "ifOutUcastPkts_prev": "383231886",
     *                          "ifOutUcastPkts_delta": "2731",
     *                          "ifOutUcastPkts_rate": "9",
     *                          "ifInErrors": "49646",
     *                          "ifInErrors_prev": "49646",
     *                          "ifInErrors_delta": "0",
     *                          "ifInErrors_rate": "0",
     *                          "ifOutErrors": "3",
     *                          "ifOutErrors_prev": "3",
     *                          "ifOutErrors_delta": "0",
     *                          "ifOutErrors_rate": "0",
     *                          "ifInOctets": "3379112468",
     *                          "ifInOctets_prev": "3375073461",
     *                          "ifInOctets_delta": "4039007",
     *                          "ifInOctets_rate": "13419",
     *                          "ifOutOctets": "4209101840",
     *                          "ifOutOctets_prev": "4208786404",
     *                          "ifOutOctets_delta": "315436",
     *                          "ifOutOctets_rate": "1048",
     *                          "poll_time": "1527165043",
     *                          "poll_prev": "1527164742",
     *                          "poll_period": "301",
     *                      },
     *                  ]
     *              }
     *          ],
     *          "current_page": 1,
     *          "from": 1,
     *          "last_page": 1,
     *          "next_page_url": null,
     *          "path": "http://example.org/api/v1/bills",
     *          "per_page": 50,
     *          "prev_page_url": null,
     *          "to": 1,
     *          "total": 1
     *      }
     */
    public function index()
    {
        $input      = $this->booleanInput('previous');
        $ref        = request()->input('ref');
        $customer   = request()->input('custid');
        return $this->paginateResponse(Bill::with('ports'));
    }

    /**
     *
     * @api {get} /api/v1/bills/:id Get individual Bill
     * @apiName Get_gil
     * @apiDescription Retrieve the list of bills currently in the system.
     * @apiGroup Bills
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id The ID of the bill to retrieve
     * @apiParam {Boolean} [previous=false] Optional Indicates you would like the data for the last complete period rather than the current period
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          {
     *              "bill_id": 1,
     *              "bill_name": "test",
     *              "bill_type": "cdr",
     *              "bill_cdr": "0",
     *              "bill_day": "1",
     *              "bill_quota": "0",
     *              "rate_95th_in": "0",
     *              "rate_95th_out": "0",
     *              "rate_95th": "0",
     *              "dir_95th": "in",
     *              "total_data": "0",
     *              "total_data_in": "0",
     *              "total_data_out": "0",
     *              "rate_average_in": "0",
     *              "rate_average_out": "0",
     *              "rate_average": "0",
     *              "bill_last_calc": "2018-05-24 08:30:05",
     *              "bill_custid": "",
     *              "bill_ref": "",
     *              "bill_notes": "",
     *              "bill_autoadded": "0",
     *              "used": "274.800kbps",
     *              "overuse": "174.800k",
     *              "percent": 549.59000000000003,
     *              "allowed": "100.000kbps",
     *              "ports": [
     *                  {
     *                      "port_id": 27,
     *                      "device_id": "14",
     *                      "port_descr_type": null,
     *                      "port_descr_descr": null,
     *                      "port_descr_circuit": null,
     *                      "port_descr_speed": null,
     *                      "port_descr_notes": null,
     *                      "ifDescr": "Motorola 10/100 FEC",
     *                      "ifName": "Motorola 10/100 FEC",
     *                      "portName": null,
     *                      "ifIndex": "1",
     *                      "ifSpeed": null,
     *                      "ifConnectorPresent": "0",
     *                      "ifPromiscuousMode": "0",
     *                      "ifHighSpeed": "0",
     *                      "ifOperStatus": "up",
     *                      "ifOperStatus_prev": "up",
     *                      "ifAdminStatus": "up",
     *                      "ifAdminStatus_prev": "up",
     *                      "ifDuplex": null,
     *                      "ifMtu": "0",
     *                      "ifType": "ethernetCsmacd",
     *                      "ifAlias": "None",
     *                      "ifPhysAddress": "0a003e20d1f0",
     *                      "ifHardType": null,
     *                      "ifLastChange": "16260149",
     *                      "ifVlan": "",
     *                      "ifTrunk": null,
     *                      "ifVrf": "0",
     *                      "counter_in": null,
     *                      "counter_out": null,
     *                      "ignore": "0",
     *                      "disabled": "0",
     *                      "detailed": "0",
     *                      "deleted": "0",
     *                      "pagpOperationMode": null,
     *                      "pagpPortState": null,
     *                      "pagpPartnerDeviceId": null,
     *                      "pagpPartnerLearnMethod": null,
     *                      "pagpPartnerIfIndex": null,
     *                      "pagpPartnerGroupIfIndex": null,
     *                      "pagpPartnerDeviceName": null,
     *                      "pagpEthcOperationMode": null,
     *                      "pagpDeviceId": null,
     *                      "pagpGroupIfIndex": null,
     *                      "ifInUcastPkts": "865421060",
     *                      "ifInUcastPkts_prev": "865416958",
     *                      "ifInUcastPkts_delta": "4102",
     *                      "ifInUcastPkts_rate": "14",
     *                      "ifOutUcastPkts": "383234617",
     *                      "ifOutUcastPkts_prev": "383231886",
     *                      "ifOutUcastPkts_delta": "2731",
     *                      "ifOutUcastPkts_rate": "9",
     *                      "ifInErrors": "49646",
     *                      "ifInErrors_prev": "49646",
     *                      "ifInErrors_delta": "0",
     *                      "ifInErrors_rate": "0",
     *                      "ifOutErrors": "3",
     *                      "ifOutErrors_prev": "3",
     *                      "ifOutErrors_delta": "0",
     *                      "ifOutErrors_rate": "0",
     *                      "ifInOctets": "3379112468",
     *                      "ifInOctets_prev": "3375073461",
     *                      "ifInOctets_delta": "4039007",
     *                      "ifInOctets_rate": "13419",
     *                      "ifOutOctets": "4209101840",
     *                      "ifOutOctets_prev": "4208786404",
     *                      "ifOutOctets_delta": "315436",
     *                      "ifOutOctets_rate": "1048",
     *                      "poll_time": "1527165043",
     *                      "poll_prev": "1527164742",
     *                      "poll_period": "301",
     *                  },
     *              ]
     *          }
     *      }
     *
     * @apiUse NotFoundError
     *
     */
    public function show(Bill $bill)
    {
        $input = $this->booleanInput('previous');
        return $this->objectResponse($bill->load('ports'));
    }

    /**
     *
     * @api {post} /api/v1/bills Create a new Bill
     * @apiName Create_New_Bill
     * @apiGroup Bills
     * @apiVersion  1.0.0
     *
     * @apiParam {Array} ports Array of port IDs for this bill.
     * @apiParam {String} bill_name Name of the bill.
     * @apiParam {Number} bill_day Day of the month.
     * @apiParam {String="quota","cdr"} bill_type Type of Bill cdr or quota.
     * @apiParam {Number} [bill_quota] Optional Bill Quota amount. Required if bill_type is quota.
     * @apiParam {Number} [bill_cdr] Optional Bill Cdr amount. Required if bill_type is cdr.
     * @apiParam {Number} [bill_custid] Optional The Customer ID reference.
     * @apiParam {String} [bill_ref] Optional Bill Reference.
     * @apiParam {String} [bill_notes] Optional Notes for this bill.
     *
     * @apiSuccessExample {json} Success-Response:
     *      HTTP/1.1 200 OK
     *      {
     *          "data": {
     *              "bill_name": "testing123",
     *              "bill_day": "1",
     *              "bill_type": "cdr",
     *              "bill_cdr": "10000",
     *              "bill_custid": "",
     *              "bill_ref": "",
     *              "bill_notes": true,
     *              "rate_95th_in": 0,
     *              "rate_95th_out": 0,
     *              "rate_95th": 0,
     *              "dir_95th": 0,
     *              "total_data": 0,
     *              "total_data_in": 0,
     *              "total_data_out": 0,
     *              "rate_average_in": 0,
     *              "rate_average_out": 0,
     *              "rate_average": 0,
     *              "bill_last_calc": "2018-05-24 11:35:16",
     *              "bill_autoadded": 0,
     *              "bill_id": 6,
     *              "used": "0.000bps",
     *              "overuse": "-",
     *              "percent": 0,
     *              "allowed": "10.000kbps",
     *              "ports": [
     *                  {
     *                      "port_id": 3,
     *                      "device_id": "3",
     *                      "port_descr_type": null,
     *                      "port_descr_descr": null,
     *                      "port_descr_circuit": null,
     *                      "port_descr_speed": null,
     *                      "port_descr_notes": null,
     *                      "ifDescr": "Cambium 10/100 FEC",
     *                      "ifName": "Cambium 10/100 FEC",
     *                      "portName": null,
     *                      "ifIndex": "1",
     *                      "ifSpeed": null,
     *                      "ifConnectorPresent": "0",
     *                      "ifPromiscuousMode": "0",
     *                      "ifHighSpeed": "0",
     *                      "ifOperStatus": "up",
     *                      "ifOperStatus_prev": "up",
     *                      "ifAdminStatus": "up",
     *                      "ifAdminStatus_prev": "up",
     *                      "ifDuplex": null,
     *                      "ifMtu": "0",
     *                      "ifType": "ethernetCsmacd",
     *                      "ifAlias": "None",
     *                      "ifPhysAddress": "0a003ea0acdf",
     *                      "ifHardType": null,
     *                      "ifLastChange": "27978544",
     *                      "ifVlan": "",
     *                      "ifTrunk": null,
     *                      "ifVrf": "0",
     *                      "counter_in": null,
     *                      "counter_out": null,
     *                      "ignore": "0",
     *                      "disabled": "0",
     *                      "detailed": "0",
     *                      "deleted": "0",
     *                      "pagpOperationMode": null,
     *                      "pagpPortState": null,
     *                      "pagpPartnerDeviceId": null,
     *                      "pagpPartnerLearnMethod": null,
     *                      "pagpPartnerIfIndex": null,
     *                      "pagpPartnerGroupIfIndex": null,
     *                      "pagpPartnerDeviceName": null,
     *                      "pagpEthcOperationMode": null,
     *                      "pagpDeviceId": null,
     *                      "pagpGroupIfIndex": null,
     *                      "ifInUcastPkts": "1666843110",
     *                      "ifInUcastPkts_prev": "1666750650",
     *                      "ifInUcastPkts_delta": "92460",
     *                      "ifInUcastPkts_rate": "308",
     *                      "ifOutUcastPkts": "3420209857",
     *                      "ifOutUcastPkts_prev": "3420139313",
     *                      "ifOutUcastPkts_delta": "70544",
     *                      "ifOutUcastPkts_rate": "235",
     *                      "ifInErrors": "18",
     *                      "ifInErrors_prev": "18",
     *                      "ifInErrors_delta": "0",
     *                      "ifInErrors_rate": "0",
     *                      "ifOutErrors": "1",
     *                      "ifOutErrors_prev": "1",
     *                      "ifOutErrors_delta": "0",
     *                      "ifOutErrors_rate": "0",
     *                      "ifInOctets": "3089009061",
     *                      "ifInOctets_prev": "2994420706",
     *                      "ifInOctets_delta": "94588355",
     *                      "ifInOctets_rate": "315295",
     *                      "ifOutOctets": "926233655",
     *                      "ifOutOctets_prev": "898677760",
     *                      "ifOutOctets_delta": "27555895",
     *                      "ifOutOctets_rate": "91853",
     *                      "poll_time": "1527175834",
     *                      "poll_prev": "1527175534",
     *                      "poll_period": "300"
     *                  }
     *              ]
     *          }
     *      }
     *
     * @apiErrorExample {json} Error-Response:
     *      HTTP/1.1 422 Unproccesssable Entity
     *      {
     *          "bill_name": [
     *              "The bill name may not be greater than 255 characters."
     *          ],
     *          "bill_day": [
     *              "The bill day field is required."
     *          ],
     *          "bill_type": [
     *              "The bill type field is required."
     *          ],
     *          "ports.1": [
     *              "The selected ports.1 is invalid."
     *          ]
     *      }
     *
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'ports' => ['required','array'],
            'ports.*'   => 'exists:ports,port_id',
            'bill_name' => 'required|max:255',
            'bill_day'  => 'required|numeric',
            'bill_type' => [
                'required',
                \Illuminate\Validation\Rule::in(['quota','cdr'])
            ],
            'bill_quota' => 'required_if:bill_type,quota',
            'bill_cdr' => 'required_if:bill_type,cdr',
        ]);

        $bill = Bill::create([
            'bill_name'         => $request->bill_name,
            'bill_day'          => $request->bill_day,
            'bill_type'         => $request->bill_type,
            "bill_{$request->bill_type}" => $request["bill_{$request->bill_type}"],
            'bill_custid'       => $request->get('custid', ''),
            'bill_ref'          => $request->get('bill_ref', ''),
            'bill_notes'        => $request->get('bill_notes', ''),
            'rate_95th_in'      => 0,
            'rate_95th_out'     => 0,
            'rate_95th'         => 0,
            'dir_95th'          => 0,
            'total_data'        => 0,
            'total_data_in'     => 0,
            'total_data_out'    => 0,
            'rate_average_in'   => 0,
            'rate_average_out'  => 0,
            'rate_average'      => 0,
            'bill_last_calc'    => date("Y-m-d H:i:s"),
            'bill_autoadded'    => 0
        ]);

        $bill->ports()->sync($request->ports);

        return $this->objectResponse($bill->load('ports'));
    }

    /**
     *
     * @api {put} /api/v1/bills/:id Update a Bill
     * @apiName Update_Bill
     * @apiDescription Update a specific bill
     * @apiGroup Bills
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id The ID of the port
     * @apiParam {Array} [ports] Optional Array of port associated to this bill.
     * This array should be the all the desired associated ports for this bill.
     * If not included in the request the ports will remain as previously entered
     * @apiParam {String} [bill_name] Optional Name of the bill.
     * @apiParam {Number} [bill_day] Optional Day of the month.
     * @apiParam {String="quota","cdr"} [bill_type] Type of Bill cdr or quota.
     * @apiParam {Number} [bill_quota] Optional Bill Quota amount. Required if bill_type is quota.
     * @apiParam {Number} [bill_cdr] Optional Bill Cdr amount. Required if bill_type is cdr.
     * @apiParam {Number} [bill_custid] Optional The Customer ID reference.
     * @apiParam {String} [bill_ref] Optional Bill Reference.
     * @apiParam {String} [bill_notes] Optional Notes for this bill.
     *
     * @apiSuccessExample {json} Success-Response:
     *      HTTP/1.1 200 OK
     *      {
     *          "message": "Bill has been updated"
     *      }
     *
     * @apiUse NotFoundError
     *
     * @apiErrorExample {json} Validation Error-Response:
     *      HTTP/1.1 422 Unproccesssable Entity
     *      {
     *          "bill_name": [
     *              "The bill name may not be greater than 255 characters."
     *          ],
     *          "bill_day": [
     *              "The bill day field is required."
     *          ],
     *          "bill_type": [
     *              "The bill type field is required."
     *          ],
     *          "ports.1": [
     *              "The selected ports.1 is invalid."
     *          ]
     *      }
     *
     */
    public function update(Request $request, Bill $bill)
    {
        $this->validate($request, [
            'ports' => ['array'],
            'ports.*'   => 'exists:ports,port_id',
            'bill_name' => 'max:255',
            'bill_day'  => 'numeric',
            'bill_type' => [
                \Illuminate\Validation\Rule::in(['quota','cdr'])
            ],
            'bill_quota' => 'required_if:bill_type,quota',
            'bill_cdr' => 'required_if:bill_type,cdr',
        ]);

        if ($request->ports) {
            $bill->ports()->sync($request->ports);
        }
        
        if ($request->bill_name) {
            $bill->bill_name = $request->bill_name;
        }
        if ($request->bill_day) {
            $bill->bill_day = $request->bill_day;
        }
        if ($request->bill_type) {
            $bill->bill_type = $request->bill_type;
        }
        if ($request->{"bill_{$bill->bill_type}"}) {
            $bill->{"bill_{$bill->bill_type}"} = $request->{"bill_{$bill->bill_type}"};
        }
        if ($request->bill_custid) {
            $bill->bill_custid = $request->get('custid', '');
        }
        if ($request->bill_ref) {
            $bill->bill_ref = $request->get('bill_ref', '');
        }
        if ($request->bill_notes) {
            $bill->bill_notes = $request->get('bill_notes', '');
        }

        $bill->save();

        return $this->messageResponse("Bill successfully updated");
    }
    /**
     *
     * @api {delete} /api/v1/bills/:id Delete a Bill
     * @apiName Delete_Bill
     * @apiDescription Delete a specific bill and all dependent data
     * @apiGroup Bills
     * @apiVersion  1.0.0
     *
     * @apiParam  {Number} id Id of the bill
     *
     * @apiSuccessExample {json} Success-Response:
     *      HTTP/1.1 200 OK
     *      {
     *          "message": "Bill has been removed"
     *      }
     *
     * @apiUse NotFoundError
     *
     */
    public function destroy(Bill $bill)
    {
        $bill->delete();
        return $this->messageResponse("Bill has been removed");
    }
}
