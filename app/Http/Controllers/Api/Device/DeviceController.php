<?php

namespace App\Http\Controllers\Api\Device;

use Librenms\Config;
use App\Models\Device;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class DeviceController extends ApiController
{
    /**
     * @api {get} /devices Get All Devices
     * @apiName Get_Devices
     * @apiGroup Device Get a list of all devices in librenms
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *              {
     *                  "device_id": 1,
     *                  "hostname": "localhost",
     *                  "sysName": "example.librenms.org",
     *                  "ip": null,
     *                  "community": "public",
     *                  "authlevel": "noAuthNoPriv",
     *                  "authname": "",
     *                  "authpass": "",
     *                  "authalgo": "MD5",
     *                  "cryptopass": "",
     *                  "cryptoalgo": "AES",
     *                  "snmpver": "v2c",
     *                  "port": "161",
     *                  "transport": "udp",
     *                  "timeout": null,
     *                  "retries": null,
     *                  "snmp_disable": "0",
     *                  "bgpLocalAs": null,
     *                  "sysObjectID": ".1.3.6.1.4.1.8072.3.2.10",
     *                  "sysDescr": "Linux 3.10.0-693.17.1.el7.x86_64 #1 SMP Thu Jan 25 20:13:58 UTC 2018 x86_64",
     *                  "sysContact": "Root <root@localhost> (configure /etc/snmp/snmp.local.conf)",
     *                  "version": "3.10.0-693.17.1.el7.x86_64",
     *                  "hardware": "Generic x86 64-bit",
     *                  "features": null,
     *                  "location": "NOC",
     *                  "os": "linux",
     *                  "status": "1",
     *                  "status_reason": "",
     *                  "ignore": "0",
     *                  "disabled": "0",
     *                  "uptime": "3805884",
     *                  "agent_uptime": "0",
     *                  "last_polled": "2018-05-18 09:25:30",
     *                  "last_poll_attempted": null,
     *                  "last_polled_timetaken": "22.39",
     *                  "last_discovered_timetaken": "10.63",
     *                  "last_discovered": "2018-05-18 06:34:41",
     *                  "last_ping": "2018-05-18 09:25:30",
     *                  "last_ping_timetaken": "0.35",
     *                  "purpose": null,
     *                  "type": "server",
     *                  "serial": null,
     *                  "icon": "http://example.org/images/os/linux.svg",
     *                  "poller_group": "0",
     *                  "override_sysLocation": "0",
     *                  "notes": null,
     *                  "port_association_mode": "1"
     *              }
     *          ],
     *          "current_page": 1,
     *          "from": 1,
     *          "last_page": 1,
     *          "next_page_url": null,
     *          "path": "http://example.org/api/v1/devices",
     *          "per_page": 15,
     *          "prev_page_url": null,
     *          "to": 10,
     *          "total": 10
     *     }
     *
     */
    public function index()
    {
        return $this->paginateResponse(new Device);
    }

    /**
     * @api {get} /devices/:id Get individual Device
     * @apiName Get_Device
     * @apiGroup Device Get an individual device
     *
     * @apiParam {Number} id Id of the Device
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          {
     *             "device_id": 1,
     *             "hostname": "localhost",
     *             "sysName": "example.librenms.org",
     *             "ip": null,
     *             "community": "public",
     *             "authlevel": "noAuthNoPriv",
     *             "authname": "",
     *             "authpass": "",
     *             "authalgo": "MD5",
     *             "cryptopass": "",
     *             "cryptoalgo": "AES",
     *             "snmpver": "v2c",
     *             "port": "161",
     *             "transport": "udp",
     *             "timeout": null,
     *             "retries": null,
     *             "snmp_disable": "0",
     *             "bgpLocalAs": null,
     *             "sysObjectID": ".1.3.6.1.4.1.8072.3.2.10",
     *             "sysDescr": "Linux 3.10.0-693.17.1.el7.x86_64 #1 SMP Thu Jan 25 20:13:58 UTC 2018 x86_64",
     *             "sysContact": "Root <root@localhost> (configure /etc/snmp/snmp.local.conf)",
     *             "version": "3.10.0-693.17.1.el7.x86_64",
     *             "hardware": "Generic x86 64-bit",
     *             "features": null,
     *             "location": "NOC",
     *             "os": "linux",
     *             "status": "1",
     *             "status_reason": "",
     *             "ignore": "0",
     *             "disabled": "0",
     *             "uptime": "3805884",
     *             "agent_uptime": "0",
     *             "last_polled": "2018-05-18 09:25:30",
     *             "last_poll_attempted": null,
     *             "last_polled_timetaken": "22.39",
     *             "last_discovered_timetaken": "10.63",
     *             "last_discovered": "2018-05-18 06:34:41",
     *             "last_ping": "2018-05-18 09:25:30",
     *             "last_ping_timetaken": "0.35",
     *             "purpose": null,
     *             "type": "server",
     *             "serial": null,
     *             "icon": "http://example.org/images/os/linux.svg",
     *             "poller_group": "0",
     *             "override_sysLocation": "0",
     *             "notes": null,
     *             "port_association_mode": "1"
     *         }
     *     }
     *
     */
    public function show(Device $device)
    {
        return $this->objectResponse($device);
    }

    /**
     * @api {post} /devices Create a new Device
     * @apiName Create_Device
     * @apiGroup Device Create a new device
     *
     * @apiParam {String} hostname The hostname of the new device
     * @apiParam {Number} port="161" The SNMP port to use
     * @apiParam {String="v1,v2c,v3"} version="v2c" The SNMP version
     * @apiParam {String} [transport=udp] Optional
     * @apiParam {Number} [poller_group=0] Optional
     * @apiParam {Boolean} [force_add=false] Optional
     * @apiParam {String} [os] Optional
     * @apiParam {String} [hardware] Optional
     * @apiParam {Object} [v3] Optional The v3 object is only required when the snmp is set accordingly
     * @apiParam {String} v3.authlevel
     * @apiParam {String} v3.authname
     * @apiParam {String} v3.authpass
     * @apiParam {String} v3.authalgo
     * @apiParam {String} v3.cryptopass
     * @apiParam {String} v3.cryptoalgo
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": 
     *          {
     *             "device_id": 1,
     *             "hostname": "localhost",
     *             "sysName": "example.librenms.org",
     *             "ip": null,
     *             "community": "public",
     *             "authlevel": "noAuthNoPriv",
     *             "authname": "",
     *             "authpass": "",
     *             "authalgo": "MD5",
     *             "cryptopass": "",
     *             "cryptoalgo": "AES",
     *             "snmpver": "v2c",
     *             "port": "161",
     *             "transport": "udp",
     *             "timeout": null,
     *             "retries": null,
     *             "snmp_disable": "0",
     *             "bgpLocalAs": null,
     *             "sysObjectID": ".1.3.6.1.4.1.8072.3.2.10",
     *             "sysDescr": "Linux 3.10.0-693.17.1.el7.x86_64 #1 SMP Thu Jan 25 20:13:58 UTC 2018 x86_64",
     *             "sysContact": "Root <root@localhost> (configure /etc/snmp/snmp.local.conf)",
     *             "version": "3.10.0-693.17.1.el7.x86_64",
     *             "hardware": "Generic x86 64-bit",
     *             "features": null,
     *             "location": "NOC",
     *             "os": "linux",
     *             "status": "1",
     *             "status_reason": "",
     *             "ignore": "0",
     *             "disabled": "0",
     *             "uptime": "3805884",
     *             "agent_uptime": "0",
     *             "last_polled": "2018-05-18 09:25:30",
     *             "last_poll_attempted": null,
     *             "last_polled_timetaken": "22.39",
     *             "last_discovered_timetaken": "10.63",
     *             "last_discovered": "2018-05-18 06:34:41",
     *             "last_ping": "2018-05-18 09:25:30",
     *             "last_ping_timetaken": "0.35",
     *             "purpose": null,
     *             "type": "server",
     *             "serial": null,
     *             "icon": "http://example.org/images/os/linux.svg",
     *             "poller_group": "0",
     *             "override_sysLocation": "0",
     *             "notes": null,
     *             "port_association_mode": "1"
     *         }
     *     }
     *
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'hostname' => 'required',
            'version'   => [
                \Illuminate\Validation\Rule::in(['v1', 'v2c', 'v3'])
            ],
            'v3.authlevel' => 'required_if:version,v3',
            'v3.authname' => 'required_if:version,v3',
            'v3.authpass' => 'required_if:version,v3',
            'v3.authalgo' => 'required_if:version,v3',
            'v3.cryptopass' => 'required_if:version,v3',
            'v3.cryptoalgo' => 'required_if:version,v3',
        ]);
        $snmpver = 'v2c';
        $hostname     = $request->hostname;
        $port         = (isset($request->port) ? $request->port : Config::get('snmp.port'));
        $transport    = (isset($request->transport) ? $request->transport : 'udp');
        $poller_group = (isset($request->poller_group) ? $request->poller_group : 0);
        $force_add    = ($request->force_add ? true : false);
        $snmp_disable = ($request->snmp_disable);
        if ($snmp_disable) {
            $additional = array(
                'os'           => $request->os ? mres($request->os) : 'ping',
                'hardware'     => $request->hardware ? mres($request->hardware) : '',
                'snmp_disable' => 1,
            );
        } elseif ($request->version == 'v1' || $request->version == 'v2c') {
            if ($request->community) {
                $config['snmp']['community'] = array($data['community']);
            }

            $snmpver = mres($request->version);
        } elseif ($request->version == 'v3') {
            $v3 = array(
                'authlevel'  => mres($request->v3['authlevel']),
                'authname'   => mres($request->v3['authname']),
                'authpass'   => mres($request->v3['authpass']),
                'authalgo'   => mres($request->v3['authalgo']),
                'cryptopass' => mres($request->v3['cryptopass']),
                'cryptoalgo' => mres($request->v3['cryptoalgo']),
            );

            array_push(Config::get('snmp.v3'), $v3);
            $snmpver = 'v3';
        } else {
            $this->errorResponse(400, 'You haven\'t specified an SNMP version to use');
        }
        try {
            // TODO: Add host method
            // $device_id = addHost($hostname, $snmpver, $port, $transport, $poller_group, $force_add, 'ifIndex', $additional);
            // return $this->objectResponse(Device::find($device_id));

        } catch (Exception $e) {
            $this->errorResponse(500, $e->getMessage());
        }
    }

    /**
     * Update device
     * Update a device by ID
     *
     * @method PUT
     * @param App\Models\Device $device Device loaded from route model binding
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Device $device)
    {

    }

    /**
     * Delete Device
     * Delete device by ID.
     *
     * @method DELETE
     * @param App\Models\Device $device Device loaded from route model binding
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Device $device)
    {
        $device->destroy();
        return $this->messageResponse("Removed device $device->hostname");
    }
}
