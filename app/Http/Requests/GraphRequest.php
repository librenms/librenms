<?php

namespace App\Http\Requests;

use App\Data\Graphing\GraphParameters;
use App\Graphs\GraphFactory;
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

    /**
     * Only the standard graph variables have rules, legacy graph scripts check the variables they read themselves
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach (array_keys($this->all()) as $key) {
                if (! preg_match('/^[a-zA-Z0-9_.-]+$/', (string) $key)) {
                    $validator->errors()->add((string) $key, 'The parameter key contains invalid characters.');
                }
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
