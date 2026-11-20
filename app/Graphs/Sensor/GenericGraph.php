<?php

namespace App\Graphs\Sensor;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphPageContext;
use App\Data\Graphing\SeriesRole;
use App\Data\Graphing\SeriesStyle;
use App\Data\Graphing\Threshold;
use App\Graphs\AbstractSeriesGraph;
use App\Models\Sensor;
use LibreNMS\Exceptions\NoGraphData;

/**
 * One sensor with the range of each interval and its alert thresholds, for sensor classes without a graph of their own
 */
class GenericGraph extends AbstractSeriesGraph
{
    /** @var array<string, array{string, SeriesRole}> threshold field to label translation and role */
    private const array THRESHOLDS = [
        'sensor_limit_low' => ['graph.sensor.thresholds.low', SeriesRole::LowLimit],
        'sensor_limit_low_warn' => ['graph.sensor.thresholds.low_warn', SeriesRole::LowWarning],
        'sensor_limit_warn' => ['graph.sensor.thresholds.high_warn', SeriesRole::HighWarning],
        'sensor_limit' => ['graph.sensor.thresholds.high', SeriesRole::HighLimit],
    ];

    private ?Sensor $sensor = null;

    protected function validation(): array
    {
        return ['id' => 'required|integer'];
    }

    protected function isAuthorized(): bool
    {
        $sensor = $this->findSensor();

        return $sensor !== null && $this->canView($sensor);
    }

    public function getPageContext(): GraphPageContext
    {
        $sensor = $this->findSensor();

        return new GraphPageContext($sensor?->device, subtitle: $sensor ? __('graph.sensor.subtitle', ['descr' => $sensor->sensor_descr]) : null);
    }

    public function getTitle(): string
    {
        $sensor = $this->sensor();

        return $sensor->device?->shortDisplayName() . '::' . $sensor->sensor_descr;
    }

    public function getSeries(): array
    {
        $sensor = $this->sensor();
        [$scale, $offset] = $this->conversion();

        return [
            'sensor' => new DataSeries($sensor->metricIdentity('sensor'), 'sensor', (string) $sensor->sensor_descr, $sensor->unit(), $scale,
                new SeriesStyle(SeriesRole::Value, range: true), $offset),
        ];
    }

    protected function options(): GraphOptions
    {
        $sensor = $this->sensor();
        [$scale, $offset] = $this->conversion();

        $thresholds = [];
        foreach (self::THRESHOLDS as $field => [$label, $role]) {
            if ($sensor->$field !== null) {
                $thresholds[] = new Threshold($sensor->$field * $scale + $offset, __($label, ['value' => $sensor->formatValue($field)]), $role);
            }
        }

        return new GraphOptions(
            verticalLabel: $sensor->classDescr(),
            axisUnit: $sensor->unit(),
            thresholds: $thresholds,
            scaleMargin: 0.01,
        );
    }

    /**
     * Temperatures are stored in celsius and shown in the unit the user prefers
     *
     * @return array{float, float} scale and offset
     */
    private function conversion(): array
    {
        return $this->sensor()->unit() === '°F' ? [1.8, 32] : [1, 0];
    }

    /**
     * @throws NoGraphData
     */
    private function sensor(): Sensor
    {
        return $this->findSensor() ?? throw new NoGraphData;
    }

    private function findSensor(): ?Sensor
    {
        return $this->params->id === null ? null : ($this->sensor ??= Sensor::with('device')->find($this->params->id));
    }
}
