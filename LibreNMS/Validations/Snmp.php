<?php

/**
 * Programs.php
 *
 * Check that SNMP backend config is valid
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
 * @copyright  2026 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.au>
 */

namespace LibreNMS\Validations;

use App\Facades\LibrenmsConfig;
use LibreNMS\Validator;

class Snmp extends BaseValidation
{
    /**
     * Validate this module.
     * To return ValidationResults, call ok, warn, fail, or result methods on the $validator
     *
     * @param  Validator  $validator
     */
    public function validate(Validator $validator): void
    {
        if (LibrenmsConfig::get('snmp.backend') === 'phpsnmp' && ! (extension_loaded('snmp') && function_exists('snmp_init_mib'))) {
            $validator->fail(trans('validation.validations.snmp.fail_extension'), trans('validation.validations.snmp.fix_backend'));
        } else {
            $validator->ok(trans('validation.validations.snmp.backend_ok'));
        }
    }
}
