<?php

namespace App\Data\TimeSeries;

/**
 * The fields of a measurement, how they are stored is up to each datastore.
 * Fields keep their order, datastores that store values by position (rrd) follow it.
 */
final readonly class MetricDefinition
{
    /**
     * @var array<string, MetricField>
     */
    public array $fields;

    public function __construct(MetricField ...$fields)
    {
        $byName = [];
        foreach ($fields as $field) {
            $byName[$field->name] = $field;
        }

        $this->fields = $byName;
    }

    public function field(string $name): ?MetricField
    {
        return $this->fields[$name] ?? null;
    }

    /**
     * @return list<string>
     */
    public function counters(): array
    {
        return array_keys(array_filter($this->fields, fn (MetricField $field): bool => $field->isCounter()));
    }
}
