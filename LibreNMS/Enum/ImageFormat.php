<?php
/**
 * GraphType.php
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Enum;

use LibreNMS\Config;

enum ImageFormat: string
{
    case png = 'png';
    case svg = 'svg';

    public static function forGraph(?string $type = null): ImageFormat
    {
        return ImageFormat::tryFrom($type ?? Config::get('webui.graph_type')) ?? ImageFormat::png;
    }

    public function contentType(): string
    {
        return $this->value == 'svg' ? 'image/svg+xml' : 'image/png';
    }
}
