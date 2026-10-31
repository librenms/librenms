<?php

namespace App\Http\Requests;

use App\Data\Graphing\GraphFactory;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\GraphVars;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Interfaces\Data\Graphing\GraphInterface;
use LibreNMS\Util\Time;
use LibreNMS\Util\Url;

class GraphRequest extends FormRequest
{
    private ?GraphInterface $graph = null;
    private bool $parsed = false;

    public string $type = '';
    public string $subtype = '';
    public ?Device $device = null;
    public ?Port $port = null;
    public int $from = 0;
    public int $to = 0;

    public ?string $subtitle = null;

    protected function prepareForValidation(): void
    {
        $this->mergeIfMissing(Url::parseLegacyPathVars($this->path()));
    }

    private function parseInput(): void
    {
        if ($this->parsed) {
            return;
        }

        $input = new GraphVars($this->all());

        $typeInput = $input->string('type') ?? '';
        if (preg_match('#^[a-zA-Z0-9]+_[^/?&]+$#', $typeInput)) {
            [$this->type, $this->subtype] = explode('_', $typeInput, 2);
        }

        $this->from = Time::parseAt($input->string('from') ?? '') ?: (int) LibrenmsConfig::get('time.day');
        $this->to = Time::parseAt($input->string('to') ?? '');

        $this->parsed = true;
    }

    public function authorize(): bool
    {
        $this->parseInput();

        if (empty($this->type) || empty($this->subtype)) {
            return false;
        }

        try {
            $graph = $this->getGraph();

            if (! $graph->authorize()) {
                return false;
            }
        } catch (GraphException) {
            // unknown graphs look the same as unauthorized ones
            return false;
        }

        $this->device = $graph->getDevice();
        $this->port = $graph->getPort();
        $this->subtitle = $graph->getSubtitle();

        return true;
    }

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
            'customer' => ['nullable', 'string', 'max:255'],
            'width' => ['nullable', 'integer', 'min:10', 'max:' . GraphParameters::MAX_SIZE],
            'height' => ['nullable', 'integer', 'min:10', 'max:' . GraphParameters::MAX_SIZE],
            'graph_title' => ['nullable', 'string', 'max:255'],

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

                // free text: customer is only used as a query binding, graph_title is escaped for rrdtool
                if ($key === 'customer' || $key === 'graph_title') {
                    continue;
                }

                $validateItem($key, $value);
            }
        });
    }

    public function toVars(array $overrides = []): array
    {
        $this->parseInput();

        $vars = $this->except(['page', 'username', 'password']);
        $vars['from'] = $this->from;
        $vars['to'] = $this->to ?: null;

        return array_merge($vars, $overrides);
    }

    public function getGraph(): GraphInterface
    {
        $this->parseInput();

        $this->graph ??= resolve(GraphFactory::class)->graphFor("{$this->type}_$this->subtype", $this->toVars());

        return $this->graph;
    }
}
