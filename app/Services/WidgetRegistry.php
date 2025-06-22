<?php

namespace App\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Interfaces\Plugins\Hooks\DashboardWidgetHook;

class WidgetRegistry
{
    /** @var Collection */
    private $widgets;

    /** @var Application */
    private $app;

    /** @var array core widgets part of LibreNMS */
    private $coreWidgets = [
        'alerts',
        'alertlog',
        'alertlog-stats',
        'availability-map',
        'component-status',
        'custom-map',
        'device-summary-horiz',
        'device-summary-vert',
        'device-types',
        'eventlog',
        'globe',
        'generic-graph',
        'graylog',
        'generic-image',
        'notes',
        'server-stats',
        'syslog',
        'top-devices',
        'top-errors',
        'top-interfaces',
        'worldmap',
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->widgets = new Collection();
        $this->registerCoreWidgets();
        $this->registerPluginWidgets();
    }

    /**
     * Register core widgets
     */
    private function registerCoreWidgets(): void
    {
        foreach ($this->coreWidgets as $widget) {
            $this->widgets->put($widget, [
                'name' => $widget,
                'title' => trans("widgets.$widget.title"),
                'controller' => $this->getControllerClass($widget),
                'type' => 'core',
                'metadata' => [],
            ]);
        }
    }

    /**
     * Register widgets from plugins using service container tagged services
     */
    private function registerPluginWidgets(): void
    {
        try {
            $taggedWidgets = $this->app->tagged('librenms.widget');

            foreach ($taggedWidgets as $widget) {
                if (! $widget instanceof DashboardWidgetHook) {
                    Log::warning('Tagged widget service does not implement DashboardWidgetHook interface');
                    continue;
                }

                $widgetName = $widget->getWidgetName();

                if (in_array($widgetName, $this->coreWidgets)) {
                    Log::warning("Plugin widget '$widgetName' conflicts with core widget name and will be skipped");
                    continue;
                }

                $this->widgets->put($widgetName, [
                    'name' => $widgetName,
                    'title' => $widget->getWidgetTitle(),
                    'controller' => $widget->getWidgetController(),
                    'type' => 'plugin',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to register plugin widgets: ' . $e->getMessage());
        }
    }

    /**
     * Get all registered widgets
     *
     * @return Collection
     */
    public function getWidgets(): Collection
    {
        return $this->widgets;
    }

    /**
     * Get widget names for dropdown selection
     *
     * @return array
     */
    public function getWidgetNames(): array
    {
        return $this->widgets->pluck('name')->toArray();
    }

    /**
     * Get widget titles for dropdown display
     *
     * @return array
     */
    public function getWidgetTitles(): array
    {
        return $this->widgets->pluck('title', 'name')->sort()->toArray();
    }

    /**
     * Get a specific widget by name
     *
     * @param  string  $name
     * @return array|null
     */
    public function getWidget(string $name): ?array
    {
        return $this->widgets->get($name);
    }

    /**
     * Check if a widget exists
     *
     * @param  string  $name
     * @return bool
     */
    public function hasWidget(string $name): bool
    {
        return $this->widgets->has($name);
    }

    /**
     * Get the controller class for a core widget
     *
     * @param  string  $widget
     * @return string
     */
    private function getControllerClass(string $widget): string
    {
        $controllerMap = [
            'alerts' => 'App\Http\Controllers\Widgets\AlertsController',
            'alertlog' => 'App\Http\Controllers\Widgets\AlertlogController',
            'alertlog-stats' => 'App\Http\Controllers\Widgets\AlertlogStatsController',
            'availability-map' => 'App\Http\Controllers\Widgets\AvailabilityMapController',
            'component-status' => 'App\Http\Controllers\Widgets\ComponentStatusController',
            'custom-map' => 'App\Http\Controllers\Widgets\CustomMapController',
            'device-summary-horiz' => 'App\Http\Controllers\Widgets\DeviceSummaryHorizController',
            'device-summary-vert' => 'App\Http\Controllers\Widgets\DeviceSummaryVertController',
            'device-types' => 'App\Http\Controllers\Widgets\DeviceTypeController',
            'eventlog' => 'App\Http\Controllers\Widgets\EventlogController',
            'globe' => 'App\Http\Controllers\Widgets\GlobeController',
            'generic-graph' => 'App\Http\Controllers\Widgets\GraphController',
            'graylog' => 'App\Http\Controllers\Widgets\GraylogController',
            'generic-image' => 'App\Http\Controllers\Widgets\ImageController',
            'notes' => 'App\Http\Controllers\Widgets\NotesController',
            'server-stats' => 'App\Http\Controllers\Widgets\ServerStatsController',
            'syslog' => 'App\Http\Controllers\Widgets\SyslogController',
            'top-devices' => 'App\Http\Controllers\Widgets\TopDevicesController',
            'top-errors' => 'App\Http\Controllers\Widgets\TopErrorsController',
            'top-interfaces' => 'App\Http\Controllers\Widgets\TopInterfacesController',
            'worldmap' => 'App\Http\Controllers\Widgets\WorldMapController',
        ];

        return $controllerMap[$widget] ?? 'App\Http\Controllers\Widgets\PlaceholderController';
    }
}
