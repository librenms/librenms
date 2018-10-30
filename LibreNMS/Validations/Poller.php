<?php
/**
 * Poller.php
 *
 * Check that the poller and discovery are running properly.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations;

use LibreNMS\Config;
use LibreNMS\ValidationResult;
use LibreNMS\Validator;

class Poller extends BaseValidation
{
    /**
     * Validate this module.
     * To return ValidationResults, call ok, warn, fail, or result methods on the $validator
     *
     * @param Validator $validator
     */
    public function validate(Validator $validator)
    {
        if (!dbIsConnected()) {
            $validator->warn("Could not check poller/discovery, db is not connected.");
            return;
        }

        if (dbFetchCell('SELECT COUNT(*) FROM `devices`') == 0) {
            $result = ValidationResult::warn("You have not added any devices yet.");

            if (isCli()) {
                $result->setFix("You can add a device in the webui or with ./addhost.php");
            } else {
                $base_url = $validator->getBaseURL();
                $result->setFix("You can add a device by visiting $base_url/addhost");
            }

            $validator->result($result);
            return; // can't check poller/discovery if there are no devices.
        }

        $this->checkDuplicatePollerEntries($validator);
        $this->checkLastPolled($validator);
        $this->checkDeviceLastPolled($validator);
        $this->checkDevicePollDuration($validator);
        $this->checkLastDiscovered($validator);
    }

    private function checkDuplicatePollerEntries(Validator $validator)
    {
        $sql = "SELECT TRIM(TRAILING '\n' FROM `poller_name`) as `name`, COUNT(*) as `count` FROM `pollers` GROUP BY `name`;";
        foreach (dbFetchRows($sql) as $poller) {
            if ($poller['count'] > 1) {
                $validator->warn(
                    'Duplicate poller entries',
                    "Remove duplicates manually or with: DELETE FROM `pollers` WHERE `poller_name` LIKE '%\\n'"
                );
            }
        }
    }

    private function checkLastPolled(Validator $validator)
    {
        // pollers table is only updated by poller-wrapper.py
        if (dbFetchCell('SELECT COUNT(*) FROM `pollers`')) {
            $dedupe_sql = "SELECT TRIM(TRAILING '\\n' FROM `poller_name`) AS `name`, MAX(`last_polled`) AS `polled` FROM `pollers` GROUP BY `name`";
            $sql = "SELECT `name` FROM ($dedupe_sql) AS pt WHERE `polled` <= DATE_ADD(NOW(), INTERVAL - 5 MINUTE)";

            $pollers = dbFetchColumn($sql);
            if (count($pollers) > 0) {
                foreach ($pollers as $poller) {
                    $validator->fail("The poller ($poller) has not completed within the last 5 minutes, check the cron job.");
                }
            }
        } elseif (dbFetchCell('SELECT COUNT(*) FROM `poller_cluster`')) {
            $sql = "SELECT `node_id` FROM `poller_cluster` WHERE `last_report` <= DATE_ADD(NOW(), INTERVAL - 5 MINUTE)";

            $pollers = dbFetchColumn($sql);
            if (count($pollers) > 0) {
                foreach ($pollers as $poller) {
                    $validator->fail("The poller cluster member ($poller) has not checked in within the last 5 minutes, check that it is running and healthy.");
                }
            }
        } else {
            $validator->fail('The poller has never run or you are not using poller-wrapper.py, check the cron job.');
        }
    }

    private function checkDeviceLastPolled(Validator $validator)
    {
        $overdue = (int)(Config::get('rrd.step', 300) * 1.2);
        if (count($devices = dbFetchColumn("SELECT `hostname` FROM `devices` WHERE (`last_polled` < DATE_ADD(NOW(), INTERVAL - $overdue SECOND) OR `last_polled` IS NULL) AND `ignore` = 0 AND `disabled` = 0 AND `status` = 1")) > 0) {
            $result = ValidationResult::warn("Some devices have not been polled in the last 5 minutes. You may have performance issues.")
                ->setList('Devices', $devices);

            if (isCli()) {
                $result->setFix('Check your poll log and see: http://docs.librenms.org/Support/Performance/');
            } else {
                $base_url = $validator->getBaseURL();
                $result->setFix("Check $base_url/pollers/tab=log and see: http://docs.librenms.org/Support/Performance/");
            }

            $validator->result($result);
        }
    }


    private function checkDevicePollDuration(Validator $validator)
    {
        $period = (int)Config::get('rrd.step', 300);
        if (count($devices = dbFetchColumn("SELECT `hostname` FROM `devices` WHERE last_polled_timetaken > $period AND `ignore` = 0 AND `disabled` = 0 AND `status` = 1")) > 0) {
            $result = ValidationResult::fail("Some devices have not completed their polling run in 5 minutes, this will create gaps in data.")
                ->setList('Devices', $devices);

            if (isCli()) {
                $result->setFix('Check your poll log and see: http://docs.librenms.org/Support/Performance/');
            } else {
                $base_url = $validator->getBaseURL();
                $result->setFix("Check $base_url/pollers/tab=log/ and see: http://docs.librenms.org/Support/Performance/");
            }

            $validator->result($result);
        }
    }

    private function checkLastDiscovered(Validator $validator)
    {
        $incomplete_sql = "SELECT 1 FROM `devices` WHERE `last_discovered` <= DATE_ADD(NOW(), INTERVAL - 24 HOUR)
                            AND `ignore` = 0 AND `disabled` = 0 AND `status` = 1 AND `snmp_disable` = 0";

        if (!dbFetchCell('SELECT 1 FROM `devices` WHERE `last_discovered` IS NOT NULL')) {
            $validator->fail('Discovery has never run. Check the cron job');
        } elseif (dbFetchCell($incomplete_sql)) {
            $validator->fail(
                "Discovery has not completed in the last 24 hours.",
                "Check the cron job to make sure it is running and using discovery-wrapper.py"
            );
        }
    }
}
