<?php

/**
 * FieldDefinition.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\View\FieldSchema;

class FieldDefinition
{
    /**
     * @param  array<string|int, mixed>|null  $options
     * @param  array<string, mixed>|null  $visibleIf
     * @param  array<int, string>|string|null  $rules
     */
    public function __construct(
        public string $key,
        public string $type = 'text',
        public ?string $label = null,
        public mixed $default = null,
        public ?array $options = null,
        public ?array $visibleIf = null,
        public array|string|null $rules = null,
        public ?string $cast = null,
        public ?int $min = null,
        public ?int $max = null,
        public ?string $placeholder = null,
        /** @var callable|null Resolved when the stored value is null; also shown as placeholder hint. */
        protected mixed $fallback = null,
    ) {
    }

    public static function make(string $key, string $type = 'text'): static
    {
        return new static(key: $key, type: $type);
    }

    public function label(?string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function default(mixed $default): static
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @param  array<string|int, mixed>|null  $options
     */
    public function options(?array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param  array<string, mixed>|null  $visibleIf
     */
    public function visibleIf(?array $visibleIf): static
    {
        $this->visibleIf = $visibleIf;

        return $this;
    }

    /**
     * @param  array<int, string>|string|null  $rules
     */
    public function rules(array|string|null $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function cast(?string $cast): static
    {
        $this->cast = $cast;

        return $this;
    }

    public function min(?int $min): static
    {
        $this->min = $min;

        return $this;
    }

    public function max(?int $max): static
    {
        $this->max = $max;

        return $this;
    }

    public function placeholder(?string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Set a callable that resolves the effective value when the stored value is null.
     * The result is also surfaced as placeholder text in the UI so users can see
     * what value will apply if they leave the field empty.
     *
     * Example:
     *   ->fallback(fn() => LibrenmsConfig::get('snmp.timeout', 3))
     */
    public function fallback(callable $fn): static
    {
        $this->fallback = $fn;

        return $this;
    }

    /**
     * Evaluate and return the fallback value, or null if no fallback is defined.
     */
    public function getFallback(): mixed
    {
        return $this->fallback !== null ? ($this->fallback)() : null;
    }

    public function getDefault(): mixed
    {
        if ($this->default !== null) {
            return is_callable($this->default) ? ($this->default)() : $this->default;
        }

        if (! empty($this->options)) {
            return array_key_first($this->options);
        }

        return null;
    }

    /**
     * Return the placeholder string: explicit placeholder takes priority,
     * then a stringified fallback value if one is defined.
     */
    public function getPlaceholder(): ?string
    {
        if ($this->placeholder !== null) {
            return $this->placeholder;
        }

        $fallbackValue = $this->getFallback();

        return $fallbackValue !== null ? (string) $fallbackValue : null;
    }

    /**
     * @return array<int, string>|string|null
     */
    public function getRules(): array|string|null
    {
        if ($this->rules !== null) {
            return is_array($this->rules) ? $this->rules : [$this->rules];
        }

        $generated = [];
        if ($this->type === 'number') {
            $generated[] = 'integer';
            if ($this->min !== null) {
                $generated[] = 'min:' . $this->min;
            }
            if ($this->max !== null) {
                $generated[] = 'max:' . $this->max;
            }
        } elseif ($this->type === 'select' && ! empty($this->options)) {
            $generated[] = 'in:' . implode(',', array_keys($this->options));
        }

        return ! empty($generated) ? $generated : null;
    }

    public function castValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $targetType = $this->cast;

        if ($targetType === null) {
            if ($this->type === 'number') {
                $targetType = 'int';
            } elseif (in_array($this->type, ['text', 'password', 'select'], true)) {
                $targetType = 'string';
            }
        }

        return match ($targetType) {
            'int', 'integer' => (int) $value,
            'float', 'double' => (float) $value,
            'bool', 'boolean' => (bool) $value,
            'string' => (string) $value,
            'array' => (array) $value,
            default => $value,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toSchemaArray(): array
    {
        $array = [
            'type' => $this->type,
        ];

        if ($this->label !== null) {
            $array['label'] = $this->label;
        }

        $default = $this->getDefault();
        if ($default !== null) {
            $array['default'] = $default;
        }

        if ($this->options !== null) {
            $array['options'] = $this->options;
        }

        if ($this->visibleIf !== null) {
            $array['visible_if'] = $this->visibleIf;
        }

        if ($this->min !== null) {
            $array['min'] = $this->min;
        }

        if ($this->max !== null) {
            $array['max'] = $this->max;
        }

        if ($this->getPlaceholder() !== null) {
            $array['placeholder'] = $this->getPlaceholder();
        }

        return $array;
    }

    /**
     * @return array<string, mixed>
     */
    public function toSchemaField(string $dataVar = 'formData'): array
    {
        return [
            ...$this->toSchemaArray(),
            'key' => $this->key,
            'field_type' => $this->type,
            'visible_if_expression' => $this->buildVisibleIfExpression($dataVar),
        ];
    }

    public function buildVisibleIfExpression(string $dataVar = 'formData'): ?string
    {
        if (empty($this->visibleIf)) {
            return null;
        }

        return collect($this->visibleIf)
            ->map(function (mixed $condVal, string $condKey) use ($dataVar): string {
                if (is_array($condVal) && isset($condVal['$in'])) {
                    return json_encode(array_values($condVal['$in'])) . '.includes(' . $dataVar . '[' . json_encode($condKey) . '])';
                }

                return $dataVar . '[' . json_encode($condKey) . '] === ' . json_encode($condVal);
            })->implode(' && ');
    }
}
