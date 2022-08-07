<?php
/**
 * CheckDispatcherService.php
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

namespace LibreNMS\Validations\Poller;

use App\Models\Poller;
use App\Models\PollerCluster;
use LibreNMS\ValidationResult;

class CheckDispatcherService implements \LibreNMS\Interfaces\Validation
{
    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        if (PollerCluster::exists()) {
            return $this->checkDispatchService();
        }

        return ValidationResult::ok(trans('validation.validations.poller.CheckDispatcherService.not_detected'));
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return true;
    }

    private function checkDispatchService(): ValidationResult
    {
        if (PollerCluster::isActive()->exists()) {
            // check for inactive nodes
            $this_node_id = config('librenms.node_id');
            $inactive = PollerCluster::isInactive()->get()
                ->map(function (PollerCluster $node) use ($this_node_id) {
                    $name = $node->poller_name ?: $node->node_id;

                    // mark this node
                    if ($node->node_id == $this_node_id) {
                        $name .= ' (this node)';
                    }

                    return $name;
                });

            if ($inactive->isNotEmpty()) {
                return ValidationResult::fail(trans('validation.validations.poller.CheckDispatcherService.nodes_down'))
                    ->setList('Inactive Nodes', $inactive->toArray());
            }

            // all ok
            return ValidationResult::ok(trans('validation.validations.poller.CheckDispatcherService.ok'));
        }

        // python wrapper found, just warn
        if (Poller::exists()) {
            $status = Poller::isActive()->exists() ? ValidationResult::SUCCESS : ValidationResult::WARNING;

            return new ValidationResult(trans('validation.validations.poller.CheckDispatcherService.warn'), $status);
        }

        // no python wrapper registered, fail
        return ValidationResult::fail(trans('validation.validations.poller.CheckDispatcherService.fail'));
    }
}
