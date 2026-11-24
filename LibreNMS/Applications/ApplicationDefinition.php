<?php

namespace LibreNMS\Applications;

use App\Facades\Rrd;
use App\Models\Application;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use LibreNMS\Util\StringHelpers;

class ApplicationDefinition
{
    public const string TYPE = '';
    protected const ?string NAME = null;
    protected const ?string GRAPH_PREFIX = null;
    protected const ?string DOCS = null;

    private const string DOCS_BASE = 'https://docs.librenms.org/Extensions/Applications/';

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
        return static::NAME ?? StringHelpers::niceCase($this->type);
    }

    public function docsUrl(): ?string
    {
        $slug = static::DOCS;

        if ($slug === null) {
            return null;
        }

        return self::DOCS_BASE . rawurlencode($slug) . '/';
    }

    public function graphType(string $suffix): string
    {
        return 'application_' . (static::GRAPH_PREFIX ?? $this->type) . ($suffix === '' ? '' : "_$suffix");
    }

    /** @return list<string> */
    public function fleetGraphs(): array
    {
        return array_map($this->graphType(...), static::OVERVIEW ?? array_keys(static::GRAPHS));
    }

    /** @param  array<string, mixed>  $options */
    final public function devicePage(Application $application, array $options): ApplicationPage
    {
        $page = new ApplicationPage($this, $application, $options);
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

    /** @param  array<string, string>  $options */
    public function fleetContent(array $options): ?Htmlable
    {
        return null;
    }
}
