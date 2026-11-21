<?php

namespace App\Data\Graphing;

/**
 * How graphs drawn in the browser are drawn. The preferences are the viewer's taste and saved for all of their graphs,
 * the rest is how one graph is being looked at and kept in its address only.
 */
readonly class GraphViewOptions
{
    public const array STACKING = ['graph', 'stack', 'overlay'];
    public const array LINE_STYLES = ['stepped', 'linear', 'smooth'];
    public const array SCALES = ['graph', 'fit', 'log'];
    public const array COLUMNS = ['now', 'min', 'max', 'avg', 'p95'];
    public const array DEFAULT_COLUMNS = ['now', 'min', 'max', 'avg'];
    public const int MIN_HEIGHT = 120;
    public const int MAX_HEIGHT = 2000;
    public const float MIN_WIDTH = 0.2;
    /** of one graph, shared in its address and never saved */
    public const array ADDRESS = ['mirror', 'stacking', 'scale'];
    /** saved for all the graphs of a user */
    public const array PREFERENCES = ['range', 'line_style', 'columns', 'width', 'height'];

    /**
     * @param  bool  $range  shade between the min and max of each line
     * @param  bool  $mirror  draw inverted series below the axis, as the graph defines
     * @param  string  $stacking  graph: as the graph defines, stack: everything on a side of the axis, overlay: nothing
     * @param  string  $scale  graph: as the graph defines, fit: to the data, log: logarithmic
     * @param  list<string>  $columns  legend columns
     * @param  ?float  $width  of the space for the graph, null to fill it
     * @param  ?int  $height  in pixels, null to follow the width
     * @param  string  $line_style  how adjacent samples are connected
     */
    public function __construct(
        public bool $range = true,
        public bool $mirror = true,
        public string $stacking = 'graph',
        public string $scale = 'graph',
        public array $columns = self::DEFAULT_COLUMNS,
        public ?float $width = null,
        public ?int $height = null,
        public string $line_style = 'stepped',
    ) {
    }

    /**
     * The saved preferences, unknown or missing values are the defaults. What only the address holds is the default.
     */
    public static function fromPreference(mixed $value): self
    {
        if (! is_array($value)) {
            return new self;
        }

        $columns = is_array($value['columns'] ?? null)
            ? array_values(array_intersect(self::COLUMNS, $value['columns']))
            : self::DEFAULT_COLUMNS;
        $height = is_numeric($value['height'] ?? null) ? (int) $value['height'] : null;
        $width = is_numeric($value['width'] ?? null) ? (float) $value['width'] : null;
        $sized = $height !== null && $height >= self::MIN_HEIGHT && $height <= self::MAX_HEIGHT;

        return new self(
            range: is_bool($value['range'] ?? null) ? $value['range'] : true,
            line_style: in_array($value['line_style'] ?? null, self::LINE_STYLES, true) ? $value['line_style'] : 'stepped',
            columns: $columns,
            width: $sized && $width !== null ? max(self::MIN_WIDTH, min($width, 1.0)) : null,
            height: $sized ? $height : null,
        );
    }

    /**
     * How the address shows this graph, so a shared address looks the same to everyone. What it leaves out is the default.
     *
     * @param  array<string, mixed>  $query
     */
    public function withAddress(array $query): self
    {
        return new self(
            range: $this->range,
            mirror: ! in_array($query['mirror'] ?? '1', ['0', 'no', 'false'], true),
            stacking: in_array($query['stacking'] ?? null, self::STACKING, true) ? $query['stacking'] : 'graph',
            scale: in_array($query['scale'] ?? null, self::SCALES, true) ? $query['scale'] : 'graph',
            columns: $this->columns,
            width: $this->width,
            height: $this->height,
            line_style: $this->line_style,
        );
    }

    /**
     * @return array<string, mixed> the options of one graph as they are when its address has none
     */
    public static function addressDefaults(): array
    {
        return array_intersect_key(get_object_vars(new self), array_flip(self::ADDRESS));
    }

    /**
     * @return array<string, mixed> the preferences of a user who saved none
     */
    public static function preferenceDefaults(): array
    {
        return array_intersect_key(get_object_vars(new self), array_flip(self::PREFERENCES));
    }

    /**
     * Validation rules for the stored preference, its value is under the prefix.
     *
     * @return array<string, string>
     */
    public static function rules(string $prefix): array
    {
        return [
            "$prefix.range" => 'sometimes|boolean',
            "$prefix.line_style" => 'sometimes|in:' . implode(',', self::LINE_STYLES),
            "$prefix.columns" => 'sometimes|array',
            "$prefix.columns.*" => 'in:' . implode(',', self::COLUMNS),
            "$prefix.width" => 'nullable|numeric|between:' . self::MIN_WIDTH . ',1',
            "$prefix.height" => 'nullable|integer|between:' . self::MIN_HEIGHT . ',' . self::MAX_HEIGHT,
        ];
    }
}
