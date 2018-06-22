<?php

namespace App\Http\Controllers\Api;

use App\Models\Rule;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;
use LibreNMS\Alerting\QueryBuilderParser;

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
     * @apiDescription `Advanced` This api endpoint can be very complex and in most cases for creating rules it'll be easier to use the UI
     * but if you so dare the option is here. There is very limited validation on the builder field so good luck :).
     * @apiGroup Rules
     * @apiVersion  1.0.0
     *
     * @apiParam {String} name The name of the rule
     * @apiParam {Object} builder The builder object
     * @apiParam {String="AND","OR"} builder.condition
     * @apiParam {Array} builder.rules Array of rule / rule group objects
     * @apiParam {Object} [rule] This is the rule object that is inserted into the `builder rules` array
     * @apiParam {String} [rule.id] The database key your rule relates to
     * @apiParam {String} [rule.field] The database field your rule relates to
     * @apiParam {String="integer","string"} [rule.type]
     * @apiParam {String="number","text","radio"} [rule.input]
     * @apiParam {String="equal","not_equal","between","not_between","is_null","is_not_null","less","less_or_equal","greater","greater_or_equal","regex","not_regex"} [rule.operator]
     * @apiParam {String} [rule.value] The value to match the operator to the input
     * @apiParam {Object} [group] This is the rule Group that is inserted into the `builder rules` array. It can have nested rule object / rule groups.
     * @apiParam {String="AND","OR"} [group.condition] The database key your rule relates to
     * @apiParam {Array} [group.rules] Array of rule / rule group objects
     * @apiParam {String="ok","critical","warning"} severity How to display the alert, OK:`Green`, Warning:`Yellow`, Critical:`Red`
     * @apiParam {Boolean} [mute=false] Show alert status but mute notifications
     * @apiParam {Boolean} [disabled=false] Disable rule
     * @apiParam {Boolean} [invert=false] Alert when this rule doesn't match
     * @apiParam {Number} [count=-1] This is how many polling runs before an alert will trigger and the frequency.
     * @apiParam {Array} [devices] An array of device ID's to map to this rule. If empty the rule will be global.
     *
     * @apiExample {curl} Example 1:
     *  curl -X POST -d '{"name": "Test Rule 1", "severity": "ok", "builder": {"condition": "AND", "rules": [{"id":"devices.status", "field":"devices.status", "type": "integer", "input": "number", "operator": "equal", "value": 1}]}}' http://example.org/api/v1/rules -H 'Content-Type:application/json' -H 'Accept:application/json' -H "X-Auth-Token:YOUR_TOKEN_HERE"
     *
     * @apiExample {curl} Example 2:
     *  curl -X POST -d '{"name": "Test Rule 2", "severity": "ok", "builder": {"condition": "AND", "rules": [{"id":"devices.status", "field":"devices.status", "type": "integer", "input": "number", "operator": "equal", "value": 1}, {"condition": "OR", "rules": [{"id":"devices.status", "field":"devices.status", "type": "integer", "input": "number", "operator": "equal", "value": 1}, {"id":"devices.status", "field":"devices.status", "type": "integer", "input": "number", "operator": "equal", "value": 2}]}]}}' http://example.org/api/v1/rules -H 'Content-Type:application/json' -H 'Accept:application/json' -H "X-Auth-Token:YOUR_TOKEN_HERE"
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *              "name":"Test Rule 2",
     *              "disabled":false,
     *              "severity":"ok",
     *              "rule":"",
     *              "extra": "{\"count\":-1,\"mute\":false,\"delay\":300,\"invert\":false,\"interval\":300}",
     *              "query":"SELECT * FROM devices WHERE (devices.device_id = ?) AND devices.status = 1 AND (devices.status = 1)",
     *              "builder":"{\"condition\":\"AND\",\"rules\":[{\"id\":\"devices.status\",\"field\":\"devices.status\",\"type\":\"integer\",\"input\":\"number\",\"operator\":\"equal\",\"value\":1},{\"condition\":\"OR\",\"rules\":[{\"id\":\"devices.status\",\"field\":\"devices.status\",\"type\":\"integer\",\"input\":\"number\",\"operator\":\"equal\",\"value\":1}]}]}",
     *              "id":21
     *          }
     *      }
     *
     * @apiErrorExample {json} Error-Response:
     *      HTTP/1.1 422 Unproccesssable Entity
     *      {
     *          "rule": [
     *              "The builder field is required."
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
            'devices'   => 'array',
            'builder.condition' => [\Illuminate\Validation\Rule::in(['OR', 'AND']), 'required'],
            'builder.rules'     => 'required|array',
            'builder.rules.*.input'     => [\Illuminate\Validation\Rule::in(["number","text","radio"])],
            'builder.rules.*.type'      => [\Illuminate\Validation\Rule::in(["integer","string"])],
            'builder.rules.*.operator'  => [\Illuminate\Validation\Rule::in(["equal","not_equal","between","not_between","is_null","is_not_null","less","less_or_equal","greater","greater_or_equal","regex","not_regex"])],
            'builder.rules.*.condition' => [\Illuminate\Validation\Rule::in(['OR', 'AND'])],
            'builder.rules.*.rules'     => 'array',
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
            'rule'      => '',
            'extra'     => json_encode($extra),
            'query'     => QueryBuilderParser::fromJson($request->builder)->toSql(),
            'builder'   => json_encode($request->builder)
        ]);

        if ($request->devices) {
            $rule->devices()->sync($request->devices);
        }

        return $this->objectResponse($rule);
    }

    /**
     *
     * @api {put} /rules/:id Update new Rule
     * @apiName Update_rule
     * @apiDescription `Advanced` This api endpoint can be very complex and in most cases for creating rules it'll be easier to use the UI
     * but if you so dare the option is here. There is very limited validation on the builder field so good luck :).
     * @apiGroup Rules
     * @apiVersion  1.0.0
     *
     * @apiParam {Number} id The id of the rule to update
     * @apiParam {String} [name] The name of the rule
     * @apiParam {Object} [builder] The builder object
     * @apiParam {String="AND","OR"} builder.condition
     * @apiParam {Array} builder.rules Array of rule / rule group objects
     * @apiParam {Object} [rule] This is the rule object that is inserted into the `builder rules` array
     * @apiParam {String} [rule.id] The database key your rule relates to
     * @apiParam {String} [rule.field] The database field your rule relates to
     * @apiParam {String="integer","string"} [rule.type]
     * @apiParam {String="number","text","radio"} [rule.input]
     * @apiParam {String="equal","not_equal","between","not_between","is_null","is_not_null","less","less_or_equal","greater","greater_or_equal","regex","not_regex"} [rule.operator]
     * @apiParam {String} [rule.value] The value to match the operator to the input
     * @apiParam {Object} [group] This is the rule Group that is inserted into the `builder rules` array. It can have nested rule object / rule groups.
     * @apiParam {String="AND","OR"} [group.condition] The database key your rule relates to
     * @apiParam {Array} [group.rules] Array of rule / rule group objects
     * @apiParam {String="ok","critical","warning"} severity How to display the alert, OK:`Green`, Warning:`Yellow`, Critical:`Red`
     * @apiParam {Boolean} [mute=false] Show alert status but mute notifications
     * @apiParam {Boolean} [disabled=false] Disable rule
     * @apiParam {Boolean} [invert=false] Alert when this rule doesn't match
     * @apiParam {Number} [count=-1] This is how many polling runs before an alert will trigger and the frequency.
     * @apiParam {Array} [devices] An array of device ID's to map to this rule. If empty the rule will be global.
     *
     * @apiExample {curl} Example 1:
     *  curl -X PUT -d '{"name": "Test Rule 1", "severity": "ok", "builder": {"condition": "AND", "rules": [{"id":"devices.status", "field":"devices.status", "type": "integer", "input": "number", "operator": "equal", "value": 1}]}}' http://example.org/api/v1/rules/1 -H 'Content-Type:application/json' -H 'Accept:application/json' -H "X-Auth-Token:YOUR_TOKEN_HERE"
     *
     * @apiExample {curl} Example 2:
     *  curl -X PUT -d '{"name": "Test Rule 2", "severity": "ok", "builder": {"condition": "AND", "rules": [{"id":"devices.status", "field":"devices.status", "type": "integer", "input": "number", "operator": "equal", "value": 1}, {"condition": "OR", "rules": [{"id":"devices.status", "field":"devices.status", "type": "integer", "input": "number", "operator": "equal", "value": 1}, {"id":"devices.status", "field":"devices.status", "type": "integer", "input": "number", "operator": "equal", "value": 2}]}]}}' http://example.org/api/v1/rules/1 -H 'Content-Type:application/json' -H 'Accept:application/json' -H "X-Auth-Token:YOUR_TOKEN_HERE"
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *              "name":"Test Rule 2",
     *              "disabled":false,
     *              "severity":"ok",
     *              "rule":"",
     *              "extra": "{\"count\":-1,\"mute\":false,\"delay\":300,\"invert\":false,\"interval\":300}",
     *              "query":"SELECT * FROM devices WHERE (devices.device_id = ?) AND devices.status = 1 AND (devices.status = 1)",
     *              "builder":"{\"condition\":\"AND\",\"rules\":[{\"id\":\"devices.status\",\"field\":\"devices.status\",\"type\":\"integer\",\"input\":\"number\",\"operator\":\"equal\",\"value\":1},{\"condition\":\"OR\",\"rules\":[{\"id\":\"devices.status\",\"field\":\"devices.status\",\"type\":\"integer\",\"input\":\"number\",\"operator\":\"equal\",\"value\":1}]}]}",
     *              "id":21
     *          }
     *      }
     *
     * @apiErrorExample {json} Error-Response:
     *      HTTP/1.1 422 Unproccesssable Entity
     *      {
     *          "rule": [
     *              "The builder field is required."
     *          ],
     *          "name": [
     *              "The name field is required."
     *          ]
     *      }
     */
    public function update(Request $request, Rule $rule)
    {
        $this->validate($request, [
            'name'  =>  "required|unique:alert_rules,name,except,$rule->id",
            'mute'  =>  'ext_bool',
            'disabled'  => 'ext_bool',
            'severity'  => [\Illuminate\Validation\Rule::in(['ok', 'critical', 'warning']), 'required'],
            'invert'    => 'ext_bool',
            'count'     => 'numeric',
            'devices'   => 'array',
            'builder.condition' => [\Illuminate\Validation\Rule::in(['OR', 'AND'])],
            'builder.rules'     => 'array',
            'builder.rules.*.input'     => [\Illuminate\Validation\Rule::in(["number","text","radio"])],
            'builder.rules.*.type'      => [\Illuminate\Validation\Rule::in(["integer","string"])],
            'builder.rules.*.operator'  => [\Illuminate\Validation\Rule::in(["equal","not_equal","between","not_between","is_null","is_not_null","less","less_or_equal","greater","greater_or_equal","regex","not_regex"])],
            'builder.rules.*.condition' => [\Illuminate\Validation\Rule::in(['OR', 'AND'])],
            'builder.rules.*.rules'     => 'array',
        ]);
        
        $current = json_decode($rule->extra);
        $extra = [
            'count' =>  $request->get('count', $current['count']),
            'mute'  =>  $request->get('mute', $current['mute']),
            'delay' =>  $request->get('delay', $current['delay']),
            'invert' => $request->get('invert', $current['invert']),
            'interval' => $request->get('interval', $current['interval'])
        ];

        $rule->name = $request->get('name', $rule->name);
        $rule->disabled = $request->get('disabled', $rule->disabled);
        $rule->severity = $request->get('severity', $rule->severity);
        $rule->extra = $extra;
        if (isset($request->builder)) {
            $rule->query = QueryBuilderParser::fromJson($request->builder)->toSql();
            $rule->builder = json_encode($rule->builder);
        }

        if ($request->devices) {
            $rule->devices()->sync($request->devices);
        }

        $rule->save();

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
