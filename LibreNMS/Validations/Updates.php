<?php
/**
 * Updates.php
 *
 * Checks the status of git and updates.
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

use DateTime;
use DateTimeZone;
use Exception;
use LibreNMS\Config;
use LibreNMS\Interfaces\ValidationGroup;
use LibreNMS\ValidationResult;
use LibreNMS\Validator;

class Updates implements ValidationGroup
{
    public function validate(Validator $validator)
    {
        // if git is not available, we cannot do the other tests
        if (!check_git_exists()) {
            $validator->warn('Unable to locate git. This should probably be installed.');
            return;
        }

        $versions = $validator->getVersions(true);

        // check if users on master update channel are up to date
        if (Config::get('update_channel') == 'master') {
            if ($versions['local_sha'] != $versions['github']['sha']) {
                try {
                    $commit_date = new DateTime('@' . $versions['local_date'], new DateTimeZone(date_default_timezone_get()));
                    if ($commit_date->diff(new DateTime())->days > 0) {
                        $validator->warn(
                            "Your install is over 24 hours out of date, last update: " . $commit_date->format('r'),
                            'Make sure your daily.sh cron is running and run ./daily.sh by hand to see if there are any errors.'
                        );
                    }
                } catch (Exception $e) {
                    $validator->fail($e->getMessage());
                }
            }

            if ($versions['local_branch'] != 'master') {
                $validator->warn(
                    "Your local git branch is not master, this will prevent automatic updates.",
                    "You can switch back to master with git checkout master"
                );
            }
        }

        // TODO check update channel stable version

        // check for modified files
        $modifiedcmd = 'git diff --name-only --exit-code';
        $validator->execAsUser($modifiedcmd, $cmdoutput, $code);
        if ($code !== 0 && !empty($cmdoutput)) {
            $result = ValidationResult::warn(
                "Your local git contains modified files, this could prevent automatic updates.",
                "You can fix this with ./scripts/github-remove"
            );
            $result->setList('Modified Files', $cmdoutput);
            $validator->result($result);
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
