<?php

namespace App\Data\Graphing;

/**
 * Stand-in for $graph_params inside legacy graph include files.
 * The includes set the title and axis options, which are converted to GraphOptions afterwards.
 */
class LegacyGraphParameters
{
    public string $title = '';
    public ?float $scale_min = null;
    public ?float $scale_max = null;
    public ?bool $scale_rigid = null;
    public ?int $base = null;
    public ?int $units_exponent = null;
    public bool $logarithmic = false;
    public bool $alt_y_grid = false;
    public ?string $vertical_label = null;
    public ?string $left_axis_format = null;
    public ?string $right_axis = null;
    public ?string $right_axis_label = null;
    public ?int $units_length = null;

    public function __construct(private readonly GraphParameters $params)
    {
    }

    public function visible(string $element): bool
    {
        return $this->params->visible($element);
    }

    public function toOptions(): GraphOptions
    {
        return new GraphOptions(
            scaleMin: $this->scale_min,
            scaleMax: $this->scale_max,
            scaleRigid: $this->scale_rigid,
            base: $this->base,
            unitsExponent: $this->units_exponent,
            logarithmic: $this->logarithmic,
            altYGrid: $this->alt_y_grid,
            verticalLabel: $this->vertical_label,
            leftAxisFormat: $this->left_axis_format,
            rightAxis: $this->right_axis,
            rightAxisLabel: $this->right_axis_label,
            unitsLength: $this->units_length,
        );
    }
}
