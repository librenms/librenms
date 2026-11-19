<?php

namespace LibreNMS\Applications;

use App\Facades\Rrd;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApplicationDefinition
{
    public const string TYPE = '';
    protected const ?string NAME = null;
    protected const ?string GRAPH_PREFIX = null;

    /** @var array<string, string> Graph suffixes and titles for the device page. */
    protected const array GRAPHS = [];

    /** @var list<string>|null Fleet graph suffixes in display order, or null to use the device graph list. */
    protected const ?array OVERVIEW = null;

    final protected function __construct(private readonly string $type) {}

    public static function forType(string $type): self
    {
        if (preg_match('/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/D', $type)) {
            $class = __NAMESPACE__ . '\\' . Str::studly($type);
            if (is_subclass_of($class, self::class) && $class::TYPE === $type) {
                return new $class($type);
            }
        }

        return new self($type);
    }

    public function displayName(): string
    {
        return static::NAME ?? ucwords(str_replace(['_', '-'], ' ', $this->type));
    }

    public function graphType(string $suffix): string
    {
        return 'application_' . (static::GRAPH_PREFIX ?? $this->type) . ($suffix === '' ? '' : "_$suffix");
    }

    /** @return list<string> */
    public function overviewGraphs(Application $application): array
    {
        return array_map($this->graphType(...), static::OVERVIEW ?? array_keys(static::GRAPHS));
    }

    public function devicePage(Application $application, Request $request): ApplicationPage
    {
        $page = new ApplicationPage($this, $application, $request);
        $this->buildDevicePage($page, $application);

        return $page;
    }

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $page->addGraphs(static::GRAPHS);
    }

    /** @return list<string> */
    protected function rrdResources(Application $application, ?string $category = null): array
    {
        $device = $application->device;

        return $device ? Rrd::getRrdApplicationArrays(['hostname' => $device->hostname], $application->app_id, static::TYPE, $category) : [];
    }

    public function fleetView(): string
    {
        return 'apps.instances';
    }

    public function fleetData(Request $request): array
    {
        return [];
    }
}
