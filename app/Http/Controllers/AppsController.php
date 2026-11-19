<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use LibreNMS\Applications\ApplicationDefinition;
use LibreNMS\Util\Html;
use LibreNMS\Util\ObjectCache;
use LibreNMS\Util\Url;

class AppsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Application::class);

        $selection = $request->validate([
            'app' => 'nullable|string',
            'instance' => 'nullable|string',
        ]);

        $type = $selection['app'] ?? null;
        $instance = $selection['instance'] ?? null;
        $definition = $type === null ? null : ApplicationDefinition::forType($type);
        $content = $definition?->fleetContent(array_filter($request->query(), is_string(...)));
        $applications = collect();
        if ($content === null) {
            $applications = Application::query()->hasAccess($request->user())->with('device')
                ->when($type !== null, fn ($query) => $query->where('app_type', $type))
                ->when($instance !== null, fn ($query) => $query->where('app_instance', $instance))
                ->get(['app_id', 'device_id', 'app_type', 'app_instance', 'app_status', 'app_state'])
                ->filter(fn (Application $application) => $application->device !== null)
                ->sortBy(fn (Application $application) => $application->device->hostname, SORT_NATURAL | SORT_FLAG_CASE);
        }

        $groups = $type === null ? $applications->sortBy('show_name', SORT_NATURAL | SORT_FLAG_CASE)->groupBy('app_type') : collect();
        $apps = $type === null ? $groups : ObjectCache::applications();
        $names = $type === null || $content !== null ? collect() : $apps->get($type, collect())
            ->pluck('app_instance')->filter(fn ($name) => filled($name))->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE);
        $instances = $names->isEmpty() ? [] : ['' => [
            'text' => __('All'),
            'link' => route('apps.index', ['app' => $type]),
        ]] + $names->mapWithKeys(fn ($name) => [$name => [
            'text' => $name,
            'link' => route('apps.index', ['app' => $type, 'instance' => $name]),
        ]])->all();

        $appOptions = ['' => ['text' => __('Overview'), 'link' => route('apps.index')]]
            + $apps->mapWithKeys(fn ($group, $appType) => [$appType => $this->appOption($group->first())])->all();

        return view('apps.index', [
            'groups' => $groups,
            'appOptions' => $appOptions,
            'instances' => $instances,
            'applications' => $applications,
            'type' => $type,
            'instance' => $instance,
            'content' => $content,
            'graphTypes' => $definition?->fleetGraphs() ?? [],
            'previewGraphs' => $groups->map(fn ($group) => $group->first()->definition()->fleetGraphs()[0] ?? null),
        ]);
    }

    /**
     * @return array{text: string, link: string, icon: ?string, title: string}
     */
    private function appOption(Application $application): array
    {
        $state = Html::appStateIcon($application->app_state);

        return [
            'text' => $application->displayName(),
            'link' => route('apps.index', ['app' => $application->app_type]),
            'icon' => $state['icon'] ? $state['icon'] . ' fa-fw' : null,
            'title' => __($state['hover_text']),
        ];
    }

    public function legacy(): RedirectResponse
    {
        return redirect()->route('apps.index', Arr::only(Url::parseOptions(), ['app', 'instance', 'view', 'graph']), 301);
    }
}
