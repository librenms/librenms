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
use App\Models\PollerCluster;
use LibreNMS\DB\Eloquent;
use LibreNMS\ValidationResult;

class CheckSchedules implements \LibreNMS\Interfaces\Validation
{
    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        if (LibrenmsConfig::get('schedule_type.poller') == 'legacy' || LibrenmsConfig::get('schedule_type.poller') == 'dispatcher') {
            if (LibrenmsConfig::get('service_poller_frequency') != null) {
                if (LibrenmsConfig::get('service_poller_frequency') > LibrenmsConfig::get('rrd.step')) {
                    return ValidationResult::fail(trans('validation.validations.poller.CheckSchedules.dispatcher_poll_slow'));
                } elseif (LibrenmsConfig::get('service_poller_frequency') < LibrenmsConfig::get('rrd.step')) {
                    return ValidationResult::warn(trans('validation.validations.poller.CheckSchedules.dispatcher_poll_fast'));
                }
            }
        }

        if (LibrenmsConfig::get('schedule_type.ping') == 'legacy' || LibrenmsConfig::get('schedule_type.ping') == 'dispatcher') {
            if (LibrenmsConfig::get('service_ping_frequency') != null) {
                if (LibrenmsConfig::get('service_ping_frequency') > LibrenmsConfig::get('ping_rrd_step')) {
                    return ValidationResult::fail(trans('validation.validations.poller.CheckSchedules._dispatcher_ping_slow'));
                } elseif (LibrenmsConfig::get('service_ping_frequency') < LibrenmsConfig::get('ping_rrd_step')) {
                    return ValidationResult::warn(trans('validation.validations.poller.CheckSchedules.dispatcher_ping_fast'));
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
