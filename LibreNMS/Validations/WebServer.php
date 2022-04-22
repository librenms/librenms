<?php
/**
 * WebServer.php
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

namespace LibreNMS\Validations;

use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Validator;

class WebServer extends BaseValidation
{

    /**
     * @inheritDoc
     */
    public function validate(Validator $validator)
    {
        if (! app()->runningInConsole()) {
            $url = request()->url();
            $expected = Str::finish(Config::get('base_url'), '/') . 'validate/results';
            if ($url !== $expected) {
                preg_match('#://([^/]+)/#', $url, $actual_host_match);
                preg_match('#://([^/]+)/#', $expected, $expected_host_match);
                $actual_host = $actual_host_match[1];
                if($actual_host != $expected_host_match[1]) {
                    $nginx = Str::startsWith(request()->server->get('SERVER_SOFTWARE'), 'nginx');
                    $server_name = $nginx ? 'server_name' : 'ServerName';
                    $fix = $nginx ? "server_name $actual_host;": "ServerName $actual_host";
                    $validator->fail("$server_name is set incorrectly for your webserver, update your webserver config.", $fix);
                } else {
                    $correct_base = str_replace('validate/results', '', $url);
                    $validator->fail("base_url is not set correctly", "lnms config:set base_url $correct_base");
                }
            }
        }
    }

    public function isDefault()
    {
        return ! app()->runningInConsole();
    }
}
