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

use App\Facades\LibrenmsConfig;
use LibreNMS\DB\Eloquent;
use LibreNMS\ValidationResult;

class CheckSchedules implements \LibreNMS\Interfaces\Validation
{
    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        // First make sure the dispatcher service is allowed to run, otherwise we would need to check crontabs
        if (LibrenmsConfig::get('schedule_type.poller') == 'legacy' || LibrenmsConfig::get('schedule_type.poller') == 'dispatcher') {
            // Then check if the dispatcher polling schedule has been overridden, otherwise it is guaranteed to match the RRD step
            if (LibrenmsConfig::get('service_poller_frequency') != null) {
                // Return an appropriate error if the polling schedule is different from the RRD step
                if (LibrenmsConfig::get('service_poller_frequency') > LibrenmsConfig::get('rrd.step')) {
                    return ValidationResult::fail(trans('validation.validations.poller.CheckSchedules.dispatcher_poll_slow'));
                } elseif (LibrenmsConfig::get('service_poller_frequency') < LibrenmsConfig::get('rrd.step')) {
                    return ValidationResult::warn(trans('validation.validations.poller.CheckSchedules.dispatcher_poll_fast'));
                }
            }
        }

        return ValidationResult::ok(trans('validation.validations.poller.CheckSchedules.no_errors'));
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return Eloquent::isConnected();
    }
}
