<?php

namespace App\Http\Controllers\Api\Device;

use App\Models\Device;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class DeviceRoutingController extends ApiController
{
    /**
     * @api {get} /api/v1/devices/:id/routing/ospf Get OSPF neighbours for device
     * @apiName Get_device_routing_ospf
     * @apiGroup Device Routing
     * @apiVersion  1.0.0
     *
     * @apiUse DeviceParam
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *              {
     *                  "device_id": "1",
     *                  "port_id": "0",
     *                  "ospf_nbr_id": "172.16.1.145.0",
     *                  "ospfNbrIpAddr": "172.16.1.145",
     *                  "ospfNbrAddressLessIndex": "0",
     *                  "ospfNbrRtrId": "172.16.0.140",
     *                  "ospfNbrOptions": "82",
     *                  "ospfNbrPriority": "1",
     *                  "ospfNbrState": "full",
     *                  "ospfNbrEvents": "5",
     *                  "ospfNbrLsRetransQLen": "0",
     *                  "ospfNbmaNbrStatus": "active",
     *                  "ospfNbmaNbrPermanence": "dynamic",
     *                  "ospfNbrHelloSuppressed": "false",
     *                  "context_name": ""
     *              }
     *          ]
     *     }
     *
     */
    public function ospf(Device $device)
    {
        return $this->objectResponse($device->ospfInstances()->get());
    }

    /**
     * @api {get} /api/v1/devices/:id/routing/vrf Get device current VRFs.
     * @apiName Get_device_routing_vrf
     * @apiGroup Device Routing
     * @apiVersion  1.0.0
     *
     * @apiUse DeviceParam
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *              {
     *                  "vrf_id": "2",
     *                  "vrf_oid": "8.77.103.109.116.45.118.114.102",
     *                  "vrf_name": "Mgmt-vrf",
     *                  "mplsVpnVrfRouteDistinguisher": "",
     *                  "mplsVpnVrfDescription": "",
     *                  "device_id": "8"
     *              },
     *          ]
     *     }
     *
     */
    public function vrf(Device $device)
    {
        return $this->objectResponse($device->vrfs()->get());
    }

    /**
     * @api {get} /api/v1/devices/:id/routing/bgp List the current BGP sessions.
     * @apiName Get_device_routing_bgp
     * @apiGroup Device Routing
     * @apiVersion  1.0.0
     *
     * @apiUse DeviceParam
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *              {
     *                  "bgpPeer_id": "4",
     *                  "device_id": "2",
     *                  "astext": "",
     *                  "bgpPeerIdentifier": "1234:1b80:1:12::2",
     *                  "bgpPeerRemoteAs": "54321",
     *                  "bgpPeerState": "established",
     *                  "bgpPeerAdminStatus": "running",
     *                  "bgpLocalAddr": "1234:1b80:1:12::1",
     *                  "bgpPeerRemoteAddr": "0.0.0.0",
     *                  "bgpPeerInUpdates": "3",
     *                  "bgpPeerOutUpdates": "1",
     *                  "bgpPeerInTotalMessages": "0",
     *                  "bgpPeerOutTotalMessages": "0",
     *                  "bgpPeerFsmEstablishedTime": "0",
     *                  "bgpPeerInUpdateElapsedTime": "0",
     *                  "context_name": ""
     *              },
     *          ]
     *     }
     *
     */
    public function bgp(Device $device)
    {
        return $this->objectResponse($device->bgppeers()->get());
    }


    /**
     * @api {get} /api/v1/devices/:id/routing/cbgp List the current BGP session counters.
     * @apiName Get_device_routing_cbgp
     * @apiGroup Device Routing
     * @apiVersion  1.0.0
     *
     * @apiUse DeviceParam
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *              {
     *                  "device_id": "9",
     *                  "bgpPeerIdentifier": "192.168.99.31",
     *                  "afi": "ipv4",
     *                  "safi": "multicast",
     *                  "AcceptedPrefixes": "2",
     *                  "DeniedPrefixes": "0",
     *                  "PrefixAdminLimit": "0",
     *                  "PrefixThreshold": "0",
     *                  "PrefixClearThreshold": "0",
     *                  "AdvertisedPrefixes": "11487",
     *                  "SuppressedPrefixes": "0",
     *                  "WithdrawnPrefixes": "10918",
     *                  "AcceptedPrefixes_delta": "-2",
     *                  "AcceptedPrefixes_prev": "2",
     *                  "DeniedPrefixes_delta": "0",
     *                  "DeniedPrefixes_prev": "0",
     *                  "AdvertisedPrefixes_delta": "-11487",
     *                  "AdvertisedPrefixes_prev": "11487",
     *                  "SuppressedPrefixes_delta": "0",
     *                  "SuppressedPrefixes_prev": "0",
     *                  "WithdrawnPrefixes_delta": "-10918",
     *                  "WithdrawnPrefixes_prev": "10918",
     *                  "context_name": ""
     *              },
     *          ]
     *     }
     *
     */
    public function cbgp(Device $device)
    {
        return $this->objectResponse($device->cbgp()->get());
    }

    /**
     * @api {get} /api/v1/devices/:id/routing/ipsec List active IPSec tunnels
     * @apiName Get_device_routing_ipsec
     * @apiDescription Please note, this will only show active VPN sessions not all configured.
     * @apiGroup Device Routing
     * @apiVersion  1.0.0
     *
     * @apiUse DeviceParam
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *              {
     *                  "tunnel_id": "1",
     *                  "device_id": "1",
     *                  "peer_port": "0",
     *                  "peer_addr": "127.0.0.1",
     *                  "local_addr": "127.0.0.2",
     *                  "local_port": "0",
     *                  "tunnel_name": "",
     *                  "tunnel_status": "active"
     *              }
     *          ]
     *     }
     *
     */
    public function ipsec(Device $device)
    {
        return $this->objectResponse($device->ipsecTunnels()->get());
    }
}
