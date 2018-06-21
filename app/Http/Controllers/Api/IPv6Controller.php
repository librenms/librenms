<?php

namespace App\Http\Controllers\Api;

use App\Models\Alert;
use App\Models\IP\IPv6Address;
use App\Models\IP\IPv6Network;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class IPv6Controller extends ApiController
{
    /**
     *
     * @api {get} /api/v1/addresses/ipv6 Get IPv6 Addresses
     * @apiName Get_ipv6_addresses
     * @apiGroup Addresses
     * @apiVersion  1.0.0
     *
     * @apiUse Pagination
     *
     * @apiExample {curl} Example usage wihout parameters:
     *     curl -H 'X-Auth-Token: YOURAPITOKENHERE' -H 'Content-Type: application/json' https://example.org/api/v1/addresses/ipv6?current_page=1
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          data: [
     *             {
     *                 "ipv6_address_id": 2,
     *                 "ipv6_address": "2001:0db8:85a3:0000:0000:8a2e:0370:7334",
     *                 "ipv6_compressed": "2001:db8:85a3::8a2e:370:7334",
     *                 "ipv6_prefixlen": "48",
     *                 "ipv6_origin": "linklayer",
     *                 "ipv6_network_id": "2",
     *                 "port_id": "52",
     *                 "context_name": ""
     *             }
     *         ],
     *         "current_page": 1,
     *         "from": 1,
     *         "last_page": 1,
     *         "next_page_url": null,
     *         "path": "http://example.org/api/v1/addresses/ipv6",
     *         "per_page": 50,
     *         "prev_page_url": null,
     *         "to": 41,
     *         "total": 41
     *     }
     */
    public function index()
    {
        return $this->paginateResponse(new IPv6Address);
    }

    /**
     *
     * @api {get} /api/v1/addresses/ipv6/networks Get IPv6 Networks
     * @apiName Get_ipv6_networks
     * @apiGroup Addresses
     * @apiVersion  1.0.0
     *
     * @apiUse Pagination
     *
     * @apiExample {curl} Example usage wihout parameters:
     *     curl -H 'X-Auth-Token: YOURAPITOKENHERE' -H 'Content-Type: application/json' https://example.org/api/v1/addresses/ipv6/networks?current_page=3
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          data: [
     *              {
     *                 "ipv6_network_id": 1,
     *                 "ipv6_network": "::/48",
     *                 "context_name": ""
     *             },
     *         ],
     *         "from": 1,
     *         "last_page": 1,
     *         "next_page_url": null,
     *         "path": "http://example.org/api/v1/addresses/ipv6/networks",
     *         "per_page": 50,
     *         "prev_page_url": null,
     *         "to": 41,
     *         "total": 41
     *     }
     */
    public function networks()
    {
        return $this->paginateResponse(new IPv6Network);
    }

    /**
     *
     * @api {get} /api/v1/addresses/ipv6/networks/:id Get IPv6 Network Addresses
     * @apiName Get_ipv6_network_addresses
     * @apiGroup Addresses
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} [id] The ID of the network
     * @apiUse Pagination
     *
     * @apiExample {curl} Example usage wihout parameters:
     *     curl -H 'X-Auth-Token: YOURAPITOKENHERE' -H 'Content-Type: application/json' https://example.org/api/v1/addresses/ipv6/networks/34
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          data: {
     *              "ipv6_network_id": 1,
     *              "ipv6_network": "::/48",
     *              "context_name": "",
     *              "addresses": [
     *                  {
     *                      "ipv6_address_id": 1,
     *                      "ipv6_address": "2001:0db8:85a3:0000:0000:8a2e:0370:7334",
     *                      "ipv6_compressed": "2001:db8:85a3::8a2e:370:7334",
     *                      "ipv6_prefixlen": "48",
     *                      "ipv6_origin": "linklayer",
     *                      "ipv6_network_id": "1",
     *                      "port_id": "52",
     *                      "context_name": ""
     *                  }
     *              ]
     *          }
     *     }
     */
    public function show($id)
    {
        $network = IPv6Network::find($id);
        $network->load('addresses');
        return $this->objectResponse($network);
    }
}
