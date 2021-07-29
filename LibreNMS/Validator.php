<?php
/**
 * Validator.php
 *
 * Class to run validations.  Also allows sharing data between ValidationGroups.
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS;

use Illuminate\Support\Str;
use LibreNMS\Interfaces\ValidationGroup;
use LibreNMS\Util\Laravel;
use ReflectionClass;

class Validator
{
    private $validation_groups = [];
    private $results = [];

    // data cache
    private $username;
    private $versions;

    /**
     * Validator constructor.
     */
    public function __construct()
    {
        // load all validations
        $pattern = $this->getBaseDir() . '/LibreNMS/Validations/*.php';

        foreach (glob($pattern) as $file) {
            $class_name = basename($file, '.php');
            $class = '\LibreNMS\Validations\\' . $class_name;

            $rc = new ReflectionClass($class);
            if (! $rc->isAbstract()) {
                $validation_name = strtolower($class_name);
                $this->validation_groups[$validation_name] = new $class();
                $this->results[$validation_name] = [];
            }
        }
    }

    /**
     * Run validations. An empty array will run all default validations.
     *
     * @param array $validation_groups selected validation groups to run
     * @param bool $print_group_status print out group status
     */
    public function validate($validation_groups = [], $print_group_status = false)
    {
        foreach ($this->validation_groups as $group_name => $group) {
            // only run each group once
            if ($group->isCompleted()) {
                continue;
            }

            if ((empty($validation_groups) && $group->isDefault()) || in_array($group_name, $validation_groups)) {
                if ($print_group_status && Laravel::isCli()) {
                    echo "Checking $group_name:";
                }

                /** @var ValidationGroup $group */
                $group->validate($this);

                if (Laravel::isCli()) {
                    if ($print_group_status) {
                        $status = ValidationResult::getStatusText($this->getGroupStatus($group_name));
                        c_echo(" $status\n");
                    }

                    $this->printResults($group_name);
                }

                // validation is complete for this group
                $group->markCompleted();
            }
        }
    }

    /**
     * Get the overall status of a validation group.
     *
     * @param string $validation_group
     * @return int
     */
    public function getGroupStatus($validation_group)
    {
        $results = $this->getResults($validation_group);

        $status = array_reduce($results, function ($compound, $result) {
            /** @var ValidationResult $result */
            return min($compound, $result->getStatus());
        }, ValidationResult::SUCCESS);

        return $status;
    }

    /**
     * Get the ValidationResults for a specific validation group.
     *
     * @param string $validation_group
     * @return ValidationResult[]
     */
    public function getResults($validation_group = null)
    {
        if (isset($validation_group)) {
            if (isset($this->results[$validation_group])) {
                return $this->results[$validation_group];
            } else {
                return [];
            }
        }

        return array_reduce($this->results, 'array_merge', []);
    }

    /**
     * Get all of the ValidationResults that have been submitted.
     * ValidationResults will be grouped by the validation group.
     *
     * @return array
     */
    public function getAllResults()
    {
        return $this->results;
    }

    /**
     * Print all ValidationResults or a group of them.
     *
     * @param string $validation_group
     */
    public function printResults($validation_group = null)
    {
        $results = $this->getResults($validation_group);

        foreach ($results as $result) {
            /** @var ValidationResult $result */
            $result->consolePrint();
        }
    }

    /**
     * Submit a validation result.
     * This allows customizing ValidationResults before submitting.
     *
     * @param ValidationResult $result
     * @param string $group manually specify the group, otherwise this will be inferred from the callers class name
     */
    public function result(ValidationResult $result, $group = null)
    {
        // get the name of the validation that submitted this result
        if (empty($group)) {
            $group = 'unknown';
            $bt = debug_backtrace();
            foreach ($bt as $entry) {
                if (Str::startsWith($entry['class'], 'LibreNMS\Validations')) {
                    $group = str_replace('LibreNMS\Validations\\', '', $entry['class']);
                    break;
                }
            }
        }

        $this->results[strtolower($group)][] = $result;
    }

    /**
     * Submit an ok validation result.
     *
     * @param string $message
     * @param string $fix
     * @param string $group manually specify the group, otherwise this will be inferred from the callers class name
     */
    public function ok($message, $fix = null, $group = null)
    {
        $this->result(new ValidationResult($message, ValidationResult::SUCCESS, $fix), $group);
    }

    /**
     * Submit a warning validation result.
     *
     * @param string $message
     * @param string $fix
     * @param string $group manually specify the group, otherwise this will be inferred from the callers class name
     */
    public function warn($message, $fix = null, $group = null)
    {
        $this->result(new ValidationResult($message, ValidationResult::WARNING, $fix), $group);
    }

    /**
     * Submit a failed validation result.
     *
     * @param string $message
     * @param string $fix
     * @param string $group manually specify the group, otherwise this will be inferred from the callers class name
     */
    public function fail($message, $fix = null, $group = null)
    {
        $this->result(new ValidationResult($message, ValidationResult::FAILURE, $fix), $group);
    }

    /**
     * Submit an informational validation result.
     *
     * @param string $message
     * @param string $group manually specify the group, otherwise this will be inferred from the callers class name
     */
    public function info($message, $group = null)
    {
        $this->result(new ValidationResult($message, ValidationResult::INFO), $group);
    }

    /**
     * Get version_info() array.  This will cache the result and add remote data if requested and not already existing.
     *
     * @param bool $remote
     * @return array
     */
    public function getVersions($remote = false)
    {
        if (! isset($this->versions)) {
            $this->versions = version_info($remote);
        } else {
            if ($remote && ! isset($this->versions['github'])) {
                $this->versions = version_info($remote);
            }
        }

        return $this->versions;
    }

    /**
     * Execute a command, but don't run it as root.  If we are root, run as the LibreNMS user.
     * Arguments match exec()
     *
     * @param string $command the command to run
     * @param array $output will hold the output of the command
     * @param int $code will hold the return code from the command
     */
    public function execAsUser($command, &$output = null, &$code = null)
    {
        if (self::getUsername() === 'root') {
            $command = 'su ' . \config('librenms.user') . ' -s /bin/sh -c "' . $command . '"';
        }
        exec($command, $output, $code);
    }

    /**
     * Get the username of the user running this and cache it for future requests.
     *
     * @return string
     */
    public function getUsername()
    {
        if (! isset($this->username)) {
            if (function_exists('posix_getpwuid')) {
                $userinfo = posix_getpwuid(posix_geteuid());
                $this->username = $userinfo['name'];
            } else {
                $this->username = getenv('USERNAME') ?: getenv('USER');
            }
        }

        return $this->username;
    }

    /**
     * Get the base url for this LibreNMS install, this will only work from web pages.
     * (unless base_url is set)
     *
     * @return string the base url without a trailing /
     */
    public function getBaseURL()
    {
        $url = function_exists('get_url') ? get_url() : Config::get('base_url');

        return rtrim(str_replace('validate', '', $url), '/');  // get base_url from current url
    }

    public function getBaseDir()
    {
        return realpath(__DIR__ . '/..');
    }
}
