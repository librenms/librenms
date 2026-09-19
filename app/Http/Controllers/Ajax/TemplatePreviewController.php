<?php

/*
 * TemplatePreviewController.php
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

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\View\SimpleTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemplatePreviewController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'template' => 'nullable|string',
            'variables' => 'nullable|array',
        ]);

        $template = $validated['template'] ?? '';
        $variables = $validated['variables'] ?? [];

        return response()->json([
            'preview' => SimpleTemplate::parse($template, $variables),
        ]);
    }
}
