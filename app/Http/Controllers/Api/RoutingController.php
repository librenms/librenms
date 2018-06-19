<?php

namespace App\Http\Controllers\Api;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class RoutingController extends ApiController
{
    /**
     * @api {get} /api/v1/routing/ospf Get all current OSPF neighbours
     * @apiName Get_routing_ospf
     * @apiGroup Routing
     * @apiVersion  1.0.0
     *
     * @apiUse Pagination
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
     *          ],
     *          "current_page": 1,
     *          "from": 1,
     *          "last_page": 1,
     *          "next_page_url": null,
     *          "path": "http://example.org/api/v1/routing/ospf",
     *          "per_page": 15,
     *          "prev_page_url": null,
     *          "to": 10,
     *          "total": 10
     *     }
     *
     */
    public function ospf(Request $request)
    {
        return $this->paginateResponse(new \App\Models\OspfInstance);
    }

    /**
     * @api {get} /api/v1/routing/vrf Get the current VRFs.
     * @apiName Get_routing_vrf
     * @apiGroup Routing
     * @apiVersion  1.0.0
     *
     * @apiUse Pagination
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
     *          ],
     *          "current_page": 1,
     *          "from": 1,
     *          "last_page": 1,
     *          "next_page_url": null,
     *          "path": "http://example.org/api/v1/routing/vrf",
     *          "per_page": 15,
     *          "prev_page_url": null,
     *          "to": 10,
     *          "total": 10
     *     }
     *
     */
    public function vrf()
    {
        return $this->paginateResponse(new \App\Models\Vrf);
    }

    /**
     * @api {get} /api/v1/routing/bgp List the current BGP sessions.
     * @apiName Get_routing_bgp
     * @apiGroup Routing
     * @apiVersion  1.0.0
     *
     * @apiUse Pagination
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
     *          ],
     *          "current_page": 1,
     *          "from": 1,
     *          "last_page": 1,
     *          "next_page_url": null,
     *          "path": "http://example.org/api/v1/routing/bgp",
     *          "per_page": 15,
     *          "prev_page_url": null,
     *          "to": 10,
     *          "total": 10
     *     }
     *
     */
    public function bgp()
    {
        return $this->paginateResponse(new \App\Models\BgpPeer);
    }


    /**
     * @api {get} /api/v1/routing/cbgp List the current BGP session counters.
     * @apiName Get_routing_cbgp
     * @apiGroup Routing
     * @apiVersion  1.0.0
     *
     * @apiUse Pagination
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
     *          ],
     *          "current_page": 1,
     *          "from": 1,
     *          "last_page": 1,
     *          "next_page_url": http://example.org/api/v1/routing/cbgp?current_page=2,
     *          "path": "http://example.org/api/v1/routing/cbgp",
     *          "per_page": 15,
     *          "prev_page_url": null,
     *          "to": 10,
     *          "total": 10
     *     }
     *
     */
    public function cbgp()
    {
        return $this->paginateResponse(new \App\Models\CbgpPeer);
    }
}
