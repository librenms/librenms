<?php

namespace App\Http\Requests;

use App\Data\Graphing\GraphFactory;
use App\Data\Graphing\GraphParameters;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Interfaces\Data\Graphing\GraphInterface;
use LibreNMS\Util\Url;

class GraphRequest extends FormRequest
{
    private ?GraphParameters $graphParams = null;
    private ?GraphInterface $graph = null;

    protected function prepareForValidation(): void
    {
        $this->mergeIfMissing(Url::parseLegacyPathVars($this->path()));
    }

    public function authorize(): bool
    {
        try {
            return $this->graph()->authorize();
        } catch (GraphException) {
            // unknown graphs look the same as unauthorized ones
            return false;
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'regex:' . GraphParameters::TYPE_PATTERN],
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

    /**
     * The request input for building graph urls, with the time range resolved
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public function toVars(array $overrides = []): array
    {
        $params = $this->graphParams();
        $vars = $params->input->all();
        $vars['from'] = $params->from;
        $vars['to'] = $params->ends_now ? null : $params->to;

        return array_merge($vars, $overrides);
    }

    public function graphParams(): GraphParameters
    {
        return $this->graphParams ??= new GraphParameters($this->except(['page', 'username', 'password']));
    }

    public function graph(): GraphInterface
    {
        return $this->graph ??= resolve(GraphFactory::class)->graphFor($this->graphParams());
    }
}
