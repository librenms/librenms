<?php

namespace App\Http\Requests;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Facades\PortCache;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use LibreNMS\Util\Time;
use LibreNMS\Util\Url;

class GraphsPageRequest extends FormRequest
{
    public string $type = '';
    public string $subtype = '';
    public ?Device $device = null;
    public ?Port $port = null;
    public int $from = 0;
    public int $to = 0;
    /** @var list<int> */
    public array $ids = [];

    protected function prepareForValidation(): void
    {
        $this->merge(Url::parseLegacyPathVars($this->path()));
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $typeInput = $this->string('type', '')->toString();
        if (! preg_match('#^[a-zA-Z0-9]+_[^/?&]+$#', $typeInput)) {
            return false;
        }
        [$this->type, $this->subtype] = explode('_', $typeInput, 2);

        $this->from = (int) (Time::parseAt($this->input('from', '')) ?: LibrenmsConfig::get('time.day'));
        $this->to = Time::parseAt($this->input('to', ''));

        // Include legacy database and HTML helper functions
        include_once base_path('includes/dbFacile.php');
        include_once base_path('includes/common.php');
        include_once base_path('includes/html/functions.inc.php');
        include_once base_path('includes/rewrites.php');

        $this->ids = $this->string('id')->explode(',')->filter()->map(intval(...))->values()->all();

        if ($deviceId = $this->input('device')) {
            $this->device = DeviceCache::get($deviceId);
        } elseif (count($this->ids) === 1) {
            if ($this->type == 'port') {
                $this->port = PortCache::get($this->getId());
                $this->device = $this->port->device;
            } elseif ($this->type == 'device') {
                $this->device = DeviceCache::get($this->getId());
            }
        }
        $auth = false;

        $authPath = base_path("includes/html/graphs/$this->type/auth.inc.php");
        if (! is_file($authPath)) {
            return false;
        }

        $runAuth = function (string $file, array $vars, mixed $device, mixed $port, bool &$auth): void {
            require $file;

            if (is_array($device) && isset($device['device_id'])) {
                $this->device ??= DeviceCache::get($device['device_id']);
            }
        };
        $runAuth($authPath, $this->toVars(), $this->device, $this->port, $auth);

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
            'id' => ['nullable', 'regex:/^\d+(,\d+)*$/'],
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
                    if (! preg_match('/^[-a-zA-Z0-9_.: \/+,]*$/', $value)) {
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
        });
    }

    public function getId(): int
    {
        if (count($this->ids) !== 1) {
            throw ValidationException::withMessages(['id' => 'Invalid id input, input must be a single integer']);
        }

        return $this->ids[0];
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
        $vars['to'] = $this->to ?: null;

        return array_merge($vars, $overrides);
    }
}
