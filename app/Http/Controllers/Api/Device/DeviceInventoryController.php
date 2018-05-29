<?php

namespace App\Http\Controllers\Api\Device;

use App\Models\Device;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class DeviceInventoryController extends ApiController
{
    /**
     * @api {get} /api/v1/devices/:id/inventory Get inventory for a Device
     * @apiName Get_Device_Inventory
     * @apiGroup Device Inventory
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id ID or Hostname of the Device
     * @apiParam  {String} [entPhysicalClass] Optional This is used to restrict the class of the inventory,
     * for example you can specify chassis to only return items in the inventory that are labelled as chassis.
     * @apiParam {Number} [entPhysicalContainedIn] Optional This is used to retrieve items within the inventory assigned to a previous component,
     * for example specifying the chassis (entPhysicalIndex) will retrieve all items where the chassis is the parent.
     *
     * @apiExample {curl} Example usage wihout parameters:
     *     curl -H 'X-Auth-Token: YOURAPITOKENHERE' https://example.org/api/v1/devices/1/inventory?entPhysicalContainedIn=65536
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *              {
     *                  "entPhysical_id": "2",
     *                  "device_id": "32",
     *                  "entPhysicalIndex": "262145",
     *                  "entPhysicalDescr": "Linux 3.3.5 ehci_hcd RB400 EHCI",
     *                  "entPhysicalClass": "unknown",
     *                  "entPhysicalName": "1:1",
     *                  "entPhysicalHardwareRev": "",
     *                  "entPhysicalFirmwareRev": "",
     *                  "entPhysicalSoftwareRev": "",
     *                  "entPhysicalAlias": "",
     *                  "entPhysicalAssetID": "",
     *                  "entPhysicalIsFRU": "false",
     *                  "entPhysicalModelName": "0x0002",
     *                  "entPhysicalVendorType": "zeroDotZero",
     *                  "entPhysicalSerialNum": "rb400_usb",
     *                  "entPhysicalContainedIn": "65536",
     *                  "entPhysicalParentRelPos": "-1",
     *                  "entPhysicalMfgName": "0x1d6b",
     *                  "ifIndex": "0"
     *              }
     *          ]
     *     }
     *
     * @apiUse NotFoundError
     */
    public function index(Device $device, Request $request)
    {
        $query = $device->inventory();

        if ($request->entPhysicalClass) {
            $query->where('entPhysicalClass', $rquest->entPhyicalClass);
        }

        if ($request->entPhysicalContainedIn) {
            $query->where('entPhysicalContainedIn', $rquest->entPhysicalContainedIn);
        }

        return $this->objectResponse($query->get());
    }
}
