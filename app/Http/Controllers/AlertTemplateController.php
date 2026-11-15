<?php

namespace App\Http\Controllers;

use App\Models\AlertRule;
use App\Models\AlertTemplate;
use App\Models\AlertTemplateMap;
use App\Models\Device;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use LibreNMS\Alert\AlertData;
use LibreNMS\Alert\LegacyTemplateConverter;
use Throwable;

class AlertTemplateController extends Controller
{
    private const string DEFAULT_TEMPLATE = 'Default Alert Template';

    public function index(): View
    {
        $this->authorize('viewAny', AlertTemplate::class);

        $templates = AlertTemplate::with('alert_rules')->orderBy('id')->get();
        $default = $templates->firstWhere('name', self::DEFAULT_TEMPLATE);

        return view('alert-templates.index', [
            'templates' => $templates,
            'default' => $default,
            // rules without a template use the default template
            'defaultRules' => AlertRule::whereNotIn('id', AlertTemplateMap::select('alert_rule_id'))->orderBy('name')->pluck('name'),
        ]);
    }

    public function show(AlertTemplate $alertTemplate): JsonResponse
    {
        $this->authorize('view', $alertTemplate);

        return response()->json([
            ...$alertTemplate->only(['id', 'name', 'template', 'title', 'title_rec']),
            'rules' => $alertTemplate->map()->pluck('alert_rule_id'),
            'legacy' => LegacyTemplateConverter::isLegacy($alertTemplate->template),
        ]);
    }

    /**
     * The alert rules a template may be attached to, with the template each one is already attached to.
     */
    public function alertRules(): JsonResponse
    {
        $this->authorize('viewAny', AlertTemplate::class);

        $usedBy = AlertTemplateMap::with('template:id,name')->get()->pluck('template.name', 'alert_rule_id');

        return response()->json(AlertRule::orderBy('name')->get(['id', 'name'])->map(fn (AlertRule $rule) => [
            'id' => $rule->id,
            'name' => $rule->name,
            'template' => $usedBy->get($rule->id),
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', AlertTemplate::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::notIn([self::DEFAULT_TEMPLATE])],
            ...$this->fieldRules(),
        ], ['name.not_in' => __('This template name is reserved!')]);

        $template = $this->save(new AlertTemplate, $validated);

        return response()->json(['message' => __('Alert template :name created', ['name' => $template->name])], 201);
    }

    public function update(Request $request, AlertTemplate $alertTemplate): JsonResponse
    {
        $this->authorize('update', $alertTemplate);

        $validated = $request->validate([
            'name' => $alertTemplate->name === self::DEFAULT_TEMPLATE
                ? ['required', Rule::in([self::DEFAULT_TEMPLATE])] // the default template cannot be renamed
                : ['required', 'string', 'max:255', Rule::notIn([self::DEFAULT_TEMPLATE])],
            ...$this->fieldRules(),
        ]);

        $this->save($alertTemplate, $validated);

        return response()->json(['message' => __('Alert template :name updated', ['name' => $alertTemplate->name])]);
    }

    public function destroy(AlertTemplate $alertTemplate): JsonResponse
    {
        $this->authorize('delete', $alertTemplate);
        abort_if($alertTemplate->name === self::DEFAULT_TEMPLATE, 422, __('The default template cannot be deleted'));

        DB::transaction(function () use ($alertTemplate): void {
            $alertTemplate->map()->delete();
            $alertTemplate->delete();
        });

        return response()->json(['message' => __('Alert template :name deleted', ['name' => $alertTemplate->name])]);
    }

    /**
     * Convert a template using the old {if}/%var syntax to Blade for review.
     */
    public function convert(Request $request, LegacyTemplateConverter $converter): JsonResponse
    {
        $this->authorize('update', AlertTemplate::class);

        $validated = $request->validate([
            'template' => 'required|string',
            'title' => 'nullable|string',
        ]);

        return response()->json([
            'message' => __('Template converted, review and save to update'),
            'template' => $converter->convert($validated['template']),
            'title' => $converter->convert($validated['title'] ?? ''),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function fieldRules(): array
    {
        return [
            'template' => 'required|string',
            'title' => 'nullable|string|max:255',
            'title_rec' => 'nullable|string|max:255',
            'rules' => 'nullable|array',
            'rules.*' => 'integer|exists:alert_rules,id',
        ];
    }

    /**
     * @param  array{name: string, template: string, title?: ?string, title_rec?: ?string, rules?: ?int[]}  $validated
     *
     * @throws ValidationException
     */
    private function save(AlertTemplate $template, array $validated): AlertTemplate
    {
        $this->assertRenders($validated);

        DB::transaction(function () use ($template, $validated): void {
            $template->fill([
                'name' => strip_tags($validated['name']),
                'template' => $validated['template'],
                'title' => $validated['title'] ?? null,
                'title_rec' => $validated['title_rec'] ?? null,
            ])->save();

            $template->map()->delete();
            $template->map()->createMany(array_map(fn (int $rule) => ['alert_rule_id' => $rule], $validated['rules'] ?? []));
        });

        return $template;
    }

    /**
     * Render the template with test data so syntax errors are reported when saving.
     *
     * @param  array{template: string, title?: ?string, title_rec?: ?string}  $validated
     *
     * @throws ValidationException
     */
    private function assertRenders(array $validated): void
    {
        $device = new Device(['hostname' => 'test']);
        $device->device_id = 0;
        $data = [
            'id' => 0,
            'rule' => 'test',
            'name' => 'Test Rule',
            'severity' => 'critical',
            'extra' => '',
            'disabled' => 0,
            'query' => '',
            'builder' => [],
            'proc' => '',
            'invert_map' => 0,
            'notes' => '',
            'alert' => new AlertData(AlertData::testData($device)),
        ];

        foreach (['template', 'title', 'title_rec'] as $field) {
            try {
                Blade::render($validated[$field] ?? '', $data);
            } catch (Throwable $e) {
                throw ValidationException::withMessages([
                    $field => __('Template failed to be parsed, please check the syntax.') . ' ' . $e->getMessage(),
                ]);
            }
        }
    }
}
