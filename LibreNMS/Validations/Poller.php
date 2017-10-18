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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
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

use LibreNMS\Interfaces\ValidationGroup;
use LibreNMS\ValidationResult;
use LibreNMS\Validator;

class Poller implements ValidationGroup
{
    /**
     * Validate this module.
     * To return ValidationResults, call ok, warn, fail, or result methods on the $validator
     *
     * @param Validator $validator
     */
    public function validate(Validator $validator)
    {
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

        // FIXME pollers table is only updated by poller-wrapper.py
        // check poller
        if (dbFetchCell('SELECT COUNT(*) FROM `pollers`')) {
            $pollers = dbFetchColumn('SELECT `poller_name` FROM `pollers` WHERE `last_polled` <= DATE_ADD(NOW(), INTERVAL - 5 MINUTE)');
            if (count($pollers) > 0) {
                foreach ($pollers as $poller) {
                    $validator->fail("The poller ($poller) has not complete within the last 5 minutes, check the cron job");
                }
            }
        } else {
            $validator->fail('The poller has never run, check the cron job');
        }

        // check devices polling
        if (count($devices = dbFetchColumn("SELECT `hostname` FROM `devices` WHERE (`last_polled` < DATE_ADD(NOW(), INTERVAL - 5 MINUTE) OR `last_polled` IS NULL) AND `ignore` = 0 AND `disabled` = 0 AND `status` = 1")) > 0) {
            $result = ValidationResult::warn("Some devices have not been polled in the last 5 minutes. You may have performance issues.")
                ->setFix('Check your poll log and see: http://docs.librenms.org/Support/Performance/')
                ->setList('Devices', $devices);
            $validator->result($result);
        }

        if (count($devices = dbFetchColumn('SELECT `hostname` FROM `devices` WHERE last_polled_timetaken > 300 AND `ignore` = 0 AND `disabled` = 0 AND `status` = 1')) > 0) {
            $result = ValidationResult::fail("Some devices have not completed their polling run in 5 minutes, this will create gaps in data.")
                ->setFix("Check your poll log and refer to http://docs.librenms.org/Support/Performance/")
                ->setList('Devices', $devices);
            $validator->result($result);
        }

        // check discovery last run
        if (dbFetchCell('SELECT COUNT(*) FROM `devices` WHERE `last_discovered` IS NOT NULL') == 0) {
            $validator->fail('Discovery has never run.", "Check the cron job');
        } elseif (dbFetchCell("SELECT COUNT(*) FROM `devices` WHERE `last_discovered` <= DATE_ADD(NOW(), INTERVAL - 24 HOUR) AND `ignore` = 0 AND `disabled` = 0 AND `status` = 1") > 0) {
            $validator->fail(
                "Discovery has not completed in the last 24 hours.",
                "Check the cron job to make sure it is running and using discovery-wrapper.py"
            );
        }
    }

    /**
     * Returns if this test should be run by default or not.
     *
     * @return bool
     */
    public function isDefault()
    {
        return true;
    }
}
