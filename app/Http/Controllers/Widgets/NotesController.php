<?php

/**
 * NotesController.php
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
use Illuminate\View\View;

class NotesController extends WidgetController
{
    protected string $name = 'notes';
    protected $defaults = [
        'title' => null,
        'notes' => null,
    ];

    public function getView(Request $request): string|View
    {
        $settings = $this->getSettings();

        // Empty / whitespace: quiet Configure empty state (settings stay behind gear / Configure).
        if ($settings['notes'] === null || trim((string) $settings['notes']) === '') {
            return $this->needsConfigurationView(__('No notes yet. Configure this widget to add content.'));
        }

        $purifier_config = [
            'HTML.Allowed' => 'b,iframe[frameborder|src|width|height],i,ul,ol,li,h1,h2,h3,h4,br,p,pre',
            'HTML.Trusted' => true,
            'HTML.SafeIframe' => true,
            'URI.SafeIframeRegexp' => '%^(https?:)?//%',
        ];
        $output = \LibreNMS\Util\Clean::html(nl2br($settings['notes']), $purifier_config);

        return view('widgets.notes', [
            'notesHtml' => $output,
        ]);
    }
}
