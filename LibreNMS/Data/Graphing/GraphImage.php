<?php
/**
 * GraphImage.php
 *
 * Wrapper around a graph image to include metadata and control output format
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

namespace LibreNMS\Data\Graphing;

use LibreNMS\Enum\ImageFormat;

class GraphImage
{
    public function __construct(public readonly ImageFormat $format, public readonly string $title, public readonly string $data)
    {
    }

    public function base64(): string
    {
        return base64_encode($this->data);
    }

    public function inline(): string
    {
        return 'data:' . $this->contentType() . ';base64,' . $this->base64();
    }

    public function fileExtension(): string
    {
        return $this->format->name;
    }

    public function contentType(): string
    {
        return $this->format->contentType();
    }

    public function __toString()
    {
        return $this->data;
    }
}
