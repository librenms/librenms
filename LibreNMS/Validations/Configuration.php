<?php
/**
 * Configuration.php
 *
 * Checks various config settings are correct.
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

namespace LibreNMS\Validations;

use LibreNMS\Config;
use LibreNMS\DB\Eloquent;
use LibreNMS\Validator;

class Configuration extends BaseValidation
{
    /**
     * Validate this module.
     * To return ValidationResults, call ok, warn, fail, or result methods on the $validator
     *
     * @param Validator $validator
     */
    public function validate(Validator $validator)
    {
        // Test transports
        if (Config::get('alerts.email.enable') == true) {
            $validator->warn('You have the old alerting system enabled - this is to be deprecated on the 1st of June 2015: https://groups.google.com/forum/#!topic/librenms-project/1llxos4m0p4');
        }

        if (config('app.debug')) {
            $validator->warn('Debug enabled.  This is a security risk.');
        }

        if (Eloquent::isConnected() && ! \DB::table('devices')->exists()) {
            $validator->warn('You have no devices.', 'Consider adding a device such as localhost: ' . $validator->getBaseURL() . '/addhost');
        }
    }
}
