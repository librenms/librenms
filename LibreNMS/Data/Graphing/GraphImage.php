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

class GraphImage
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $data;

    public function __construct(string $type, string $title, string $data)
    {
        $this->type = $type;
        $this->title = $title;
        $this->data = $data;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function data(): string
    {
        return $this->data;
    }

    public function base64(): string
    {
        return base64_encode($this->data);
    }

    public function inline(): string
    {
        return 'data:' . $this->imageType() . ';base64,' . $this->base64();
    }

    public function fileExtension(): string
    {
        switch ($this->imageType()) {
            case 'image/svg+xml':
                return 'svg';
            case 'image/png':
                // fallthrough
            default:
                return 'png';
        }
    }

    public function imageType(): string
    {
        return $this->type;
    }

    public function __toString()
    {
        return $this->data();
    }
}
