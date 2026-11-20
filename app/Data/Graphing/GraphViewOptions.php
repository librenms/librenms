<?php

namespace App\Data\Graphing;

/**
 * How a user likes graphs drawn in the browser, kept as a user preference.
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
    /** shared in the address of a graph */
    public const array ADDRESS = ['range', 'mirror', 'stacking', 'scale', 'line_style'];

    /**
     * @param  bool  $range  shade between the min and max of each line
     * @param  bool  $mirror  draw inverted series below the axis, as the graph defines
     * @param  string  $stacking  graph: as the graph defines, stack: everything on a side of the axis, overlay: nothing
     * @param  string  $scale  graph: as the graph defines, fit: to the data, log: logarithmic
     * @param  string  $line_style  how adjacent samples are connected
     * @param  list<string>  $columns  legend columns
     * @param  ?float  $width  of the space for the graph, null to fill it
     * @param  ?int  $height  in pixels, null to follow the width
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
     * Unknown or missing values are the defaults.
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
            mirror: is_bool($value['mirror'] ?? null) ? $value['mirror'] : true,
            stacking: in_array($value['stacking'] ?? null, self::STACKING, true) ? $value['stacking'] : 'graph',
            scale: in_array($value['scale'] ?? null, self::SCALES, true) ? $value['scale'] : 'graph',
            line_style: in_array($value['line_style'] ?? null, self::LINE_STYLES, true) ? $value['line_style'] : 'stepped',
            columns: $columns,
            width: $sized && $width !== null ? max(self::MIN_WIDTH, min($width, 1.0)) : null,
            height: $sized ? $height : null,
        );
    }

    /**
     * The drawing options in an address describe the whole drawing, so a shared address looks the same to everyone:
     * what it leaves out is the default, not the viewer's preference. The legend and size stay the viewer's.
     *
     * @param  array<string, mixed>  $query
     */
    public function withAddress(array $query): self
    {
        if (array_intersect_key($query, array_flip(self::ADDRESS)) === []) {
            return $this;
        }

        $flag = fn (string $key): bool => ! in_array($query[$key] ?? '1', ['0', 'no', 'false'], true);

        return self::fromPreference([
            'range' => $flag('range'),
            'mirror' => $flag('mirror'),
            'stacking' => $query['stacking'] ?? null,
            'scale' => $query['scale'] ?? null,
            'line_style' => $query['line_style'] ?? null,
            'columns' => $this->columns,
            'width' => $this->width,
            'height' => $this->height,
        ]);
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
            "$prefix.mirror" => 'sometimes|boolean',
            "$prefix.stacking" => 'sometimes|in:' . implode(',', self::STACKING),
            "$prefix.line_style" => 'sometimes|in:' . implode(',', self::LINE_STYLES),
            "$prefix.scale" => 'sometimes|in:' . implode(',', self::SCALES),
            "$prefix.columns" => 'sometimes|array',
            "$prefix.columns.*" => 'in:' . implode(',', self::COLUMNS),
            "$prefix.width" => 'nullable|numeric|between:' . self::MIN_WIDTH . ',1',
            "$prefix.height" => 'nullable|integer|between:' . self::MIN_HEIGHT . ',' . self::MAX_HEIGHT,
        ];
    }
}
