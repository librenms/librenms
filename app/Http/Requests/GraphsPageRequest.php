<?php

namespace App\Http\Requests;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use LibreNMS\Util\Time;

class GraphsPageRequest extends FormRequest
{
    public string $type = '';
    public string $subtype = '';
    public ?Device $device = null;
    public ?Port $port = null;
    public int $from = 0;
    public int $to = 0;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $typeInput = $this->string('type', '')->toString();
        if (! preg_match('/^[a-zA-Z0-9]+_[a-zA-Z0-9_.-]+$/', $typeInput)) {
            return false;
        }
        [$this->type, $this->subtype] = explode('_', $typeInput, 2);

        $this->from = (int) (Time::parseAt($this->input('from', '')) ?: LibrenmsConfig::get('time.day'));
        $this->to = (int) (Time::parseAt($this->input('to', '')) ?: LibrenmsConfig::get('time.now'));

        // Include legacy database and HTML helper functions
        include_once base_path('includes/dbFacile.php');
        include_once base_path('includes/common.php');
        include_once base_path('includes/html/functions.inc.php');
        include_once base_path('includes/rewrites.php');

        $device = null;
        if ($deviceId = $this->input('device')) {
            $device = DeviceCache::get($deviceId);
        } elseif (($entityId = $this->input('id')) && $this->type !== 'port') {
            $device = DeviceCache::get($entityId);
        }
        $port = null;
        $auth = false;

        // Legacy auth.inc.php files expect their inputs in a $vars array in scope
        $vars = $this->except(['page', 'username', 'password']);
        if ($this->has('id') && ! $this->has('device') && $this->type !== 'port') {
            $vars['device'] = $this->input('id');
        }

        $authPath = base_path("includes/html/graphs/{$this->type}/auth.inc.php");
        if (! is_file($authPath)) {
            // Let validation handle reporting that the graph type is invalid
            return true;
        }

        $runAuth = static function (string $file, array $vars, mixed &$device, mixed &$port, bool &$auth): void {
            require $file;
        };
        $runAuth($authPath, $vars, $device, $port, $auth);

        $this->device = $device;
        $this->port = $port;

        return (bool) $auth;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'regex:/^[a-zA-Z0-9]+_[a-zA-Z0-9_.-]+$/'],
            'from' => ['nullable', 'string', 'regex:/^[-a-zA-Z0-9_ :]+$/'],
            'to' => ['nullable', 'string', 'regex:/^[-a-zA-Z0-9_ :]+$/'],
            'widescreen' => ['nullable', 'string', 'in:yes,no'],
            'legend' => ['nullable', 'string', 'in:yes,no'],
            'previous' => ['nullable', 'string', 'in:yes,no'],
            'showcommand' => ['nullable', 'string', 'in:yes,no'],
            'port_speed_zoom' => ['nullable', 'in:0,1'],
            'device' => ['nullable', 'integer'],
            'id' => ['nullable', 'integer'],
            'width' => ['nullable', 'integer', 'min:10'],
            'height' => ['nullable', 'integer', 'min:10'],

            // Collectd parameters
            'c_plugin' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'c_plugin_instance' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'c_type' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'c_type_instance' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_.-]+$/'],

            // Sensor parameters
            'sensor' => ['nullable', 'integer'],

            // Generic parameters commonly used by legacy graph scripts
            'in' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'out' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'inverse' => ['nullable', 'string', 'in:true,false,1,0,yes,no'],
            'float_precision' => ['nullable', 'integer'],
            'total' => ['nullable', 'string', 'in:true,false,1,0,yes,no'],
            'details' => ['nullable', 'string', 'in:true,false,1,0,yes,no'],
            'aggregate' => ['nullable', 'string', 'in:true,false,1,0,yes,no'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $validateItem = function (string $key, mixed $value) use ($validator, &$validateItem): void {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $validateItem($key, $k);
                        $validateItem($key, $v);
                    }
                } elseif (is_string($value)) {
                    if (! preg_match('/^[-a-zA-Z0-9_.: \/+]*$/', $value)) {
                        $validator->errors()->add($key, 'The parameter value contains invalid characters.');
                    }
                }
            };

            foreach ($this->all() as $key => $value) {
                // Validate key
                if (! preg_match('/^[a-zA-Z0-9_.-]+$/', $key)) {
                    $validator->errors()->add($key, 'The parameter key contains invalid characters.');
                }
                $validateItem($key, $value);
            }

            // Check if graph type auth file exists
            $typeInput = $this->input('type');
            if (is_string($typeInput) && str_contains($typeInput, '_')) {
                [$typePart] = explode('_', $typeInput, 2);
                $authPath = base_path("includes/html/graphs/{$typePart}/auth.inc.php");
                if (! is_file($authPath)) {
                    $validator->errors()->add('type', 'The specified graph type is invalid.');
                }
            }
        });
    }

    /**
     * Build the legacy variables array for graph template execution.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public function toVars(array $overrides = []): array
    {
        $vars = $this->except(['page', 'username', 'password']);
        $vars['from'] = $this->from;
        $vars['to'] = $this->to;

        if ($this->port) {
            $vars['device'] = $this->port->device_id;
            $vars['id'] = $this->port->port_id;
        } elseif ($this->device) {
            $vars['device'] = $this->device->device_id;
            $vars['id'] = $this->device->device_id;
        }

        return array_merge($vars, $overrides);
    }
}
