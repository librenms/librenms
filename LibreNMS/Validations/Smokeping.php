<?php

/*
 * Smokeping.php
 *
 * Validate system items
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2026 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.au>
 */

namespace LibreNMS\Validations;

use App\Facades\LibrenmsConfig;
use Illuminate\Support\Str;
use LibreNMS\Validator;

class Smokeping extends BaseValidation
{
    public function validate(Validator $validator): void
    {
        if (! LibrenmsConfig::get('smokeping.integration')) {
            return;
        }

        $smokeping_dir = LibrenmsConfig::get('smokeping.dir');
        $rrd_dir = Str::finish(LibrenmsConfig::get('rrd_dir'), '/');

        // Warn if rrdcached is enabled, the smokeping dir is an absolute path and does not sit under rrd_dir
        if (LibrenmsConfig::get('rrdcached') && str_starts_with($smokeping_dir, '/') && ! str_starts_with($smokeping_dir, $rrd_dir)) {
            $validator->warn(trans('validation.validations.smokeping.smokepingdir_absolutepath_rrdcached'));
        }
    }
}
