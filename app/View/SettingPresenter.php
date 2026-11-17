<?php

/**
 * SettingPresenter.php
 *
 * Prepare setting definitions for the settings frontend, translations are resolved server side.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
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

namespace App\View;

use Illuminate\Contracts\Support\Arrayable;

class SettingPresenter
{
    /**
     * @param  array<string, mixed>  $setting
     * @return array{name: string, type: string, value: mixed, default: mixed, overridden: bool, required: bool, pattern: ?string, options: mixed, when: mixed, advanced: bool, description: string, help: ?string, units: ?string}
     */
    public static function present(array $setting, string $prefix = 'settings'): array
    {
        $name = (string) $setting['name'];
        $type = (string) ($setting['type'] ?? '');
        $overridden = (bool) ($setting['overridden'] ?? false);

        $help = self::translateOrNull("$prefix.settings.$name.help");
        if ($overridden) {
            $readonly = __('settings.readonly');
            $help = $help ? "$help<br /><br />$readonly" : $readonly;
        }

        $units = $setting['units'] ?? null;

        $options = $setting['options'] ?? null;
        if ($options instanceof Arrayable) {
            $options = $options->toArray();
        }

        return [
            'name' => $name,
            'type' => $type,
            'value' => $setting['value'] ?? null,
            'default' => $setting['default'] ?? null,
            'overridden' => $overridden,
            'required' => (bool) ($setting['required'] ?? false),
            'pattern' => $setting['pattern'] ?? null,
            'options' => in_array($type, ['select', 'multiple'])
                ? self::presentOptions($name, (array) $options, $prefix)
                : $options,
            'when' => $setting['when'] ?? null,
            'advanced' => (bool) ($setting['advanced'] ?? false),
            'description' => self::translateOrNull("$prefix.settings.$name.description") ?? $name,
            'help' => $help,
            'units' => $units === null ? null : (self::translateOrNull("$prefix.units.$units") ?? (string) $units),
        ];
    }

    public static function translateOrNull(string $key): ?string
    {
        $translated = __($key);

        return is_string($translated) && $translated !== $key ? $translated : null;
    }

    /**
     * Convert options to an ordered list so numeric keys survive the trip through json
     *
     * @param  array<array-key, mixed>  $options
     * @return list<array{value: string, text: string}>
     */
    private static function presentOptions(string $name, array $options, string $prefix): array
    {
        $presented = [];

        foreach ($options as $value => $text) {
            $value = (string) $value;
            $text = (string) $text;

            $presented[] = [
                'value' => $value,
                'text' => self::translateOrNull("$prefix.settings.$name.options.$value")
                    ?? self::translateOrNull("$prefix.settings.$name.options.$text")
                    ?? $text,
            ];
        }

        return $presented;
    }
}
