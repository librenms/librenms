<?php

namespace App\Http\Controllers;

use App\Http\Interfaces\ToastInterface;
use App\Models\AlertRule;
use App\Models\AlertTemplate;
use App\Models\AlertTemplateMap;
use App\Models\Device;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

    public function create(): View
    {
        $this->authorize('create', AlertTemplate::class);

        return view('alert-templates.create', $this->formData(new AlertTemplate));
    }

    public function store(Request $request, ToastInterface $toast): RedirectResponse
    {
        $this->authorize('create', AlertTemplate::class);

        $validated = $request->validate([
            'name' => [...$this->nameRules(), Rule::notIn([self::DEFAULT_TEMPLATE])],
            ...$this->fieldRules(),
        ], ['name.not_in' => __('This template name is reserved!')]);

        $template = $this->save(new AlertTemplate, $validated);
        $toast->success(__('Alert template :name created', ['name' => $template->name]));

        return redirect()->route('alert-templates.index');
    }

    public function edit(AlertTemplate $alertTemplate): View
    {
        $this->authorize('update', $alertTemplate);

        return view('alert-templates.edit', $this->formData($alertTemplate));
    }

    public function update(Request $request, AlertTemplate $alertTemplate, ToastInterface $toast): RedirectResponse
    {
        $this->authorize('update', $alertTemplate);

        $validated = $request->validate([
            'name' => $alertTemplate->name === self::DEFAULT_TEMPLATE
                ? [...$this->nameRules(), Rule::in([self::DEFAULT_TEMPLATE])] // the default template cannot be renamed
                : [...$this->nameRules(), Rule::notIn([self::DEFAULT_TEMPLATE])],
            ...$this->fieldRules(),
        ]);

        $this->save($alertTemplate, $validated);
        $toast->success(__('Alert template :name updated', ['name' => $alertTemplate->name]));

        return redirect()->route('alert-templates.index');
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
     * @return array{template: AlertTemplate, isDefault: bool, isLegacy: bool, rules: Collection<int, AlertRule>, usedBy: Collection<int, string>, selectedRules: Collection<int, int>}
     */
    private function formData(AlertTemplate $template): array
    {
        return [
            'template' => $template,
            'isDefault' => $template->exists && $template->name === self::DEFAULT_TEMPLATE,
            'isLegacy' => $template->exists && LegacyTemplateConverter::isLegacy($template->template),
            'rules' => AlertRule::orderBy('name')->get(['id', 'name']),
            // a rule may only use one template
            'usedBy' => AlertTemplateMap::with('template:id,name')
                ->where('alert_templates_id', '!=', $template->id ?? 0)
                ->get()->pluck('template.name', 'alert_rule_id'),
            'selectedRules' => $template->exists ? $template->map()->pluck('alert_rule_id') : collect(),
        ];
    }

    /**
     * @return list<string|Closure>
     */
    private function nameRules(): array
    {
        return ['required', 'string', 'max:255', function (string $attribute, mixed $value, Closure $fail): void {
            if (is_string($value) && strip_tags($value) !== $value) {
                $fail(__('The template name must not contain HTML tags.'));
            }
        }];
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
                'name' => $validated['name'],
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
