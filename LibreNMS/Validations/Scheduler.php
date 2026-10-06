<?php

/**
 * Scheduler.php
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
 */

namespace LibreNMS\Validations;

use App\Facades\LibrenmsConfig;
use Exception;
use Illuminate\Support\Facades\Cache;
use LibreNMS\ValidationResult;
use LibreNMS\Validator;

class Scheduler extends BaseValidation
{
    /**
     * Validate this module.
     * To return ValidationResults, call ok, warn, fail, or result methods on the $validator
     *
     * @param  Validator  $validator
     */
    public function validate(Validator $validator): void
    {
        try {
            $scheduler_working = Cache::has('scheduler_working');
        } catch (Exception $e) {
            $validator->fail(trans('validation.validations.poller.CheckLocking.fail', ['message' => $e->getMessage()]));

            return;
        }

        $has_systemd = is_executable(LibrenmsConfig::locateBinary('systemctl'));

        if (! $scheduler_working) {
            $commands = $this->generateCommands($validator, $has_systemd);
            $validator->result(ValidationResult::fail('Scheduler is not running')->setFix($commands));

            return;
        }

        // the old oneshot timer kills everything run in the background when schedule:run exits
        // check the enable symlink instead of asking systemctl, the web user may not be able to run it
        if (is_link('/etc/systemd/system/timers.target.wants/librenms-scheduler.timer')) {
            $validator->result(ValidationResult::warn('Scheduler is run by the old librenms-scheduler.timer, long running tasks block it and background tasks are killed when it exits')
                ->setFix(array_merge(['sudo systemctl disable --now librenms-scheduler.timer', 'sudo rm /etc/systemd/system/librenms-scheduler.timer'], $this->generateCommands($validator, $has_systemd))));
        }
    }

    /**
     * @param  Validator  $validator
     * @param  bool  $has_systemd
     * @return array
     */
    private function generateCommands(Validator $validator, bool $has_systemd): array
    {
        $commands = [];
        $base_dir = rtrim($validator->getBaseDir(), '/');

        if ($has_systemd) {
            // systemd exists
            if ($base_dir === '/opt/librenms') {
                // standard install dir
                $commands[] = 'sudo cp /opt/librenms/dist/librenms-scheduler.service /etc/systemd/system/';
            } else {
                // non-standard install dir
                $commands[] = "sudo sh -c 'sed \"s#/opt/librenms#$base_dir#\" $base_dir/dist/librenms-scheduler.service > /etc/systemd/system/librenms-scheduler.service'";
            }
            $commands[] = 'sudo systemctl daemon-reload';
            $commands[] = 'sudo systemctl enable --now librenms-scheduler.service';

            return $commands;
        }

        // non-systemd use cron
        if ($base_dir === '/opt/librenms') {
            $commands[] = 'sudo cp /opt/librenms/dist/librenms-scheduler.cron /etc/cron.d/';

            return $commands;
        }

        // non-standard install dir
        $commands[] = "sudo sh -c 'sed \"s#/opt/librenms#$base_dir#\" $base_dir/dist/librenms-scheduler.cron > /etc/cron.d/librenms-scheduler.cron'";

        return $commands;
    }
}
