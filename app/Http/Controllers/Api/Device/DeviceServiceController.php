<?php

namespace App\Http\Controllers\Api\Device;

use App\Models\Device;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class DeviceServiceController extends ApiController
{
    /**
     * @api {get} /api/v1/devices/:id/services Get device services
     * @apiName Get_Device_services
     * @apiGroup Device Services
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id ID or Hostname of the Device
     * @apiParam {String="ok","warning","critical"} [state] Optional Only which have a certain state
     * @apiParam {String} [type] Optional Service type used sql LIKE to find services, so for tcp, use type=tcp for http use type=http
     *
     * @apiExample {curl} Example usage wihout parameters:
     *     curl -H 'X-Auth-Token: YOURAPITOKENHERE' -i http://example.org/api/v1/devices/1/services
     *
     * @apiExample {curl} Example usage with parameters:
     *     curl -H 'X-Auth-Token: YOURAPITOKENHERE' -i http://example.org/api/v1/devices/1/services?state=ok&type=http
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *            {
     *                "service_id": "2",
     *                "device_id": "2",
     *                "service_ip": "demo2.yourdomian.net",
     *                "service_type": "esxi_hardware.py",
     *                "service_desc": "vmware hardware",
     *                "service_param": "-H 192.168.1.11 -U USER -P PASS -p",
     *                "service_ignore": "0",
     *                "service_status": "0",
     *                "service_changed": "1471702206",
     *                "service_message": "OK - Server: Supermicro X9SCL/X9SCM s/n: 0123456789 System BIOS: 2.2 2015-02-20",
     *                "service_disabled": "0",
     *                "service_ds": "{\"P2Vol_0_Processor_1_Vcore\":\"\",\"P2Vol_1_System_Board_1_-12V\":\"\",\"P2Vol_2_System_Board_1_12V\":\"\",\"P2Vol_3_System_Board_1_3.3VCC\":\"\",\"P2Vol_4_System_Board_1_5VCC\":\"\",\"P2Vol_5_System_Board_1_AVCC\":\"\",\"P2Vol_6_System_Board_1_VBAT\":\"\",\"P2Vol_7_System_Board_1_"
     *            }
     *        ]
     *     }
     *
     */
    public function index(Device $device)
    {
        $this->validate(request(), [
            'state' =>  \Illuminate\Validation\Rule::in(['ok', 'warning', 'critical']),
            'type'  => 'max:255'
        ]);

        $state = request()->get('state', null);
        $type = request()->get('type', null);

        $query = $device->services();

        if ($state) {
            $query = $query->state($state);
        }
        if ($type) {
            $query = $query->where('service_type', 'LIKE', "%$type%");
        }

        return $this->objectResponse($query->get());
    }

    /**
     * @api {post} /api/v1/devices/:id/services Create device service
     * @apiName Create_Device_Service
     * @apiGroup Device Services
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id ID or Hostname of the Device
     * @apiParam {String} type Service type (Ex: http.c, ntp.c, ...)
     * @apiParam {String} ip Ip of the service
     * @apiParam {String} [desc] Optional  Descrtion to use for the service
     * @apiParam {String} [param] Optional Parameters for the service
     * @apiParam {Boolean} [ignore] Optional Ignore the service for checks

     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *            {
     *                "service_id": "2",
     *                "device_id": "2",
     *                "service_ip": "demo2.yourdomian.net",
     *                "service_type": "esxi_hardware.py",
     *                "service_desc": "vmware hardware",
     *                "service_param": "-H 192.168.1.11 -U USER -P PASS -p",
     *                "service_ignore": "0",
     *                "service_status": "0",
     *                "service_changed": "1471702206",
     *                "service_message": "OK - Server: Supermicro X9SCL/X9SCM s/n: 0123456789 System BIOS: 2.2 2015-02-20",
     *                "service_disabled": "0",
     *                "service_ds": "{\"P2Vol_0_Processor_1_Vcore\":\"\",\"P2Vol_1_System_Board_1_-12V\":\"\",\"P2Vol_2_System_Board_1_12V\":\"\",\"P2Vol_3_System_Board_1_3.3VCC\":\"\",\"P2Vol_4_System_Board_1_5VCC\":\"\",\"P2Vol_5_System_Board_1_AVCC\":\"\",\"P2Vol_6_System_Board_1_VBAT\":\"\",\"P2Vol_7_System_Board_1_"
     *            }
     *        ]
     *     }
     *
     * @apiErrorExample Error-Response:
     *      HTTP/1.1 422 Unproccessable Entity
     *      {
     *          "type": [
     *              "The selected type is invalid."
     *          ],
     *          "ip": [
     *              "The ip must be a valid IP address."
     *          ]
     *      }
     */
    public function store(Request $request, Device $device)
    {
        $this->validate(
            $request, [
                'type'  => [
                    'required',
                    \Illuminate\Validation\Rule::in(list_available_services())
                ],
                'ip'    => 'required|ip',
                'ignore' => [
                    \Illuminate\Validation\Rule::in([0, 1, true, false, "true", "false", "1", "0"]),
                ]
            ],
            [
                'ignore.*'    => "The ignore field must be a boolean"
            ]
        );
        
        $service = $device->services()->create([
            'service_type'      => $request->type,
            'service_ip'        => $request->ip,
            'service_desc'      => $request->get('desc', ''),
            'service_param'     => $request->get('param', ''),
            'service_ignore'    => $request->get('ignore', false),
            'service_message'   => "Service not yet checked",
            'service_ds'        => "{}"
        ]);
        
        return $this->objectResponse($service);
    }
}
