<?php

namespace App\Http\Controllers\Api\Device;

use App\Models\Device;
use App\Models\Component;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class DeviceComponentController extends ApiController
{
    /**
     * @api {get} /api/v1/devices/:id/components Get all components for a device
     * @apiName Get_Device_Components
     * @apiGroup Device Components
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id The id or Hostname of the Device
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          [
     *              {
     *                  "id": 1,
     *                  "device_id": "1",
     *                  "type": "test",
     *                  "label": "a really cool component",
     *                  "status": "1",
     *                  "disabled": "0",
     *                  "ignore": "0",
     *                  "error": null,
     *                  "prefs": [
     *                      {
     *                          "id": 1,
     *                          "component": "1",
     *                          "attribute": "Attribute 1",
     *                          "value": "this value"
     *                      },
     *                      {
     *                          "id": 2,
     *                          "component": "1",
     *                          "attribute": "Attribute 2",
     *                          "value": "Attribute 2 value"
     *                      },
     *                      {
     *                          "id": 3,
     *                          "component": "1",
     *                          "attribute": "Attribute 3",
     *                          "value": "Attribute 3 value"
     *                      }
     *                  ]
     *              }
     *          ]
     *     }
     *
     * @apiErrorExample {json} Error-Response:
     *      HTTP/1.1 404 Not-Found
     *      {
     *          "status": "Item not Found"
     *      }
     */
    public function index(Device $device)
    {
        return $this->objectResponse($device->components()->with('prefs')->get());
    }

    /**
     * @api {post} /api/v1/devices/:id/components Create component for a device
     * @apiName Create_Device_Component
     * @apiGroup Device Components
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id The id or Hostname of the Device
     * @apiParam {Number} component_id The id of the Component
     * @apiParam {String} type The type of component to add
     * @apiParam {String} [label] The label for the component
     * @apiParam {Boolean} [ignore=false] Ignore alerts on this component, defaults to false
     * @apiParam {Boolean} [disabled=false] Disable component entierly, defaults to false
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          {
     *              "type": "APITTEST4",
     *              "label": "What a great 4 label",
     *              "ignore": 0,
     *              "disabled": 0,
     *              "device_id": 1,
     *              "id": 6
     *          }
     *     }
     *
     * @apiErrorExample {json} Validation-Error-Response:
     *      HTTP/1.1 422 Unprocessable Entity
     *      {
     *          "type": [
     *              "The type field is required."
     *          ],
     *          "ignore": [
     *              "The ignore field must be true or false."
     *          ],
     *          "disabled": [
     *              "The disabled field must be true or false."
     *          ]
     *      }
     *
     * @apiErrorExample {json} Error-Response:
     *      HTTP/1.1 404 Not-Found
     *      {
     *          "status": "Item not Found"
     *      }
     */
    public function store(Request $request, Device $device)
    {
        $this->validate(
            $request,
            [
                'type'      => 'required|max:255',
                'ignore'    => 'ext_bool',
                'disabled'  => 'ext_bool',
                'label'     => 'max:500'
            ]
        );

        $component = $device->components()->create([
            'type'      =>  $request->type,
            'label'     =>  $request->get('label', ''),
            'ignore'    =>  $request->get('ignore', false),
            'disabled'  =>  $request->get('disabled', false),
        ]);

        return $this->objectResponse($component);
    }

    /**
     * @api {put} /api/v1/devices/:id/components/:component_id Update component for a device
     * @apiName Update_Device_Component
     * @apiGroup Device Components
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id The id or Hostname of the Device
     * @apiParam {Number} component_id The id of the Component to update
     * @apiParam {String} [type] The type of component to add
     * @apiParam {String} [label] The label for the component
     * @apiParam {Boolean} [ignore] Ignore alerts on this component
     * @apiParam {Boolean} [disabled] Disable component entierly
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          {
     *              "message": "Component #1 has been deleted."
     *          }
     *     }
     *
     * @apiErrorExample {json} Validation-Error-Response:
     *      HTTP/1.1 422 Unprocessable Entity
     *      {
     *          "ignore": [
     *              "The ignore field must be true or false."
     *          ],
     *          "disabled": [
     *              "The disabled field must be true or false."
     *          ]
     *      }
     *
     * @apiErrorExample {json} Error-Response:
     *      HTTP/1.1 404 Not-Found
     *      {
     *          "status": "Item not Found"
     *      }
     */
    public function update(Request $request, Device $device, Component $component)
    {
        $this->validate(
            $request,
            [
                'type'      => 'max:255',
                'ignore'    => 'ext_bool',
                'disabled'  => 'ext_bool',
                'label'     => 'max:500'
            ]
        );
        
        $component->update(array_filter($request->all()));
        return $this->messageResponse("Component #$component->id updated.");
    }

    /**
     * @api {delete} /api/v1/devices/:id/components/:component_id Delete component for a device
     * @apiName Delete_Device_Component
     * @apiGroup Device Components
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id The id or Hostname of the Device
     * @apiParam {Number} component_id The id of the Component to delete
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          {
     *              "message": "Component #1 has been deleted."
     *          }
     *     }
     *
     * @apiErrorExample {json} Error-Response:
     *      HTTP/1.1 404 Not-Found
     *      {
     *          "status": "Item not Found"
     *      }
     */
    public function destroy(Device $device, Component $component)
    {
        $id = $component->id;
        $component->delete();
        return $this->messageResponse("Component #$id has been deleted");
    }
}
