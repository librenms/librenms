<?php

namespace LibreNMS\Applications;

use App\Models\Application;
use Illuminate\Contracts\Support\Htmlable;

final class ApplicationPage
{
    /** @var list<array{title: string, type: string, vars: array<string, mixed>, anchor: ?string}> */
    public array $graphs = [];

    /** @var list<array{label: string, options: list<array{text: string, link: string, selected: bool}>}> */
    public array $filters = [];

    /** @var array<string, string|int|float|Htmlable> */
    public array $details = [];

    /** @var array<string, string> */
    public array $text = [];

    /** @var list<array{title: string, headers: list<string>, rows: array}> */
    public array $tables = [];

    public ?string $notice = null;
    public ?Htmlable $content = null;

    /** @var array<string, string> */
    private readonly array $options;

    /** @param  array<string, mixed>  $options */
    public function __construct(
        private readonly ApplicationDefinition $definition,
        private readonly Application $application,
        array $options,
    ) {
        $this->options = array_filter($options, is_string(...));
    }

    public function option(string $key): ?string
    {
        return $this->options[$key] ?? null;
    }

    public function addGraphs(array $graphs, array $vars = []): void
    {
        foreach ($graphs as $suffix => $title) {
            $this->addGraph($title, $this->definition->graphType($suffix), ['id' => $this->application->app_id, ...$vars]);
        }
    }

    public function addGraph(string $title, string $type, array $vars, ?string $anchor = null): void
    {
        $this->graphs[] = [
            'title' => $title,
            'type' => $type,
            'vars' => array_filter($vars, fn ($value) => $value !== null),
            'anchor' => $anchor,
        ];
    }

    public function addFilter(string $label, string $key, array $choices, string|false|null $selected, array $context = []): void
    {
        $options = [];
        foreach ($choices as $value => $text) {
            $options[] = [
                'text' => (string) $text,
                'link' => $this->application->deviceAppsUrl([$key => (string) $value === '' ? null : $value] + $context),
                'selected' => (string) $value === ($selected ?? ''),
            ];
        }

        if ($options !== []) {
            $this->filters[] = ['label' => $label, 'options' => $options];
        }
    }

    public function addResourceFilter(string $label, string $key, array $values, ?string $defaultLabel = null, array $context = []): ?string
    {
        $selected = $this->option($key);
        $choices = array_combine($values, $values);
        if ($defaultLabel !== null) {
            $choices = ['' => $defaultLabel] + $choices;
        }
        $this->addFilter($label, $key, $choices, $selected, $context);

        return $selected;
    }

    public function addTable(string $title, array $headers, array $rows): void
    {
        $this->tables[] = [
            'title' => $title,
            'headers' => $headers,
            'rows' => array_map(fn (array $row) => array_map(fn ($value) => ['data' => $value], $row), $rows),
        ];
    }

    public function hasContent(): bool
    {
        return $this->graphs || $this->tables || $this->content || $this->details || $this->text;
    }
}
