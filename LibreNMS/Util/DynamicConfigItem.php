<?php

/**
 * DynamicConfigItem.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use App\Facades\LibrenmsConfig;
use Validator;

#[\AllowDynamicProperties]
class DynamicConfigItem implements \ArrayAccess
{
    public $group;
    public $section;
    public $value;
    public $type;
    public $default;
    public $overridden = false;  // overridden by config.php
    public $hidden = false;
    public $required = false;
    public $disabled = false;
    public $options = [];
    public $when;
    public $pattern;
    public $validate;
    public $units;

    public function __construct(public $name, $settings = [])
    {
        $this->value = LibrenmsConfig::get($this->name, $this->default);

        foreach ($settings as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Check given value is valid. Using the type of this config item and possibly other variables.
     *
     * @param  mixed  $value
     * @return bool|mixed
     */
    public function checkValue($value)
    {
        // For map and nested-map types, do key validation first (Laravel Validator can't validate array keys)
        if ($this->type == 'map') {
            if (! is_array($value)) {
                return false;
            }

            foreach ($value as $key => $v) {
                // values must be scalar (not arrays) for flat key-value maps
                if (is_array($v)) {
                    return false;
                }

                if (! $this->checkKey($key)) {
                    return false;
                }
            }

            // After key validation passes, run Laravel validator for value rules if present
            if ($this->validate) {
                return $this->buildValidator($value)->passes();
            }

            return true;
        } elseif ($this->type == 'nested-map' || $this->type == 'array-sub-keyed') {
            // Note: 'array-sub-keyed' is deprecated, kept for backwards compatibility.
            // Use 'nested-map' for new settings.
            if (! is_array($value)) {
                return false;
            }

            foreach ($value as $key => $v) {
                if (! is_array($v)) {
                    return false;
                }

                if (! $this->checkKey($key)) {
                    return false;
                }
            }

            // After key validation passes, run Laravel validator for value rules if present
            if ($this->validate) {
                return $this->buildValidator($value)->passes();
            }

            return true;
        } elseif ($this->validate) {
            // For other types with validate rules, use Laravel validator
            return $this->buildValidator($value)->passes();
        } elseif ($this->type == 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
        } elseif ($this->type == 'integer') {
            return (! is_bool($value) && filter_var($value, FILTER_VALIDATE_INT)) || $value === '0' || $value === 0;
        } elseif ($this->type == 'float') {
            return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
        } elseif ($this->type == 'select') {
            return in_array($value, array_keys($this->options));
        } elseif ($this->type == 'email') {
            // allow email format that includes display text
            if (preg_match('/.* <(.*)>/', (string) $value, $matches)) {
                $value = $matches[1];
            }

            return filter_var($value, FILTER_VALIDATE_EMAIL);
        } elseif ($this->type == 'list' || $this->type == 'array') {
            // Note: 'array' is deprecated, kept for backwards compatibility. Use 'list' for new settings.
            return is_array($value); // this should probably have more complex validation via validator rules
        } elseif ($this->type == 'color') {
            return (bool) preg_match('/^#?[0-9a-fA-F]{6}([0-9a-fA-F]{2})?$/', (string) $value);
        } elseif (in_array($this->type, ['text', 'password'])) {
            return ! is_array($value);
        } elseif ($this->type === 'executable') {
            $value == $this->sanitizePath($value);

            return $value !== false && is_file($value) && is_executable($value);
        } elseif ($this->type === 'directory') {
            $value == $this->sanitizePath($value);

            return $value !== false && is_dir($value);
        }

        return false;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function getSection()
    {
        return $this->section;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getOptions()
    {
        return array_reduce($this->options, function ($result, $option) {
            $key = $this->optionTranslationKey($option);
            $trans = __($key);
            $result[$option] = ($trans === $key ? $option : $trans);

            return $result;
        }, []);
    }

    public function isHidden()
    {
        return $this->hidden;
    }

    public function isRequired()
    {
        return $this->required;
    }

    public function getType()
    {
        return $this->type;
    }

    public function hasDescription()
    {
        $key = $this->descriptionTranslationKey();

        return __($key) !== $key;
    }

    public function hasHelp()
    {
        $key = $this->helpTranslationKey();

        return __($key) !== $key;
    }

    public function hasUnits()
    {
        return isset($this->units);
    }

    public function getUnits()
    {
        return $this->hasUnits() ? __($this->units) : '';
    }

    public function getDescription()
    {
        $key = $this->descriptionTranslationKey();
        $trans = __($key);

        return $trans === $key ? $this->name : $trans;
    }

    public function getHelp()
    {
        return __($this->helpTranslationKey());
    }

    public function only($fields = [])
    {
        $array = [];

        foreach ($fields as $field) {
            $array[$field] = $this->$field;
        }

        return $array;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }

    public function isValid()
    {
        return ($this->group == '' || $this->type) && ! $this->hidden && ! $this->disabled;
    }

    /**
     * @param  mixed  $value  The value that was validated
     * @return string
     */
    public function getValidationMessage($value)
    {
        // For map and nested-map types, check for key validation errors first
        if (in_array($this->type, ['map', 'nested-map', 'array-sub-keyed']) && is_array($value)) {
            foreach ($value as $key => $v) {
                if (! $this->checkKey($key)) {
                    // Return the is_regex validation message for regex key validation
                    if (($this->validate['key'] ?? null) === 'regex') {
                        $validator = Validator::make(
                            ['key' => $key],
                            ['key' => 'is_regex'],
                            [],
                            ['key' => __('Key') . " '$key'"]
                        );

                        if ($validator->fails()) {
                            return $validator->errors()->first('key');
                        }
                    }

                    return __('settings.validate.key', ['id' => $this->name, 'value' => json_encode($key)]);
                }
            }
        }

        if (! $this->validate) {
            return __('settings.validate.' . $this->type, ['id' => $this->name, 'value' => json_encode($value)]);
        }

        $messages = $this->buildValidator($value)->messages()->all();

        // Make array index references more user-friendly
        // "The value.0 is invalid" -> "item 1 is invalid"
        $messages = array_map(fn ($message) => preg_replace_callback(
            '/\bvalue\.(\d+)\b/i',
            fn ($matches) => strtolower(__('Item')) . ' ' . ((int) $matches[1] + 1),
            (string) $message
        ), $messages);

        return implode(" \n", $messages);
    }

    // ArrayAccess functions
    public function offsetExists($offset): bool
    {
        return isset($this->$offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset): mixed
    {
        return $this->$offset ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->$offset = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->$offset);
    }

    public function getName()
    {
        return $this->name;
    }

    public function checkKey($key): bool
    {
        $key = (string) $key;
        if (strlen(trim($key)) === 0) {
            return false;
        }

        if (($this->validate['key'] ?? null) === 'regex') {
            return Validator::make(['key' => $key], ['key' => 'is_regex'])->passes();
        }

        return true;
    }

    private function descriptionTranslationKey()
    {
        return "settings.settings.$this->name.description";
    }

    private function helpTranslationKey()
    {
        return "settings.settings.$this->name.help";
    }

    private function optionTranslationKey($option)
    {
        return "settings.settings.$this->name.options.$option";
    }

    private function buildValidator($value)
    {
        return Validator::make(['value' => $value], $this->validate);
    }

    private function sanitizePath(string $path): string|false
    {
        if (preg_match('/[`;#$|&\'"><(]/', $path)) {
            return false;
        }

        return realpath($path); // avoid path redirection shenanigans
    }
}
