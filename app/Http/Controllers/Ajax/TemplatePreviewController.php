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
use App\Models\Device;
use App\View\SimpleTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use LibreNMS\Util\Dns;

class TemplatePreviewController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'template' => 'nullable|string',
            'variables' => 'nullable|array',
            'resolve_ip' => 'nullable|string|max:253',
        ]);

        $template = $validated['template'] ?? '';
        $variables = $validated['variables'] ?? [];

        // fill in the ip variable from the hostname so the add device preview shows the real value
        $resolvedIp = null;
        if (! empty($validated['resolve_ip']) && $request->user()->can('create', Device::class)) {
            $resolvedIp = $this->resolveIp($validated['resolve_ip']);
            if ($resolvedIp !== null) {
                $variables['ip'] = $resolvedIp;
            }
        }

        return response()->json([
            'preview' => SimpleTemplate::parse($template, $variables),
            'resolved_ip' => $resolvedIp,
        ]);
    }

    private function resolveIp(string $hostname): ?string
    {
        // failures are cached too, a lookup that hits the DNS timeout should only be slow once
        $ip = Cache::remember('template-preview-ip:' . strtolower($hostname), 300,
            fn () => array_first(app(Dns::class)->getAddresses($hostname)) ?? '');

        return $ip === '' ? null : $ip;
    }
}
