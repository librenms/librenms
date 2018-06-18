<?php

namespace App\Http\Controllers\Api;

use App\Models\Rule;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class RuleController extends ApiController
{
    /**
     *
     * @api {get} /api/v1/rules Get alert rules
     * @apiName Get_rules
     * @apiDescription Get all rule details.
     * @apiGroup Rules
     * @apiVersion  1.0.0
     *
     * @apiUse Pagination
     *
     * @apiSuccessExample {type} Success-Response:
     *      HTTP/1.1 200 OK
     *      {
     *          "data": [
     *              {
     *                  "id": "1",
     *                  "device_id": "-1",
     *                  "rule": "%devices.os != \"Juniper\"",
     *                  "severity": "critical",
     *                  "extra": "{\"mute\":false,\"count\":\"15\",\"delay\":\"300\",\"invert\":false}",
     *                  "disabled": "0",
     *                  "name": "A test rule"
     *              },
     *          ],
     *          "current_page": 1,
     *          "from": 1,
     *          "last_page": 4,
     *          "next_page_url": "http://example.org/api/v1/rules?page=2",
     *          "path": "http://example.org/api/v1/rules",
     *          "per_page": 50,
     *          "prev_page_url": null,
     *          "to": 50,
     *          "total": 175
     *      }
     *
     *
     */
    public function index()
    {
        return $this->paginateResponse(new Rule);
    }

    /**
     *
     * @api {get} /api/v1/rules/:id Get individual rule
     * @apiName Get_rule
     * @apiDescription Get details of a rule
     * @apiGroup Rules
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id The id of the rule
     *
     * @apiSuccessExample {type} Success-Response:
     *      HTTP/1.1 200 OK
     *      {
     *          "data": {
     *              "id": "1",
     *              "device_id": "1",
     *              "rule": "%devices.os != \"Juniper\"",
     *              "severity": "warning",
     *              "extra": "{\"mute\":true,\"count\":\"15\",\"delay\":null,\"invert\":false}",
     *              "disabled": "0",
     *              "name": "A test rule"
     *          }
     *      }
     *
     * @apiUse NotFoundError
     */
    public function show(Rule $rule)
    {
        return $this->objectResponse($rule);
    }

    /**
     *
     * @api {post} /rules Create new Rule
     * @apiName Create_rule
     * @apiDescription Add a new alert rule.
     * @apiGroup Rules
     * @apiVersion  1.0.0
     *
     * @apiParam {String} name The name of the rule
     * @apiParam {String} rule The rule which should be in the format %entity $condition $value (i.e `%devices.status != 0` for devices marked as down).
     * @apiParam {Boolean} [mute=false] Show alert status but mute notifications
     * @apiParam {Boolean} [disabled=false] Disable rule
     * @apiParam {String="ok","critical","warning"} severity How to display the alert, OK:`Green`, Warning:`Yellow`, Critical:`Red`
     * @apiParam {Boolean} [invert=false] Alert when this rule doesn't match
     * @apiParam {Number} [count=-1] This is how many polling runs before an alert will trigger and the frequency.
     * @apiParam {Array} [devices] An array of device ID's to map to this rule. If empty the rule will be global.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          {
     *              "id": 1,
     *              "rule": "%macros.device_down = \"1\"",
     *              "severity": "critical",
     *              "extra": "{\"mute\":false,\"count\":-1,\"delay\":300,\"invert\":false,\"interval\":300}",
     *              "disabled": "0",
     *              "name": "Devices up/down",
     *              "query": "SELECT * FROM devices WHERE (devices.device_id = ?) && (((devices.status = 0  &&  ((devices.disabled = 0  &&  devices.ignore = 0)))) = \"1\")",
     *              "builder": "",
     *              "proc": null
     *          },
     *      }
     *
     * @apiErrorExample {json} Error-Response:
     *      HTTP/1.1 422 Unproccesssable Entity
     *      {
     *          "rule": [
     *              "The rule field is required."
     *          ],
     *          "name": [
     *              "The name field is required."
     *          ]
     *      }
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'  =>  'required|unique:alert_rules,name',
            'mute'  =>  'ext_bool',
            'disabled'  => 'ext_bool',
            'severity'  => [\Illuminate\Validation\Rule::in(['ok', 'critical', 'warning']), 'required'],
            'invert'    => 'ext_bool',
            'count'     => 'numeric',
            'rule'      => 'required',
            'devices'   => 'array'
        ]);

        $extra = [
            'count' =>  $request->get('count', -1),
            'mute'  =>  $request->get('mute', false),
            'delay' =>  $request->get('delay', 300),
            'invert' => $request->get('invert', false),
            'interval' => $request->get('interval', 300)
        ];

        $rule = Rule::create([
            'name'      => $request->name,
            'disabled'  => $request->get('disabled', false),
            'severity'  => $request->severity,
            'rule'      => $request->rule,
            'extra'     => json_encode($extra),
            'query'     => '' // TODO: Actually generate query?
        ]);

        if ($request->devices) {
            $rule->devices()->sync($request->devices);
        }

        return $this->objectResponse($rule);
    }

    public function update(Request $request, Rule $rule)
    {
        $this->validate($request, [
            'name'  =>  "required|unique:alert_rules,name,except,$rule->id",
            'mute'  =>  'ext_bool',
            'disabled'  => 'ext_bool',
            'severity'  => [\Illuminate\Validation\Rule::in(['ok', 'critical', 'warning']), 'required'],
            'invert'    => 'ext_bool',
            'count'     => 'numeric',
            'rule'      => 'required',
            'devices'   => 'array'
        ]);
        
        $current = json_decode($rule->extra);
        $extra = [
            'count' =>  $request->get('count', $current['count']),
            'mute'  =>  $request->get('mute', $current['mute']),
            'delay' =>  $request->get('delay', $current['delay']),
            'invert' => $request->get('invert', $current['invert']),
            'interval' => $request->get('interval', $current['interval'])
        ];

        $rule = Rule::create([
            'name'      => $request->get('name', $rule->name),
            'disabled'  => $request->get('disabled', $rule->disabled),
            'severity'  => $request->get('severity', $rule->severity),
            'rule'      => $request->get('rule', $rule->rule),
            'extra'     => json_encode($extra),
            'query'     => '' // TODO: Actually generate query?
        ]);

        if ($requst->devices) {
            $rule->devices()->sync($request->devices);
        }

        return $this->objectResponse($rule);
    }
    /**
     *
     * @api {delete} /api/v1/rules/:id Delete a rule
     * @apiName Delete_rule
     * @apiGroup Rules
     * @apiVersion  1.0.0
     *
     * @apiParam  {Number} id The id of the rule.
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data":
     *          {
     *              "message": "Rule #234 has been deleted"
     *          }
     *     }
     *
     * @apiUse NotFoundError
     *
     */
    public function destroy(Rule $rule)
    {
        $rule->delete();
        return $this->messageResponse("Rule #$rule->id has been deleted");
    }
}
