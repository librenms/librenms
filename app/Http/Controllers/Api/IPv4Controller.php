<?php

namespace App\Http\Controllers\Api;

use App\Models\Alert;
use App\Models\IP\IPv4Address;
use App\Models\IP\IPv4Network;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class IPv4Controller extends ApiController
{
    /**
     *
     * @api {get} /api/v1/addresses/ipv4 Get IPv4 Addresses
     * @apiName Get_ipv4_addresses
     * @apiGroup Addresses
     * @apiVersion  1.0.0
     *
     * @apiUse Pagination
     * 
     * @apiExample {curl} Example usage wihout parameters:
     *     curl -H 'X-Auth-Token: YOURAPITOKENHERE' -H 'Content-Type: application/json' https://example.org/api/v1/addresses/ipv4?current_page=2
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          data: [
     *             {
     *                 "ipv4_address_id": 72,
     *                 "ipv4_address": "1.1.1.1",
     *                 "ipv4_prefixlen": "16",
     *                 "ipv4_network_id": "43",
     *                 "port_id": "18",
     *                 "context_name": ""
     *             }
     *         ],
     *         "current_page": 1,
     *         "from": 1,
     *         "last_page": 1,
     *         "next_page_url": null,
     *         "path": "http://example.org/api/v1/addresses/ipv4",
     *         "per_page": 50,
     *         "prev_page_url": null,
     *         "to": 41,
     *         "total": 41
     *     }
     */
    public function index()
    {
        return $this->paginateResponse(new IPv4Address);
    }

    /**
     *
     * @api {get} /api/v1/addresses/ipv4/networks Get IPv4 Networks
     * @apiName Get_ipv4_networks
     * @apiGroup Addresses
     * @apiVersion  1.0.0
     *
     * @apiUse Pagination
     * 
     * @apiExample {curl} Example usage wihout parameters:
     *     curl -H 'X-Auth-Token: YOURAPITOKENHERE' -H 'Content-Type: application/json' https://example.org/api/v1/addresses/ipv4/networks?current_page=3
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          data: [
     *              {
     *                  "ipv4_network_id": 43,
     *                  "ipv4_network": "10.1.0.0/16",
     *                  "context_name": ""
     *              }
     *         ],
     *         "current_page": 1,
     *         "from": 1,
     *         "last_page": 1,
     *         "next_page_url": null,
     *         "path": "http://example.org/api/v1/addresses/ipv4/networks",
     *         "per_page": 50,
     *         "prev_page_url": null,
     *         "to": 41,
     *         "total": 41
     *     }
     */
    public function networks()
    {
        return $this->paginateResponse(new IPv4Network);
    }

    /**
     *
     * @api {get} /api/v1/addresses/ipv4/networks/:id Get IPv4 Network Addresses
     * @apiName Get_ipv4_network_addresses
     * @apiGroup Addresses
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} [id] The ID of the network
     * @apiUse Pagination
     *
     * @apiExample {curl} Example usage wihout parameters:
     *     curl -H 'X-Auth-Token: YOURAPITOKENHERE' -H 'Content-Type: application/json' https://example.org/api/v1/addresses/ipv4/networks/34
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          data: {
     *              "ipv4_network_id": 34,
     *              "ipv4_network": "10.1.1.1/24",
     *              "context_name": "",
     *              "addresses": [
     *                  {
     *                      "ipv4_address_id": 63,
     *                      "ipv4_address": "10.1.1.2",
     *                      "ipv4_prefixlen": "27",
     *                      "ipv4_network_id": "34",
     *                      "port_id": "1483",
     *                      "context_name": ""
     *                  }
     *              ]
     *          }
     *     }
     */
    public function show($id)
    {
        $network = IPv4Network::find($id);
        $network->load('addresses');
        return $this->objectResponse($network);
    }
}
