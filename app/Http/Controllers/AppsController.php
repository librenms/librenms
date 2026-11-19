<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use LibreNMS\Applications\ApplicationDefinition;
use LibreNMS\Util\ObjectCache;
use LibreNMS\Util\Url;

class AppsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Application::class);

        $request->validate([
            'app' => 'nullable|string',
            'instance' => 'nullable|string',
            'view' => 'nullable|string',
            'graph' => 'nullable|string',
        ]);

        $type = $request->query('app');
        $instance = $request->query('instance');
        $definition = $type === null ? null : ApplicationDefinition::forType($type);
        $applications = Application::query()->hasAccess($request->user())->with('device')
            ->when($type !== null, fn ($query) => $query->where('app_type', $type))
            ->when($instance !== null, fn ($query) => $query->where('app_instance', $instance))
            ->get()
            ->filter(fn (Application $application) => $application->device !== null)
            ->sortBy(fn (Application $application) => $application->device->hostname, SORT_NATURAL | SORT_FLAG_CASE);

        $groups = $applications->sortBy('show_name', SORT_NATURAL | SORT_FLAG_CASE)->groupBy('app_type');
        $apps = $type === null ? $groups : ObjectCache::applications();
        $names = $type === null ? collect() : $apps->get($type, collect())
            ->pluck('app_instance')->filter(fn ($name) => filled($name))->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE);
        $instances = $names->isEmpty() ? [] : ['' => [
            'text' => __('All'),
            'link' => route('apps.index', ['app' => $type]),
        ]] + $names->mapWithKeys(fn ($name) => [$name => [
            'text' => $name,
            'link' => route('apps.index', ['app' => $type, 'instance' => $name]),
        ]])->all();

        return view('apps.index', [
            'groups' => $groups,
            'apps' => $apps,
            'instances' => $instances,
            'applications' => $applications,
            'type' => $type,
            'instance' => $instance,
            'definition' => $definition,
        ] + ($definition?->fleetData($request) ?? []));
    }

    public function legacy(): RedirectResponse
    {
        return redirect()->route('apps.index', Arr::only(Url::parseOptions(), ['app', 'instance', 'view', 'graph']), 301);
    }
}
