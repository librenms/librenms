<?php

namespace App\Http\Controllers\Api;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class ServiceController extends ApiController
{
    /**
     * @api {get} /api/v1/services Get all services
     * @apiName Get_services
     * @apiGroup Services
     * @apiVersion  1.0.0
     *
     * @apiParam {String="ok","warning","critical"} [state] Optional Only which have a certain state
     * @apiParam {String} [type] Optional Service type used sql LIKE to find services, so for tcp, use type=tcp for http use type=http
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
    public function index(Request $request)
    {
        $this->validate($request, [
            'state' =>  \Illuminate\Validation\Rule::in(['ok', 'warning', 'critical']),
            'type'  => 'max:255'
        ]);

        $state = request()->get('state', null);
        $type = request()->get('type', null);

        $query = new Service;

        if ($state) {
            $query = $query->state($state);
        }
        if ($type) {
            $query = $query->where('service_type', 'LIKE', "%$type%");
        }

        return $this->objectResponse($query->get());
    }
}
