<?php

/**
 * AlertDetailsController.php
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Ajax;

use App\Models\AlertLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class AlertDetailsController
{
    use AuthorizesRequests;

    public function __invoke(AlertLog $alertLog): JsonResponse
    {
        $this->authorize('view', $alertLog);

        $details = $alertLog->details['rule'] ?? null;

        if ($alertLog->problem_id && $alertLog->rule && ! $alertLog->rule->notify_per_entity) {
            $rows = [];
            $siblings = AlertLog::query()
                ->where('device_id', $alertLog->device_id)
                ->where('rule_id', $alertLog->rule_id)
                ->where('state', $alertLog->state->value)
                ->where('time_logged', $alertLog->time_logged)
                ->whereNotNull('problem_id')
                ->get(['id', 'details']);
            foreach ($siblings as $sibling) {
                foreach ((array) ($sibling->details['rule'] ?? []) as $row) {
                    $rows[] = $row;
                }
            }
            if (! empty($rows)) {
                $details = $rows;
            }
        }

        return response()->json([
            'details' => $details ?: 'No Details found',
        ]);
    }
}
