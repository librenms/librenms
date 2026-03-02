<?php

/**
 * ImageController.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Widgets;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ImageController extends WidgetController
{
    protected string $name = 'generic-image';
    protected $defaults = [
        'title' => null,
        'image_url' => null,
        'target_url' => null,
    ];

    public function getView(Request $request): string|View
    {
        $data = $this->getSettings();

        if (is_null($data['image_url'])) {
            return $this->getSettingsView($request);
        }

        $dimensions = $request->input('dimensions');
        $data['dimensions'] = $dimensions;

        // send size so generated images can generate the proper size
        $data['image_url'] = str_replace(['@AUTO_HEIGHT@', '@AUTO_WIDTH@'], [$dimensions['y'], $dimensions['x']], $data['image_url']);

        // bust cache
        if (Str::contains($data['image_url'], '?')) {
            $data['image_url'] .= '&' . mt_rand();
        } else {
            $data['image_url'] .= '?' . mt_rand();
        }

        return view('widgets.generic-image', $data);
    }

    public function getSettings($settingsView = false): array
    {
        if (is_null($this->settings)) {
            $this->settings = parent::getSettings();
            if (! empty($this->settings['image_title'])) {
                $this->settings['title'] = $this->settings['image_title'];
            }
        }

        return $this->settings;
    }
}
