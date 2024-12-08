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
    /** @var string */
    private $http_regex = '#(http://([^:/]+|\[[a-fA-F\d:]+:[a-fA-F\d:]+]))(?::80)?/#';
    /** @var string */
    private $https_regex = '#(https://([^:/]+|\[[a-fA-F\d:]+:[a-fA-F\d:]+]))(?::443)?/#';
    /** @var string */
    private $host_regex = '#://([^/:\[]+|\[[a-fA-F\d:]+:[a-fA-F\d:]+])#';

    /**
     * @inheritDoc
     */
    public function validate(Validator $validator): void
    {
        if (! app()->runningInConsole()) {
            $url = $this->removeStandardPorts(request()->url());
            $expected = $this->removeStandardPorts(Str::finish(Config::get('base_url'), '/') . 'validate/results');

            if ($url !== $expected) {
                preg_match($this->host_regex, $url, $actual_host_match);
                preg_match($this->host_regex, $expected, $expected_host_match);
                $actual_host = $actual_host_match[1] ?? '';
                $expected_host = $expected_host_match[1] ?? "parse failure ($expected)";
                if ($actual_host != $expected_host) {
                    $nginx = Str::startsWith(request()->server->get('SERVER_SOFTWARE'), 'nginx');
                    $server_name = $nginx ? 'server_name' : 'ServerName';
                    $fix = $nginx ? "server_name $actual_host;" : "ServerName $actual_host";
                    $validator->fail("$server_name is set incorrectly for your webserver, update your webserver config. $actual_host $expected_host", $fix);
                } else {
                    $correct_base = str_replace('validate/results', '', $url);
                    $validator->fail('base_url is not set correctly', "lnms config:set base_url $correct_base");
                }
            }

            if (request()->secure() && ! \config('session.secure')) {
                $validator->fail('Secure session cookies are not enabled', 'Set SESSION_SECURE_COOKIE=true and run lnms config:cache');
            }
        }
    }

    public function isDefault(): bool
    {
        return ! app()->runningInConsole();
    }

    private function removeStandardPorts(string $url): string
    {
        return preg_replace($this->http_regex, '$1/', preg_replace($this->https_regex, '$1/', $url));
    }
}
